<?php
header('Content-Type: application/json');
require __DIR__ . '/../includes/db.php';

$bins = db_get_bins();
echo json_encode([
  'bins' => $bins,
  'count' => count($bins),
  'source' => db_bins_source(),
  'generated_at' => gmdate('c'),
]);
