<?php
// login.php - handle login
session_start();
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/config/firebase.php';

$loginAttemptLimit = 5;
$loginAttemptWindowSeconds = 300; // 5 minutes
$loginLockSeconds = 180; // lock for 3 minutes after limit

function login_rate_limit_store(): string {
  return __DIR__ . '/data/login_attempts.json';
}

function login_rate_limit_load(): array {
  $path = login_rate_limit_store();
  if (!file_exists($path)) {
    return [];
  }
  $decoded = json_decode((string) file_get_contents($path), true);
  return is_array($decoded) ? $decoded : [];
}

function login_rate_limit_save(array $data): void {
  $path = login_rate_limit_store();
  $dir = dirname($path);
  if (!is_dir($dir)) {
    mkdir($dir, 0775, true);
  }
  file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
}

function login_rate_limit_check(string $ip, int $windowSeconds, int $lockSeconds): ?string {
  $data = login_rate_limit_load();
  $now = time();
  $windowStart = $now - $windowSeconds;
  $ipData = $data[$ip] ?? ['attempts' => [], 'locked_until' => 0];

  // Drop stale attempts
  $ipData['attempts'] = array_values(array_filter(
    $ipData['attempts'],
    static fn($ts) => (int) $ts >= $windowStart
  ));

  if (!empty($ipData['locked_until']) && $ipData['locked_until'] > $now) {
    $retryAfter = $ipData['locked_until'] - $now;
    return 'Too many login attempts. Try again in ' . $retryAfter . ' seconds.';
  }

  // Persist the pruned data
  $data[$ip] = $ipData;
  login_rate_limit_save($data);
  return null;
}

function login_rate_limit_record_failure(string $ip, int $windowSeconds, int $lockSeconds, int $limit): void {
  $data = login_rate_limit_load();
  $now = time();
  $windowStart = $now - $windowSeconds;
  $ipData = $data[$ip] ?? ['attempts' => [], 'locked_until' => 0];

  $ipData['attempts'] = array_values(array_filter(
    $ipData['attempts'],
    static fn($ts) => (int) $ts >= $windowStart
  ));
  $ipData['attempts'][] = $now;

  if (count($ipData['attempts']) >= $limit) {
    $ipData['locked_until'] = $now + $lockSeconds;
    $ipData['attempts'] = [];
  }

  $data[$ip] = $ipData;
  login_rate_limit_save($data);
}

function login_rate_limit_clear(string $ip): void {
  $data = login_rate_limit_load();
  if (isset($data[$ip])) {
    unset($data[$ip]);
    login_rate_limit_save($data);
  }
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$lockMessage = login_rate_limit_check($ipAddress, $loginAttemptWindowSeconds, $loginLockSeconds);
if ($lockMessage) {
  header('Location: index.php?error=' . urlencode($lockMessage));
  exit;
}

function login_fail_and_redirect(string $message, string $ip, int $windowSeconds, int $lockSeconds, int $limit): void {
  login_rate_limit_record_failure($ip, $windowSeconds, $lockSeconds, $limit);
  header('Location: index.php?error=' . urlencode($message));
  exit;
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL) || $password===''){
  login_fail_and_redirect('Invalid email or password.', $ipAddress, $loginAttemptWindowSeconds, $loginLockSeconds, $loginAttemptLimit);
}
if (!firebase_is_ready()) {
  header('Location: index.php?error=' . urlencode('Authentication service is unavailable.'));
  exit;
}

try {
  $user = firebase_users_find_by_email($email);
} catch (Throwable $e) {
  header('Location: index.php?error=' . urlencode('Unable to verify credentials.'));
  exit;
}

if(!$user || !password_verify($password, $user['password_hash'] ?? '')){
  login_fail_and_redirect('Invalid email or password.', $ipAddress, $loginAttemptWindowSeconds, $loginLockSeconds, $loginAttemptLimit);
}
if (!empty($user['status']) && strtolower($user['status']) !== 'active') {
  login_fail_and_redirect('Account is inactive.', $ipAddress, $loginAttemptWindowSeconds, $loginLockSeconds, $loginAttemptLimit);
}

$sessionEmail = $user['email'] ?? $email;
$isMasterAdmin = ($sessionEmail !== '') && strcasecmp($sessionEmail, MASTER_ADMIN_EMAIL) === 0;
$sessionRole = $user['role'] ?? 'User';
if ($isMasterAdmin) {
  $sessionRole = 'Admin'; // master admin inherits admin privileges
}

login_rate_limit_clear($ipAddress);
session_regenerate_id(true);

$_SESSION['user_id'] = $user['id'] ?? ($user['bin_id'] ?? '');
$_SESSION['user_name'] = $user['name'] ?? 'User';
$_SESSION['user_role'] = $sessionRole;
$_SESSION['user_email'] = $sessionEmail;
$_SESSION['is_master_admin'] = $isMasterAdmin;
$_SESSION['last_activity'] = time();
try {
  if (!empty($user['id'])) {
    firebase_users_update($user['id'], ['last_login_at' => gmdate('c')]);
  }
} catch (Throwable $e) {
  error_log('[firebase] Failed to update last_login_at: ' . $e->getMessage());
}
header('Location: dashboard.php');
exit;
