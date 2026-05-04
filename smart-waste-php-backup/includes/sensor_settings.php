<?php
// includes/sensor_settings.php
// Sensor calibration + adjustment storage for ultrasonic fill level, YOLOv5 camera, and NEO-6M GPS.

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/firebase.php';

function sensor_settings_defaults(): array {
  return [
    'ultrasonic' => [
      'collection_threshold_pct' => 75,
      'alert_pct' => 60,
      'critical_pct' => 90,
      'empty_distance_cm' => 32.0,
      'full_distance_cm' => 8.0,
      'poll_interval_sec' => 5,
      'smoothing_samples' => 5,
      'offline_timeout_sec' => 45,
    ],
    'camera' => [
      'model' => 'YOLOv5',
      'min_confidence' => 0.55,
      'nms_iou' => 0.45,
      'frame_interval_sec' => 1.5,
      'min_consecutive_hits' => 2,
      'exposure_compensation' => -0.1,
    ],
    'gps' => [
      'module' => 'NEO-6M',
      'update_rate_hz' => 1,
      'min_satellites' => 5,
      'hdop_max' => 2.5,
      'drift_correction_m' => 3,
      'offset_lat' => 0.0,
      'offset_lng' => 0.0,
      'heartbeat_seconds' => 30,
    ],
    'meta' => [
      'updated_at' => null,
      'updated_by' => null,
    ],
  ];
}

function sensor_settings_cache_path(): string {
  return __DIR__ . '/../data/sensor_settings.json';
}

function sensor_settings_table_ensure(PDO $pdo): void {
  $pdo->exec(
    "CREATE TABLE IF NOT EXISTS sensor_settings (
      id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
      settings_json LONGTEXT NOT NULL,
      updated_by VARCHAR(100) DEFAULT NULL,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
  );
  $pdo->exec("INSERT IGNORE INTO sensor_settings (id, settings_json) VALUES (1, '{}')");
}

function sensor_settings_load(): array {
  $defaults = sensor_settings_defaults();

  try {
    $pdo = get_pdo();
    sensor_settings_table_ensure($pdo);
    $stmt = $pdo->query("SELECT settings_json, updated_by, updated_at FROM sensor_settings WHERE id = 1 LIMIT 1");
    $row = $stmt->fetch();
    if ($row) {
      $decoded = json_decode((string) $row['settings_json'], true);
      if (is_array($decoded)) {
        $defaults = array_replace_recursive($defaults, $decoded);
      }
      if (!empty($row['updated_by'])) {
        $defaults['meta']['updated_by'] = $row['updated_by'];
      }
      if (!empty($row['updated_at'])) {
        $defaults['meta']['updated_at'] = gmdate('c', strtotime($row['updated_at']));
      }
      return $defaults;
    }
  } catch (Throwable $e) {
    error_log('[settings] Unable to load settings from database: ' . $e->getMessage());
  }

  if (firebase_is_ready()) {
    try {
      $remote = firebase_db_get('settings/sensors');
      if (is_array($remote)) {
        return array_replace_recursive($defaults, $remote);
      }
    } catch (Throwable $e) {
      error_log('[firebase] Unable to load sensor settings: ' . $e->getMessage());
    }
  }

  $cachePath = sensor_settings_cache_path();
  if (file_exists($cachePath)) {
    $fallback = json_decode((string) file_get_contents($cachePath), true);
    if (is_array($fallback)) {
      return array_replace_recursive($defaults, $fallback);
    }
  }

  return $defaults;
}

function sensor_settings_save(array $settings, ?string $updatedBy = null): array {
  $nowIso = gmdate('c');
  $settings['meta']['updated_at'] = $settings['meta']['updated_at'] ?? $nowIso;
  $settings['meta']['updated_by'] = $updatedBy ?? ($settings['meta']['updated_by'] ?? null);

  $result = [
    'db_saved' => false,
    'firebase_saved' => false,
    'cache_saved' => false,
  ];
  try {
    $pdo = get_pdo();
    sensor_settings_table_ensure($pdo);
    $stmt = $pdo->prepare(
      "INSERT INTO sensor_settings (id, settings_json, updated_by, updated_at)
       VALUES (1, :json, :updated_by, NOW())
       ON DUPLICATE KEY UPDATE settings_json = VALUES(settings_json), updated_by = VALUES(updated_by), updated_at = NOW()"
    );
    $stmt->execute([
      ':json' => json_encode($settings, JSON_UNESCAPED_SLASHES),
      ':updated_by' => $settings['meta']['updated_by'],
    ]);
    $result['db_saved'] = true;
  } catch (Throwable $e) {
    error_log('[settings] Unable to save settings to database: ' . $e->getMessage());
  }

  $cachePath = sensor_settings_cache_path();
  try {
    file_put_contents($cachePath, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    $result['cache_saved'] = true;
  } catch (Throwable $e) {
    error_log('[settings] Unable to write cache: ' . $e->getMessage());
  }

  if (firebase_is_ready()) {
    try {
      firebase_db_put('settings/sensors', $settings);
      $result['firebase_saved'] = true;
    } catch (Throwable $e) {
      error_log('[firebase] Unable to persist sensor settings: ' . $e->getMessage());
    }
  }

  return $result;
}
