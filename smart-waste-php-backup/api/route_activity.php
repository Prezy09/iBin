<?php
declare(strict_types=1);

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';
auth_require_login();

$storagePath = __DIR__ . '/../data/route_activity.json';
if (!is_dir(dirname($storagePath))) {
  mkdir(dirname($storagePath), 0775, true);
}
if (!file_exists($storagePath)) {
  file_put_contents($storagePath, json_encode([
    'updated_at' => null,
    'status' => 'idle',
    'completed_at' => null,
    'stops' => [],
  ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function route_activity_load(string $path): array {
  $raw = json_decode((string) file_get_contents($path), true);
  if (!is_array($raw)) {
    return [
      'updated_at' => null,
      'status' => 'idle',
      'completed_at' => null,
      'stops' => [],
    ];
  }
  if (!isset($raw['stops']) || !is_array($raw['stops'])) {
    $raw['stops'] = [];
  }
  if (!isset($raw['status']) || !is_string($raw['status'])) {
    $raw['status'] = 'idle';
  }
  if (!array_key_exists('completed_at', $raw)) {
    $raw['completed_at'] = null;
  }
  return $raw;
}

function route_activity_save(string $path, array $data): void {
  file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function route_activity_refresh_status(array &$data): void {
  if (($data['status'] ?? '') === 'completed') {
    return;
  }
  $stops = isset($data['stops']) && is_array($data['stops']) ? $data['stops'] : [];
  if (!$stops) {
    $data['status'] = 'idle';
    return;
  }
  foreach ($stops as $stop) {
    if (($stop['status'] ?? 'scheduled') !== 'delivered') {
      $data['status'] = 'in_progress';
      return;
    }
  }
  $data['status'] = 'awaiting_confirmation';
}

function route_activity_can_confirm(array $data): bool {
  $stops = isset($data['stops']) && is_array($data['stops']) ? $data['stops'] : [];
  if (!$stops) {
    return false;
  }
  foreach ($stops as $stop) {
    if (($stop['status'] ?? 'scheduled') !== 'delivered') {
      return false;
    }
  }
  return true;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$role = $_SESSION['user_role'] ?? 'User';

if ($method === 'GET') {
  echo json_encode(route_activity_load($storagePath));
  exit;
}

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Invalid payload']);
  exit;
}

$action = $payload['action'] ?? '';
$current = route_activity_load($storagePath);

switch ($action) {
  case 'sync_route':
    $stopsInput = is_array($payload['stops'] ?? null) ? $payload['stops'] : [];
    $stops = [];
    foreach ($stopsInput as $stop) {
      if (!empty($stop['isLandfill'])) {
        continue;
      }
      $binId = trim((string) ($stop['bin_id'] ?? $stop['id'] ?? ''));
      if ($binId === '') {
        continue;
      }
      $gps = null;
      if (isset($stop['gps']) && is_array($stop['gps'])) {
        $lat = isset($stop['gps']['lat']) ? floatval($stop['gps']['lat']) : null;
        $lng = isset($stop['gps']['lng']) ? floatval($stop['gps']['lng']) : null;
        if ($lat !== null && $lng !== null) {
          $gps = ['lat' => $lat, 'lng' => $lng];
        }
      }
      $compartments = null;
      if (isset($stop['compartments']) && is_array($stop['compartments'])) {
        $compartments = [
          'biodegradable' => (int) round($stop['compartments']['biodegradable'] ?? $stop['compartments']['bio'] ?? 0),
          'recyclable' => (int) round($stop['compartments']['recyclable'] ?? $stop['compartments']['rec'] ?? 0),
          'residual' => (int) round($stop['compartments']['residual'] ?? $stop['compartments']['res'] ?? 0),
        ];
      }
      $stops[] = [
        'bin_id' => $binId,
        'name' => $stop['name'] ?? $binId,
        'address' => $stop['address'] ?? '',
        'status' => 'scheduled',
        'updated_at' => gmdate('c'),
        'last_event' => 'Pickup scheduled',
        'destination' => $stop['destination'] ?? 'Landfill',
        'gps' => $gps,
        'compartments' => $compartments,
        'fill_avg' => isset($stop['fill_avg']) ? (int) $stop['fill_avg'] : null,
        'battery' => isset($stop['battery']) ? (int) $stop['battery'] : null,
        'order' => isset($stop['order']) ? (int) $stop['order'] : null,
      ];
    }
    $current = [
      'updated_at' => gmdate('c'),
      'status' => $stops ? 'scheduled' : 'idle',
      'completed_at' => null,
      'stops' => $stops,
    ];
    route_activity_save($storagePath, $current);
    echo json_encode(['ok' => true, 'stops' => count($stops)]);
    break;

  case 'mark_done':
    $binId = trim((string) ($payload['bin_id'] ?? ''));
    if ($binId === '') {
      http_response_code(400);
      echo json_encode(['ok' => false, 'error' => 'Missing bin_id']);
      break;
    }
    if (!in_array($role, ['Admin', 'Operator'], true)) {
      http_response_code(403);
      echo json_encode(['ok' => false, 'error' => 'Forbidden']);
      break;
    }
    $updated = false;
    foreach ($current['stops'] as &$stop) {
      if (($stop['bin_id'] ?? '') === $binId) {
        $stop['status'] = 'delivered';
        $stop['updated_at'] = gmdate('c');
        $stop['last_event'] = sprintf('Delivered by %s', $role);
        $updated = true;
        break;
      }
    }
    if (!$updated) {
      http_response_code(404);
      echo json_encode(['ok' => false, 'error' => 'Bin not tracked']);
      break;
    }
    $current['updated_at'] = gmdate('c');
    route_activity_refresh_status($current);
    route_activity_save($storagePath, $current);
    echo json_encode(['ok' => true, 'bin_id' => $binId]);
    break;

  case 'confirm_route':
    if (!in_array($role, ['Admin', 'Operator'], true)) {
      http_response_code(403);
      echo json_encode(['ok' => false, 'error' => 'Forbidden']);
      break;
    }
    if (($current['status'] ?? '') === 'completed') {
      echo json_encode(['ok' => true, 'completed_at' => $current['completed_at'] ?? gmdate('c')]);
      break;
    }
    if (!route_activity_can_confirm($current)) {
      http_response_code(409);
      echo json_encode(['ok' => false, 'error' => 'Pending stops']);
      break;
    }
    $current['status'] = 'completed';
    $current['completed_at'] = gmdate('c');
    $current['updated_at'] = gmdate('c');
    route_activity_save($storagePath, $current);
    echo json_encode(['ok' => true, 'completed_at' => $current['completed_at']]);
    break;

  default:
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Unsupported action']);
    break;
}
