<?php
declare(strict_types=1);

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';
auth_require_role(['Admin', 'Operator']);
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  header('Allow: POST');
  echo json_encode(['ok' => false, 'error' => 'Use POST to register bins.']);
  exit;
}

if (!firebase_is_ready()) {
  http_response_code(503);
  echo json_encode(['ok' => false, 'error' => 'Firebase connection is not configured.']);
  exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input) || !$input) {
  $input = $_POST ?? [];
}

$binIdRaw = trim((string) ($input['bin_id'] ?? ''));
$binId = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($binIdRaw));
if ($binId === '') {
  $binId = $binIdRaw;
}

if (strlen($binId) < 3 || strlen($binId) > 64 || !preg_match('/^[a-zA-Z0-9_-]+$/', $binId)) {
  http_response_code(422);
  echo json_encode(['ok' => false, 'error' => 'Bin ID must be 3-64 characters and only contain letters, numbers, dash, or underscore.']);
  exit;
}

if (find_bin($binId)) {
  http_response_code(409);
  echo json_encode(['ok' => false, 'error' => 'A bin with this ID already exists.']);
  exit;
}

$displayName = trim((string) ($input['name'] ?? ''));
$address = trim((string) ($input['address'] ?? ''));
$lat = isset($input['locationLat']) ? (float) $input['locationLat'] : 0.0;
$lng = isset($input['locationLng']) ? (float) $input['locationLng'] : 0.0;
$battery = isset($input['battery']) ? db_cast_percent($input['battery']) : 0;
$fillBio = isset($input['fill_bio']) ? db_cast_percent($input['fill_bio']) : 0;
$fillRec = isset($input['fill_rec']) ? db_cast_percent($input['fill_rec']) : 0;
$fillRes = isset($input['fill_res']) ? db_cast_percent($input['fill_res']) : 0;
$now = gmdate('c');

$bin = [
  'bin_id' => $binId,
  'name' => $displayName !== '' ? $displayName : $binId,
  'address' => $address !== '' ? $address : 'Himamaylan City',
  'status' => 'Offline',
  'battery' => $battery,
  'battery_level' => $battery,
  'batteryLevel' => $battery,
  'fill_bio' => $fillBio,
  'fill_rec' => $fillRec,
  'fill_res' => $fillRes,
  'fillBio' => $fillBio,
  'fillRecyclable' => $fillRec,
  'fillResidual' => $fillRes,
  'compartments' => [
    'biodegradable' => $fillBio,
    'recyclable' => $fillRec,
    'residual' => $fillRes,
  ],
  'gps' => [
    'lat' => $lat,
    'lng' => $lng,
  ],
  'location_lat' => $lat,
  'location_lng' => $lng,
  'locationLat' => $lat,
  'locationLng' => $lng,
  'last_ping' => null,
  'lastPing' => null,
  'last_ping_at' => null,
  'last_updated' => $now,
  'last_updated_at' => $now,
  'created_at' => $now,
  'created_by' => $_SESSION['user_id'] ?? null,
];

db_save_bins([$bin]);
$normalized = db_normalize_bin($bin, $binId);

echo json_encode([
  'ok' => true,
  'bin' => $normalized,
]);
