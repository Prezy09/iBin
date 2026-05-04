<?php
header('Content-Type: application/json');
require __DIR__ . '/../includes/db.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST') {
  $payload = json_decode(file_get_contents('php://input'), true);
  if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
  }
  $saved = db_save_route_plan($payload);
  echo json_encode(['status' => $saved ? 'saved' : 'skipped']);
  exit;
}

if ($method !== 'GET') {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed']);
  exit;
}

$bins = db_get_bins();
$threshold = isset($_GET['threshold']) ? intval($_GET['threshold']) : 70;

$targets = [];
foreach ($bins as $bin) {
  $maxFill = bin_max_fill($bin);
  if ($maxFill >= $threshold) {
    $targets[] = $bin;
  }
}
if (empty($targets)) {
  echo json_encode(['order' => [], 'count' => 0]);
  exit;
}

$visited = [];
$current = $targets[0];
$visited[] = $current['bin_id'];
while (count($visited) < count($targets)) {
  $best = null;
  $bestDistance = INF;
  foreach ($targets as $candidate) {
    $id = $candidate['bin_id'];
    if (in_array($id, $visited, true)) {
      continue;
    }
    $distance = bin_distance_km($current, $candidate);
    if ($distance < $bestDistance) {
      $bestDistance = $distance;
      $best = $candidate;
    }
  }
  if (!$best) {
    break;
  }
  $visited[] = $best['bin_id'];
  $current = $best;
}
echo json_encode(['order' => $visited, 'count' => count($visited)]);

function bin_max_fill(array $bin): int {
  if (!empty($bin['compartments']) && is_array($bin['compartments'])) {
    return max(array_map('intval', $bin['compartments']));
  }
  $bio = (int) ($bin['fill_bio'] ?? 0);
  $rec = (int) ($bin['fill_rec'] ?? 0);
  $res = (int) ($bin['fill_res'] ?? 0);
  return max($bio, $rec, $res);
}

function bin_distance_km(array $a, array $b): float {
  $coordA = bin_coords($a);
  $coordB = bin_coords($b);
  if (!$coordA || !$coordB) {
    return INF;
  }
  $earthRadius = 6371;
  $lat1 = deg2rad($coordA['lat']);
  $lat2 = deg2rad($coordB['lat']);
  $deltaLat = deg2rad($coordB['lat'] - $coordA['lat']);
  $deltaLng = deg2rad($coordB['lng'] - $coordA['lng']);
  $aVal = sin($deltaLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($deltaLng / 2) ** 2;
  $c = 2 * asin(min(1, sqrt($aVal)));
  return $earthRadius * $c;
}

function bin_coords(array $bin): ?array {
  if (isset($bin['gps']['lat'], $bin['gps']['lng'])) {
    return ['lat' => (float) $bin['gps']['lat'], 'lng' => (float) $bin['gps']['lng']];
  }
  if (isset($bin['location_lat'], $bin['location_lng'])) {
    return ['lat' => (float) $bin['location_lat'], 'lng' => (float) $bin['location_lng']];
  }
  return null;
}
