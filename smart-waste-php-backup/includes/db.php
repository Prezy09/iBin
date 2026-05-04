<?php
require_once __DIR__ . '/../config/firebase.php';

function db_bins_source(): string {
  return $GLOBALS['db_bins_source'] ?? 'local-cache';
}

function db_set_bins_source(string $source): void {
  $GLOBALS['db_bins_source'] = $source;
}

function db_bins_limit(): int {
  static $limit = null;
  if ($limit !== null) {
    return $limit;
  }
  $env = firebase_env();
  $value = isset($env['FIREBASE_BINS_LIMIT']) ? (int) $env['FIREBASE_BINS_LIMIT'] : 0;
  $limit = $value > 0 ? $value : 0;
  return $limit;
}

function db_get_bins(): array {
  $limit = db_bins_limit();
  $query = [];
  if ($limit > 0) {
    $query = [
      'orderBy' => json_encode('last_updated'),
      'limitToLast' => $limit,
    ];
  }
  if (firebase_is_ready()) {
    try {
      $raw = firebase_db_get('bins', $query) ?? [];
      $bins = db_normalize_bins($raw);
      if ($limit > 0 && count($bins) > 1) {
        usort($bins, static function ($a, $b) {
          return strcmp($b['last_updated'] ?? '', $a['last_updated'] ?? '');
        });
      }
      if ($bins) {
        db_set_bins_source('firebase');
        return $bins;
      }
    } catch (Throwable $e) {
      $isIndexError = stripos($e->getMessage(), 'index not defined') !== false;
      if ($isIndexError) {
        try {
          // Retry without orderBy/limit if Firebase rules lack the index.
          $raw = firebase_db_get('bins') ?? [];
          $bins = db_normalize_bins($raw);
          if ($limit > 0 && count($bins) > 1) {
            usort($bins, static function ($a, $b) {
              return strcmp($b['last_updated'] ?? '', $a['last_updated'] ?? '');
            });
            $bins = array_slice($bins, 0, $limit);
          }
          if ($bins) {
            db_set_bins_source('firebase');
            return $bins;
          }
        } catch (Throwable $e2) {
          error_log('[firebase] Failed to load bins after index retry: ' . $e2->getMessage());
        }
      } else {
        error_log('[firebase] Failed to load bins: ' . $e->getMessage());
      }
    }
    // Firebase reachable but no bins exist yet; return empty and keep source as firebase.
    db_set_bins_source('firebase');
    return [];
  }
  db_set_bins_source('local-cache');
  $fallback = db_sample_bins();
  if ($limit > 0 && count($fallback) > $limit) {
    $fallback = array_slice($fallback, 0, $limit);
  }
  return $fallback;
}

function db_save_bins($bins): void {
  if (!firebase_is_ready()) {
    error_log('[firebase] Skipped saving bins because Firebase is not configured.');
    return;
  }
  $payload = [];
  foreach ((array) $bins as $key => $bin) {
    $normalized = db_normalize_bin($bin, is_string($key) ? $key : null);
    if (!$normalized || empty($normalized['bin_id'])) {
      continue;
    }
    $payload[$normalized['bin_id']] = $normalized;
  }
  if (!$payload) {
    return;
  }
  try {
    firebase_db_patch('bins', $payload);
    db_save_bin_locations($payload);
  } catch (Throwable $e) {
    error_log('[firebase] Failed to save bins: ' . $e->getMessage());
  }
}

function find_bin($id) {
  if (!$id) {
    return null;
  }
  if (firebase_is_ready()) {
    try {
      $bin = firebase_db_get('bins/' . $id);
      if (is_array($bin)) {
        return db_normalize_bin($bin, $id);
      }
    } catch (Throwable $e) {
      error_log('[firebase] find_bin error: ' . $e->getMessage());
    }
  }
  foreach (db_sample_bins() as $bin) {
    if (($bin['bin_id'] ?? null) === $id) {
      return $bin;
    }
  }
  return null;
}

function db_sample_bins(): array {
  static $cache = null;
  if ($cache !== null) {
    return $cache;
  }
  $sources = [
    __DIR__ . '/../ibin.json',
    __DIR__ . '/../data/bins.json',
  ];
  foreach ($sources as $path) {
    if (!file_exists($path)) {
      continue;
    }
    $content = json_decode((string) file_get_contents($path), true);
    if (isset($content['bins']) && is_array($content['bins'])) {
      $cache = db_normalize_bins($content['bins']);
      return $cache;
    }
    if (is_array($content)) {
      $cache = db_normalize_bins($content);
      return $cache;
    }
  }
  $cache = [];
  return $cache;
}

