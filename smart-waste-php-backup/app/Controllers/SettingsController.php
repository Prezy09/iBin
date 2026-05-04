<?php
namespace App\Controllers;

use Core\Controller;

class SettingsController extends Controller {

    public function index() {
        require_once __DIR__ . '/../../includes/auth.php';
        auth_require_role(['Admin']);
        
        require_once __DIR__ . '/../../includes/sensor_settings.php';
        
        $settings = sensor_settings_load();
        $saveStatus = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $payload = $settings;

            $payload['ultrasonic']['collection_threshold_pct'] = $this->settings_clamp_int($_POST['ultrasonic_collection_threshold'] ?? $payload['ultrasonic']['collection_threshold_pct'], 0, 100);
            $payload['ultrasonic']['alert_pct'] = $this->settings_clamp_int($_POST['ultrasonic_alert_pct'] ?? $payload['ultrasonic']['alert_pct'], 0, 100);
            $payload['ultrasonic']['critical_pct'] = $this->settings_clamp_int($_POST['ultrasonic_critical_pct'] ?? $payload['ultrasonic']['critical_pct'], 0, 100);
            $payload['ultrasonic']['empty_distance_cm'] = $this->settings_clamp_float($_POST['ultrasonic_empty_cm'] ?? $payload['ultrasonic']['empty_distance_cm'], 0, 400, 1);
            $payload['ultrasonic']['full_distance_cm'] = $this->settings_clamp_float($_POST['ultrasonic_full_cm'] ?? $payload['ultrasonic']['full_distance_cm'], 0, 400, 1);
            $payload['ultrasonic']['poll_interval_sec'] = $this->settings_clamp_int($_POST['ultrasonic_poll_interval'] ?? $payload['ultrasonic']['poll_interval_sec'], 1, 300);
            $payload['ultrasonic']['smoothing_samples'] = $this->settings_clamp_int($_POST['ultrasonic_smoothing'] ?? $payload['ultrasonic']['smoothing_samples'], 1, 25);
            $payload['ultrasonic']['offline_timeout_sec'] = $this->settings_clamp_int($_POST['ultrasonic_offline_timeout'] ?? $payload['ultrasonic']['offline_timeout_sec'], 5, 900);

            $payload['camera']['model'] = 'YOLOv5';
            $payload['camera']['min_confidence'] = $this->settings_clamp_float($_POST['camera_min_confidence'] ?? $payload['camera']['min_confidence'], 0.1, 1.0, 2);
            $payload['camera']['nms_iou'] = $this->settings_clamp_float($_POST['camera_nms_iou'] ?? $payload['camera']['nms_iou'], 0.1, 1.0, 2);
            $payload['camera']['frame_interval_sec'] = $this->settings_clamp_float($_POST['camera_frame_interval'] ?? $payload['camera']['frame_interval_sec'], 0.1, 10.0, 2);
            $payload['camera']['min_consecutive_hits'] = $this->settings_clamp_int($_POST['camera_min_hits'] ?? $payload['camera']['min_consecutive_hits'], 1, 10);
            $payload['camera']['exposure_compensation'] = $this->settings_clamp_float($_POST['camera_exposure'] ?? $payload['camera']['exposure_compensation'], -2.0, 2.0, 1);

            $payload['gps']['module'] = 'NEO-6M';
            $payload['gps']['update_rate_hz'] = $this->settings_clamp_int($_POST['gps_update_rate'] ?? $payload['gps']['update_rate_hz'], 1, 10);
            $payload['gps']['min_satellites'] = $this->settings_clamp_int($_POST['gps_min_sat'] ?? $payload['gps']['min_satellites'], 3, 12);
            $payload['gps']['hdop_max'] = $this->settings_clamp_float($_POST['gps_hdop_max'] ?? $payload['gps']['hdop_max'], 0.5, 10.0, 2);
            $payload['gps']['drift_correction_m'] = $this->settings_clamp_int($_POST['gps_drift_correction'] ?? $payload['gps']['drift_correction_m'], 0, 50);
            $payload['gps']['offset_lat'] = $this->settings_clamp_float($_POST['gps_offset_lat'] ?? $payload['gps']['offset_lat'], -1.0, 1.0, 6);
            $payload['gps']['offset_lng'] = $this->settings_clamp_float($_POST['gps_offset_lng'] ?? $payload['gps']['offset_lng'], -1.0, 1.0, 6);
            $payload['gps']['heartbeat_seconds'] = $this->settings_clamp_int($_POST['gps_heartbeat'] ?? $payload['gps']['heartbeat_seconds'], 5, 300);

            $payload['meta']['updated_by'] = $_SESSION['user_name'] ?? 'Admin';
            $payload['meta']['updated_at'] = gmdate('c');

            $saveStatus = sensor_settings_save($payload, $payload['meta']['updated_by']);
            $settings = $payload;
        }

        $lastUpdated = $settings['meta']['updated_at'] ? date('Y-m-d H:i', strtotime($settings['meta']['updated_at'])) . ' UTC' : 'Not saved yet';
        $lastUpdatedBy = $settings['meta']['updated_by'] ?: 'n/a';
        
        $data = [
            'title' => 'Settings',
            'active' => 'settings',
            'brand' => APP_BRAND_FULL,
            'settings' => $settings,
            'saveStatus' => $saveStatus,
            'lastUpdated' => $lastUpdated,
            'lastUpdatedBy' => $lastUpdatedBy,
        ];

        $this->render('settings/index', $data);
    }
    
    private function settings_clamp_int($value, int $min, int $max): int {
        $num = (int) round((float) $value);
        return max($min, min($max, $num));
    }

    private function settings_clamp_float($value, float $min, float $max, int $precision = 2): float {
        $num = round((float) $value, $precision);
        if ($num < $min) {
            return $min;
        }
        if ($num > $max) {
            return $max;
        }
        return $num;
    }
}
