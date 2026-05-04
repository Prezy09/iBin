<?php

header("Content-Type: application/json");

// --- API KEY SECURITY ---
$API_KEY = "ibin2025";

// Allow key in header: X-API-KEY: ibin2025
$hdr = $_SERVER["HTTP_X_API_KEY"] ?? "";

// Also allow ?key=ibin2025 as fallback (optional)
$q = $_GET["key"] ?? "";

// Also allow body: api_key=ibin2025 (from device POST)
$bodyKey = $_POST['api_key'] ?? null;

if ($hdr !== $API_KEY && $q !== $API_KEY && $bodyKey !== $API_KEY) {
  http_response_code(401);
  echo json_encode(["ok" => false, "error" => "Unauthorized"]);
  exit;
}

require __DIR__ . '/../includes/db.php';

$rawBody = file_get_contents('php://input');
$input = $_POST;

if (!$input) {
  $input = json_decode($rawBody, true);
  if ($rawBody !== '' && !is_array($input)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON']);
    exit;
  }
}

if (!$input || !isset($input['bin_id'])) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'Missing bin_id']);
  exit;
}

// Map Pi field names to API expectations
if (isset($input['fill_recyclable'])) {
  $input['rec'] = $input['fill_recyclable'];
}
if (isset($input['fill_bio'])) {
  $input['bio'] = $input['fill_bio'];
}
if (isset($input['fill_residual'])) {
  $input['res'] = $input['fill_residual'];
}
$inputLastPing = $input['last_ping'] ?? null;

$binId = trim((string) $input['bin_id']);
$target = find_bin($binId);
if (!$target) {
  $target = [
    'bin_id' => $binId,
    'name' => $input['name'] ?? $binId,
    'type' => $input['type'] ?? 'General',
    'address' => $input['address'] ?? ($input['location'] ?? ''),
    'barangay' => $input['barangay'] ?? '',
    'status' => $input['status'] ?? 'online',
    'compartments' => [
      'biodegradable' => 0,
      'recyclable' => 0,
      'residual' => 0,
    ],
    'gps' => [
      'lat' => isset($input['lat']) ? floatval($input['lat']) : 0,
      'lng' => isset($input['lng']) ? floatval($input['lng']) : 0,
    ],
    'battery' => 0,
    'last_updated' => gmdate('c'),
  ];
}

if (!isset($target['compartments']) || !is_array($target['compartments'])) {
  $target['compartments'] = [
    'biodegradable' => 0,
    'recyclable' => 0,
    'residual' => 0,
  ];
}

$target['compartments']['biodegradable'] = read_percent($input, 'bio', $target['compartments']['biodegradable'] ?? 0);
$target['compartments']['recyclable'] = read_percent($input, 'rec', $target['compartments']['recyclable'] ?? 0);
$target['compartments']['residual'] = read_percent($input, 'res', $target['compartments']['residual'] ?? 0);
$target['fill_bio'] = $target['compartments']['biodegradable'];
$target['fillBio'] = $target['compartments']['biodegradable'];
$target['fill_rec'] = $target['compartments']['recyclable'];
$target['fillRecyclable'] = $target['compartments']['recyclable'];
$target['fill_res'] = $target['compartments']['residual'];
$target['fillResidual'] = $target['compartments']['residual'];

if (isset($input['battery']) && is_numeric($input['battery'])) {
  $target['battery'] = clamp_percent($input['battery']);
}
if (!isset($target['battery'])) {
  $target['battery'] = 0;
}
$target['battery_level'] = $target['battery'];
$target['batteryLevel'] = $target['battery'];

if (!isset($target['gps']) || !is_array($target['gps'])) {
  $target['gps'] = ['lat' => 0, 'lng' => 0];
}
if (isset($input['lat']) && is_numeric($input['lat'])) {
  $target['gps']['lat'] = floatval($input['lat']);
}
if (isset($input['lng']) && is_numeric($input['lng'])) {
  $target['gps']['lng'] = floatval($input['lng']);
}
$target['location_lat'] = $target['gps']['lat'];
$target['location_lng'] = $target['gps']['lng'];
$target['locationLat'] = $target['gps']['lat'];
$target['locationLng'] = $target['gps']['lng'];

$nowIso = gmdate('c');
$pingIso = $inputLastPing ?: $nowIso;
$target['last_updated'] = $pingIso;
$target['last_ping'] = $pingIso;
$target['last_ping_at'] = $pingIso;
$target['lastPing'] = $pingIso;
if (isset($input['name'])) {
  $target['name'] = $input['name'];
}
if (isset($input['address'])) {
  $target['address'] = $input['address'];
}
if (isset($input['barangay'])) {
  $target['barangay'] = $input['barangay'];
}
if (isset($input['type'])) {
  $target['type'] = $input['type'];
}
$target['status'] = $input['status'] ?? 'online';
$target['connectivity'] = $input['connectivity'] ?? ($target['status'] === 'online' ? 'online' : ($target['connectivity'] ?? ''));

db_save_bins([$target]);
echo json_encode(['ok'=>true, 'bin'=>$target]);

function clamp_percent($value): int {
  $num = (int) round(floatval($value));
  if ($num < 0) {
    return 0;
  }
  if ($num > 100) {
    return 100;
  }
  return $num;
}

function read_percent(array $input, string $key, $fallback): int {
  if (!array_key_exists($key, $input)) {
    return (int) $fallback;
  }
  $value = $input[$key];
  if ($value === '' || $value === null) {
    return (int) $fallback;
  }
  if (!is_numeric($value)) {
    return (int) $fallback;
  }
  return clamp_percent($value);
}
