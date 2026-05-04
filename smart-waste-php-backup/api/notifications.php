<?php
declare(strict_types=1);

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';
auth_require_login();
require_once __DIR__ . '/../includes/db.php';

// Keep activity timestamps aligned to PH local time.
date_default_timezone_set('Asia/Manila');

function format_notification_time(?string $value): ?string {
  if (!$value) {
    return null;
  }
  $timestamp = strtotime($value);
  if (!$timestamp) {
    return null;
  }
  return date('M j, Y g:i A', $timestamp);
}

$role = $_SESSION['user_role'] ?? 'User';
$isMasterAdmin = auth_is_master_admin();
$bins = db_get_bins();
$highBins = [];
$now = time();
foreach ($bins as $bin) {
  $compartments = $bin['compartments'] ?? [];
  $avg = 0;
  if ($compartments && is_array($compartments)) {
    $avg = (int) round(array_sum($compartments) / max(count($compartments), 1));
  } else {
    $avg = max(
      (int) ($bin['fill_bio'] ?? 0),
      (int) ($bin['fill_rec'] ?? 0),
      (int) ($bin['fill_res'] ?? 0)
    );
  }
  if ($avg >= 80) {
    $highBins[] = [
      'bin_id' => $bin['bin_id'] ?? $bin['id'] ?? '',
      'name' => $bin['name'] ?? $bin['bin_id'] ?? 'Bin',
      'fill' => $avg,
      'address' => $bin['address'] ?? '',
    ];
  }
}

$routePath = __DIR__ . '/../data/route_activity.json';
$routeData = [];
if (file_exists($routePath)) {
  $routeData = json_decode((string) file_get_contents($routePath), true) ?: [];
}
$stops = isset($routeData['stops']) && is_array($routeData['stops']) ? $routeData['stops'] : [];
$routeStatus = isset($routeData['status']) && is_string($routeData['status']) ? $routeData['status'] : 'idle';
$routeCompletedAt = $routeData['completed_at'] ?? null;
$scheduled = array_filter($stops, static fn($stop) => ($stop['status'] ?? '') === 'scheduled');
$delivered = array_filter($stops, static fn($stop) => ($stop['status'] ?? '') === 'delivered');

$alerts = [];
if ($scheduled) {
  $names = array_map(static fn($stop) => $stop['name'] ?? $stop['bin_id'] ?? '', array_slice($scheduled, 0, 3));
  $scheduledAt = format_notification_time($routeData['updated_at'] ?? null);
  $detailParts = [];
  if ($scheduledAt) {
    $detailParts[] = 'Scheduled ' . $scheduledAt;
  }
  $nameList = implode(', ', array_filter($names));
  if ($nameList) {
    $detailParts[] = $nameList;
  }
  $detailText = $detailParts ? implode(' · ', $detailParts) : '';
  $alerts[] = [
    'type' => 'pickup',
    'severity' => 'warning',
    'icon' => 'bi-truck',
    'title' => count($scheduled) . ' pickup(s) scheduled',
    'detail' => $detailText,
  ];
}
if ($delivered) {
  $latestDelivered = null;
  $latestTs = null;
  foreach ($delivered as $stop) {
    $ts = isset($stop['updated_at']) ? strtotime((string) $stop['updated_at']) : null;
    if ($ts && (!$latestTs || $ts > $latestTs)) {
      $latestTs = $ts;
      $latestDelivered = $stop;
    }
  }
  $latestTime = $latestDelivered ? format_notification_time($latestDelivered['updated_at'] ?? null) : null;
  $detailText = $latestTime ? 'Completed ' . $latestTime : '';
  if ($latestDelivered) {
    $name = $latestDelivered['name'] ?? $latestDelivered['bin_id'] ?? '';
    if ($name) {
      $detailText = trim($detailText . ($detailText ? ' · ' : '') . $name);
    }
  }
  $alerts[] = [
    'type' => 'delivery',
    'severity' => 'success',
    'icon' => 'bi-check2-circle',
    'title' => count($delivered) . ' delivery updates',
    'detail' => $detailText ?: null,
  ];
}
$awaitingConfirmation = $routeStatus === 'awaiting_confirmation';
if ($awaitingConfirmation) {
  $alerts[] = [
    'type' => 'route',
    'severity' => 'info',
    'icon' => 'bi-clipboard-check',
    'title' => 'Awaiting pickup confirmation',
    'detail' => 'All scheduled stops marked done.',
  ];
}
if ($routeStatus === 'completed') {
  $confirmedAtFormatted = '';
  if ($routeCompletedAt) {
    $confirmedTs = strtotime((string) $routeCompletedAt);
    $confirmedAtFormatted = $confirmedTs ? date('M j, Y g:i A', $confirmedTs) : (string) $routeCompletedAt;
  }
  $alerts[] = [
    'type' => 'route_complete',
    'severity' => 'success',
    'icon' => 'bi-flag',
    'title' => 'Pickup route confirmed',
    'detail' => $confirmedAtFormatted ? 'Confirmed at ' . $confirmedAtFormatted : 'Confirmation received from operators.',
  ];
}
foreach ($highBins as $binInfo) {
  $alerts[] = [
    'type' => 'bin',
    'severity' => 'danger',
    'icon' => 'bi-exclamation-triangle',
    'title' => $binInfo['name'] . ' high fill',
    'detail' => 'Fill level at ' . $binInfo['fill'] . '%',
  ];
}

$pendingRequests = [];
if ($isMasterAdmin && firebase_is_ready()) {
  try {
    $pendingRequests = firebase_access_requests_pending();
  } catch (Throwable $e) {
    $pendingRequests = [];
  }
}
if ($pendingRequests) {
  $latest = $pendingRequests[0];
  $latestEmail = $latest['email'] ?? '';
  $alerts[] = [
    'type' => 'access_request',
    'severity' => 'info',
    'icon' => 'bi-person-plus',
    'title' => count($pendingRequests) . ' access request(s) pending',
    'detail' => $latestEmail ? ('Latest: ' . $latestEmail) : null,
  ];
}

echo json_encode([
  'role' => $role,
  'generated_at' => gmdate('c'),
  'counts' => [
    'pickups' => count($scheduled),
    'delivered' => count($delivered),
    'bins_high' => count($highBins),
    'routes_confirmed' => $routeStatus === 'completed' ? 1 : 0,
    'access_requests' => count($pendingRequests),
  ],
  'alerts' => $alerts,
  'stops' => $stops,
]);
