<?php
// includes/auth.php
// Centralized session + authentication helpers

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

if (!defined('APP_IDLE_TIMEOUT')) {
  define('APP_IDLE_TIMEOUT', 600); // 10 minutes
}

// Master admin email (can be overridden via .env.php['MASTER_ADMIN_EMAIL'])
if (!defined('MASTER_ADMIN_EMAIL')) {
  $masterFromEnv = '';
  $envPath = __DIR__ . '/../.env.php';
  if (file_exists($envPath)) {
    $envData = include $envPath;
    if (is_array($envData) && !empty($envData['MASTER_ADMIN_EMAIL'])) {
      $masterFromEnv = trim((string) $envData['MASTER_ADMIN_EMAIL']);
    }
  }
  define('MASTER_ADMIN_EMAIL', $masterFromEnv !== '' ? $masterFromEnv : 'kennetharnelprestoza@gmail.com');
}

/**
 * Ensures the user is authenticated and enforces the idle timeout.
 */
function auth_require_login(): void {
  if (!isset($_SESSION['user_id'])) {
    auth_redirect_to_login();
  }

  if (!isset($_SESSION['is_master_admin'])) {
    $email = $_SESSION['user_email'] ?? '';
    $isMaster = $email !== '' && strcasecmp($email, auth_master_admin_email()) === 0;
    $_SESSION['is_master_admin'] = $isMaster;
    if ($isMaster && ($_SESSION['user_role'] ?? '') !== 'Admin') {
      $_SESSION['user_role'] = 'Admin';
    }
  }

  if (auth_session_has_timed_out()) {
    auth_logout_and_redirect(true);
  }

  $_SESSION['last_activity'] = time();
}

/**
 * Require that the current user has one of the allowed roles.
 * Admin always passes through.
 */
function auth_require_role(array $allowed): void {
  auth_require_login();
  $role = auth_role();
  if ($role === 'Admin') {
    return;
  }
  if (!in_array($role, $allowed, true)) {
    http_response_code(403);
    exit('Forbidden: insufficient permissions.');
  }
}

function auth_role(): string {
  return $_SESSION['user_role'] ?? 'User';
}

function auth_is_admin(): bool {
  return auth_role() === 'Admin';
}

function auth_master_admin_email(): string {
  return MASTER_ADMIN_EMAIL;
}

function auth_is_master_admin(): bool {
  $email = $_SESSION['user_email'] ?? '';
  if ($email === '') {
    return false;
  }
  return strcasecmp($email, auth_master_admin_email()) === 0;
}

function auth_require_master_admin(): void {
  auth_require_login();
  if (!auth_is_master_admin()) {
    http_response_code(403);
    exit('Forbidden: master admin only.');
  }
}

function auth_is_operator(): bool {
  return auth_role() === 'Operator';
}

/**
 * Determines if the current session has been idle past the configured timeout.
 */
function auth_session_has_timed_out(): bool {
  $lastActivity = isset($_SESSION['last_activity']) ? (int) $_SESSION['last_activity'] : 0;
  if ($lastActivity <= 0) {
    return false;
  }

  return (time() - $lastActivity) > APP_IDLE_TIMEOUT;
}

/**
 * Clears the session and redirects to the login screen.
 *
 * @param bool $timedOut when true appends ?timeout=1 so the UI can show a helpful message
 */
function auth_logout_and_redirect(bool $timedOut = false): void {
  $_SESSION = [];
  if (session_id() !== '') {
    session_unset();
    session_destroy();
  }

  auth_redirect_to_login($timedOut ? ['timeout' => 1] : []);
}

/**
 * Redirect to the login page with optional query parameters.
 *
 * @param array<string, scalar> $params
 */
function auth_redirect_to_login(array $params = []): void {
  $target = 'login';
  if ($params) {
    $query = http_build_query($params);
    $target .= '?' . $query;
  }

  // Build absolute URL so it works behind Railway / Vercel reverse proxies
  // where a bare relative Location header can cause redirect loops.
  $proto = 'http';
  if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    $proto = 'https';
  } elseif (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') {
    $proto = 'https';
  }
  $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
  $absoluteTarget = $proto . '://' . $host . '/' . $target;

  header('Location: ' . $absoluteTarget);
  exit;
}