function db_normalize_bins($raw): array {
  $bins = [];
  if (!is_array($raw)) {
    return $bins;
  }
  if (array_is_list($raw)) {
    foreach ($raw as $bin) {
      $normalized = db_normalize_bin($bin);
      if ($normalized) {
        $bins[] = $normalized;
      }
    }
  } else {
    foreach ($raw as $key => $bin) {
      $normalized = db_normalize_bin($bin, (string) $key);
      if ($normalized) {
        $bins[] = $normalized;
      }
    }
  }
  return $bins;
}

function db_normalize_bin($bin, ?string $key = null): ?array {
  if (!is_array($bin)) {
    return null;
  }
  $id = $bin['bin_id'] ?? $bin['id'] ?? $key;
  if (!$id) {
    return null;
  }
  $bin['bin_id'] = $id;
  if (!isset($bin['name'])) {
    $bin['name'] = $id;
  }

  $bioValue = $bin['compartments']['biodegradable'] ?? $bin['fill_bio'] ?? $bin['fillBio'] ?? $bin['bio'] ?? 0;
  $recValue = $bin['compartments']['recyclable'] ?? $bin['fill_rec'] ?? $bin['fillRecyclable'] ?? $bin['rec'] ?? 0;
  $resValue = $bin['compartments']['residual'] ?? $bin['fill_res'] ?? $bin['fillResidual'] ?? $bin['res'] ?? 0;
  $bin['compartments'] = [
    'biodegradable' => db_cast_percent($bioValue),
    'recyclable' => db_cast_percent($recValue),
    'residual' => db_cast_percent($resValue),
  ];
  $bin['fill_bio'] = $bin['compartments']['biodegradable'];
  $bin['fillBio'] = $bin['compartments']['biodegradable'];
  $bin['fill_rec'] = $bin['compartments']['recyclable'];
  $bin['fillRecyclable'] = $bin['compartments']['recyclable'];
  $bin['fill_res'] = $bin['compartments']['residual'];
  $bin['fillResidual'] = $bin['compartments']['residual'];

  $batteryValue = $bin['battery'] ?? $bin['battery_level'] ?? $bin['batteryLevel'] ?? 0;
  $bin['battery'] = db_cast_percent($batteryValue);
  $bin['battery_level'] = $bin['battery'];
  $bin['batteryLevel'] = $bin['battery'];

  $latSources = [
    $bin['gps']['lat'] ?? null,
    $bin['location_lat'] ?? null,
    $bin['locationLat'] ?? null,
    $bin['lat'] ?? null,
  ];
  $lngSources = [
    $bin['gps']['lng'] ?? null,
    $bin['location_lng'] ?? null,
    $bin['locationLng'] ?? null,
    $bin['lng'] ?? null,
  ];
  $lat = db_pick_float($latSources);
  $lng = db_pick_float($lngSources);
  if ($lat !== null && $lng !== null) {
    $bin['gps'] = ['lat' => $lat, 'lng' => $lng];
    $bin['location_lat'] = $lat;
    $bin['location_lng'] = $lng;
    $bin['locationLat'] = $lat;
    $bin['locationLng'] = $lng;
  } elseif (isset($bin['gps']) && is_array($bin['gps'])) {
    if (isset($bin['gps']['lat']) && is_numeric($bin['gps']['lat'])) {
      $bin['gps']['lat'] = (float) $bin['gps']['lat'];
      $bin['location_lat'] = $bin['locationLat'] = $bin['gps']['lat'];
    }
    if (isset($bin['gps']['lng']) && is_numeric($bin['gps']['lng'])) {
      $bin['gps']['lng'] = (float) $bin['gps']['lng'];
      $bin['location_lng'] = $bin['locationLng'] = $bin['gps']['lng'];
    }
  } else {
    unset($bin['gps']);
    $bin['location_lat'] = $bin['location_lng'] = $bin['locationLat'] = $bin['locationLng'] = null;
  }

  if (!isset($bin['last_updated'])) {
    $bin['last_updated'] = $bin['last_updated_at'] ?? $bin['updated_at'] ?? gmdate('c');
  }
  $bin['last_updated_at'] = $bin['last_updated'];
  $bin['lastUpdated'] = $bin['last_updated'];

  $lastPing = $bin['last_ping'] ?? $bin['lastPing'] ?? $bin['last_ping_at'] ?? $bin['lastPingAt'] ?? null;
  if ($lastPing !== null && $lastPing !== '') {
    $bin['last_ping'] = $lastPing;
    $bin['lastPing'] = $lastPing;
    $bin['last_ping_at'] = $lastPing;
  } else {
    $bin['last_ping'] = $bin['lastPing'] = $bin['last_ping_at'] = null;
  }

  if (!isset($bin['status'])) {
    $bin['status'] = 'Offline';
  }

  return $bin;
}

