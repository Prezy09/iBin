<?php
declare(strict_types=1);

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';
auth_require_role(['Admin', 'Operator']);
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(405);
  header('Allow: GET');
  echo json_encode(['ok' => false, 'error' => 'Use GET to scan for bins.']);
  exit;
}

$binIdRaw = trim((string) ($_GET['id'] ?? $_GET['bin_id'] ?? ''));
if ($binIdRaw === '') {
  http_response_code(422);
  echo json_encode(['ok' => false, 'error' => 'Provide a bin_id query parameter.']);
  exit;
}
$binId = strtolower($binIdRaw);

$existingBins = db_get_bins();
foreach ($existingBins as $existing) {
  $existingId = strtolower((string) ($existing['bin_id'] ?? $existing['id'] ?? ''));
  if ($existingId === $binId) {
    http_response_code(409);
    echo json_encode(['ok' => false, 'error' => 'This bin is already registered in the web app.']);
    exit;
  }
}

$catalog = db_sample_bins();
$match = null;
foreach ($catalog as $candidate) {
  $candidateId = strtolower((string) ($candidate['bin_id'] ?? $candidate['id'] ?? ''));
  if ($candidateId === $binId) {
    $normalized = db_normalize_bin($candidate, $candidate['bin_id'] ?? null);
    if ($normalized) {
      $match = $normalized;
    }
    break;
  }
}

if (!$match) {
  http_response_code(404);
  echo json_encode(['ok' => false, 'error' => 'No matching bin found in the database.']);
  exit;
}

echo json_encode([
  'ok' => true,
  'bin' => $match,
]);