function db_save_bin_locations(array $bins): void {
  if (!firebase_is_ready()) {
    return;
  }
  $locations = [];
  foreach ($bins as $bin) {
    if (!is_array($bin)) {
      continue;
    }
    $binId = $bin['bin_id'] ?? null;
    if (!$binId) {
      continue;
    }
    $lat = $bin['gps']['lat'] ?? null;
    $lng = $bin['gps']['lng'] ?? null;
    if ($lat === null || $lng === null) {
      continue;
    }
    $lat = (float) $lat;
    $lng = (float) $lng;
    if (!is_finite($lat) || !is_finite($lng)) {
      continue;
    }
    $locations[$binId] = [
      'lat' => $lat,
      'lng' => $lng,
      'updated_at' => $bin['last_updated'] ?? gmdate('c'),
    ];
  }
  if (!$locations) {
    return;
  }
  try {
    firebase_db_patch('bin_locations', $locations);
  } catch (Throwable $e) {
    error_log('[firebase] Failed to save bin location snapshots: ' . $e->getMessage());
  }
}

function db_cast_percent($value): int {
  $num = (int) round((float) $value);
  if ($num < 0) {
    return 0;
  }
  if ($num > 100) {
    return 100;
  }
  return $num;
}

function db_pick_float(array $sources): ?float {
  foreach ($sources as $value) {
    if ($value === null || $value === '') {
      continue;
    }
    $float = (float) $value;
    if (is_finite($float)) {
      return $float;
    }
  }
  return null;
}

function db_save_route_plan(array $plan): bool {
  if (!firebase_is_ready()) {
    error_log('[firebase] Route plan skipped because Firebase is not configured.');
    return false;
  }
  $stops = [];
  $rawStops = is_array($plan['stops'] ?? null) ? $plan['stops'] : [];
  foreach ($rawStops as $index => $stop) {
    if (!is_array($stop)) {
      continue;
    }
    $isLandfill = !empty($stop['is_landfill']);
    $binId = $stop['bin_id'] ?? null;
    if (!$isLandfill && !$binId) {
      continue;
    }
    $gps = null;
    if (isset($stop['gps']['lat'], $stop['gps']['lng'])) {
      $lat = (float) $stop['gps']['lat'];
      $lng = (float) $stop['gps']['lng'];
      if (is_finite($lat) && is_finite($lng)) {
        $gps = ['lat' => $lat, 'lng' => $lng];
      }
    }
    $stops[] = [
      'order' => (int) ($stop['order'] ?? $index),
      'bin_id' => $binId,
      'name' => $stop['name'] ?? ($binId ?: 'Stop'),
      'address' => $stop['address'] ?? '',
      'fill_avg' => (int) ($stop['fill_avg'] ?? 0),
      'is_landfill' => $isLandfill,
      'battery' => isset($stop['battery']) ? (int) $stop['battery'] : null,
      'gps' => $gps,
    ];
  }
  $data = [
    'threshold' => (int) ($plan['threshold'] ?? 0),
    'start_bin' => $plan['start_bin'] ?? '',
    'using_sample' => !empty($plan['using_sample']),
    'avg_speed_kmh' => (int) ($plan['avg_speed_kmh'] ?? 0),
    'total_distance_km' => round((float) ($plan['total_distance_km'] ?? 0), 3),
    'travel_minutes' => round((float) ($plan['travel_minutes'] ?? 0), 2),
    'bins_scheduled' => (int) ($plan['bins_scheduled'] ?? max(count($stops) - 1, 0)),
    'optimized_at' => $plan['optimized_at'] ?? gmdate('c'),
    'stops' => $stops,
    'meta' => [
      'source' => $plan['source'] ?? 'collection_ui',
      'candidate_bins' => (int) ($plan['candidate_bins'] ?? 0),
    ],
  ];
  try {
    firebase_db_put('routes/current', $data);
    $historyKey = gmdate('Ymd_His') . '_' . substr(sha1($data['optimized_at'] . ($data['start_bin'] ?? '')), 0, 6);
    firebase_db_put('routes/history/' . $historyKey, $data);
    return true;
  } catch (Throwable $e) {
    error_log('[firebase] Failed to save route plan: ' . $e->getMessage());
    return false;
  }
}
