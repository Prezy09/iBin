<?php
// app/Views/settings/index.php
function settings_h($value): string {
  return htmlspecialchars((string) $value, ENT_QUOTES);
}
?>
<div class="dashboard-hero card p-4 mb-4">
  <div class="row g-4 align-items-center">
    <div class="col-lg-8">
      <div class="d-flex gap-3 align-items-start">
        <div class="hero-icon bubble bg-warning-subtle text-warning">
          <i class="bi bi-sliders"></i>
        </div>
        <div>
          <p class="text-uppercase small text-secondary fw-semibold mb-1">Sensor calibrations</p>
          <h4 class="mb-2">Tune ultrasonic, camera, and GPS profiles.</h4>
          <p class="text-muted mb-0">Adjust thresholds, smoothing, and heartbeat values to keep the iBin hardware stack reliable.</p>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="dashboard-chips d-flex flex-wrap gap-2 justify-content-lg-end">
        <span class="dashboard-chip">
          <i class="bi bi-clock-history"></i>
          Last save: <?= settings_h($lastUpdated); ?>
        </span>
        <span class="dashboard-chip">
          <i class="bi bi-person-circle"></i>
          <?= settings_h($lastUpdatedBy); ?>
        </span>
      </div>
    </div>
  </div>
</div>
<form method="post" class="row g-3">
  <div class="col-12">
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
      <?php
        $dbSaved = is_array($saveStatus) ? !empty($saveStatus['db_saved']) : (bool) $saveStatus;
        $firebaseSaved = is_array($saveStatus) ? !empty($saveStatus['firebase_saved']) : false;
        $cacheSaved = is_array($saveStatus) ? !empty($saveStatus['cache_saved']) : false;
        $overallOk = $dbSaved || $firebaseSaved;
      ?>
      <div class="alert <?= $overallOk ? 'alert-success' : 'alert-warning' ?> d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
          <?php if ($overallOk): ?>
            Sensor calibrations updated.
          <?php else: ?>
            Settings could not be stored in the database or Firebase. Please check connectivity.
          <?php endif; ?>
          <span class="badge bg-<?= $dbSaved ? 'success' : 'secondary' ?> ms-2">DB</span>
          <span class="badge bg-<?= $firebaseSaved ? 'success' : 'secondary' ?> ms-1">Firebase</span>
          <span class="badge bg-<?= $cacheSaved ? 'success' : 'secondary' ?> ms-1">Cache</span>
        </div>
        <span class="small text-secondary">Last saved by <?= settings_h($lastUpdatedBy) ?> at <?= settings_h($lastUpdated) ?></span>
      </div>
    <?php else: ?>
      <div class="alert alert-info d-flex align-items-center justify-content-between">
        <div>Only configured sensors are shown: Ultrasonic (fill level), Camera (YOLOv5 waste detection), and GPS (NEO-6M).</div>
        <span class="small text-secondary">Last saved by <?= settings_h($lastUpdatedBy) ?> at <?= settings_h($lastUpdated) ?></span>
      </div>
    <?php endif; ?>
  </div>

  <div class="col-12 col-xl-8">
    <div class="dashboard-panel p-4 h-100">
      <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
          <h6 class="mb-1">Ultrasonic Fill Level</h6>
          <p class="text-secondary small mb-0">Primary bin sensor (HC-SR04 or equivalent). Calibrate empty/full distances and alert thresholds.</p>
        </div>
        <span class="badge bg-success-subtle text-success-emphasis">Active</span>
      </div>

      <div class="row g-2">
        <div class="col-12 col-md-4">
          <label class="form-label small mb-1">Collection threshold (%)</label>
          <input type="number" name="ultrasonic_collection_threshold" class="form-control form-control-sm" min="0" max="100" value="<?= settings_h($settings['ultrasonic']['collection_threshold_pct']) ?>">
          <div class="form-text">Dispatch crews once average fill exceeds this percentage.</div>
        </div>
        <div class="col-6 col-md-4">
          <label class="form-label small mb-1">Alert (%)</label>
          <input type="number" name="ultrasonic_alert_pct" class="form-control form-control-sm" min="0" max="100" value="<?= settings_h($settings['ultrasonic']['alert_pct']) ?>">
        </div>
        <div class="col-6 col-md-4">
          <label class="form-label small mb-1">Critical (%)</label>
          <input type="number" name="ultrasonic_critical_pct" class="form-control form-control-sm text-danger" min="0" max="100" value="<?= settings_h($settings['ultrasonic']['critical_pct']) ?>">
        </div>
      </div>

      <hr class="my-3">

      <div class="row g-2">
        <div class="col-12 col-sm-6">
          <label class="form-label small mb-1">Empty bin distance (cm)</label>
          <input type="number" step="0.1" name="ultrasonic_empty_cm" class="form-control form-control-sm" min="0" value="<?= settings_h($settings['ultrasonic']['empty_distance_cm']) ?>">
          <div class="form-text">Measured from sensor to base with bin empty.</div>
        </div>
        <div class="col-12 col-sm-6">
          <label class="form-label small mb-1">Full bin distance (cm)</label>
          <input type="number" step="0.1" name="ultrasonic_full_cm" class="form-control form-control-sm" min="0" value="<?= settings_h($settings['ultrasonic']['full_distance_cm']) ?>">
          <div class="form-text">Distance when waste touches the sensing plane.</div>
        </div>
      </div>

      <div class="row g-2 mt-1">
        <div class="col-12 col-md-4">
          <label class="form-label small mb-1">Poll interval (sec)</label>
          <input type="number" name="ultrasonic_poll_interval" class="form-control form-control-sm" min="1" value="<?= settings_h($settings['ultrasonic']['poll_interval_sec']) ?>">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label small mb-1">Smoothing window (samples)</label>
          <input type="number" name="ultrasonic_smoothing" class="form-control form-control-sm" min="1" value="<?= settings_h($settings['ultrasonic']['smoothing_samples']) ?>">
          <div class="form-text">Rolling average to tame sensor noise.</div>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label small mb-1">Offline timeout (sec)</label>
          <input type="number" name="ultrasonic_offline_timeout" class="form-control form-control-sm" min="5" value="<?= settings_h($settings['ultrasonic']['offline_timeout_sec']) ?>">
          <div class="form-text">Mark sensor offline if no replies within this window.</div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-xl-4">
    <div class="dashboard-panel p-4 h-100">
      <h6 class="mb-1">Waste Detection Camera</h6>
      <p class="small text-secondary mb-2">Model: YOLOv5 (trained with OpenCV). Tune detection thresholds to match your environment.</p>
      <div class="row g-2">
        <div class="col-12 col-md-6">
          <label class="form-label small mb-1">Min confidence</label>
          <div class="input-group input-group-sm">
            <input type="number" name="camera_min_confidence" class="form-control" step="0.01" min="0.1" max="1" value="<?= settings_h($settings['camera']['min_confidence']) ?>">
            <span class="input-group-text">score</span>
          </div>
          <div class="form-text">Lower to capture more detections; raise to reduce false positives.</div>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label small mb-1">NMS IoU</label>
          <div class="input-group input-group-sm">
            <input type="number" name="camera_nms_iou" class="form-control" step="0.01" min="0.1" max="1" value="<?= settings_h($settings['camera']['nms_iou']) ?>">
            <span class="input-group-text">overlap</span>
          </div>
          <div class="form-text">Controls suppression of overlapping detections.</div>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label small mb-1">Frame interval (sec)</label>
          <input type="number" name="camera_frame_interval" class="form-control form-control-sm" step="0.1" min="0.1" value="<?= settings_h($settings['camera']['frame_interval_sec']) ?>">
          <div class="form-text">How often frames are sampled for inference.</div>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label small mb-1">Consecutive hits</label>
          <input type="number" name="camera_min_hits" class="form-control form-control-sm" min="1" max="10" value="<?= settings_h($settings['camera']['min_consecutive_hits']) ?>">
          <div class="form-text">Require this many back-to-back detections before flagging waste.</div>
        </div>
        <div class="col-12">
          <label class="form-label small mb-1">Exposure compensation (EV)</label>
          <input type="number" name="camera_exposure" class="form-control form-control-sm" step="0.1" min="-2" max="2" value="<?= settings_h($settings['camera']['exposure_compensation']) ?>">
          <div class="form-text">Adjust for low-light bins; negative for bright scenes.</div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="dashboard-panel p-4 h-100">
      <h6 class="mb-1">GPS (NEO-6M)</h6>
      <p class="small text-secondary mb-2">Keep coordinates stable and accurate for bin routing. Apply offsets after ground-truth measurements.</p>
      <div class="row g-2">
        <div class="col-12 col-md-4">
          <label class="form-label small mb-1">Update rate (Hz)</label>
          <input type="number" name="gps_update_rate" class="form-control form-control-sm" min="1" max="10" value="<?= settings_h($settings['gps']['update_rate_hz']) ?>">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label small mb-1">Min satellites</label>
          <input type="number" name="gps_min_sat" class="form-control form-control-sm" min="3" max="12" value="<?= settings_h($settings['gps']['min_satellites']) ?>">
          <div class="form-text">Ignore fixes with fewer satellites.</div>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label small mb-1">Max HDOP</label>
          <input type="number" name="gps_hdop_max" class="form-control form-control-sm" min="0.5" step="0.1" value="<?= settings_h($settings['gps']['hdop_max']) ?>">
          <div class="form-text">Drop fixes with poor precision.</div>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label small mb-1">Drift correction (m)</label>
          <input type="number" name="gps_drift_correction" class="form-control form-control-sm" min="0" max="50" value="<?= settings_h($settings['gps']['drift_correction_m']) ?>">
          <div class="form-text">Ignore jumps smaller than this distance.</div>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label small mb-1">Latitude offset</label>
          <input type="number" name="gps_offset_lat" class="form-control form-control-sm" step="0.000001" min="-1" max="1" value="<?= settings_h($settings['gps']['offset_lat']) ?>">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label small mb-1">Longitude offset</label>
          <input type="number" name="gps_offset_lng" class="form-control form-control-sm" step="0.000001" min="-1" max="1" value="<?= settings_h($settings['gps']['offset_lng']) ?>">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label small mb-1">Heartbeat (sec)</label>
          <input type="number" name="gps_heartbeat" class="form-control form-control-sm" min="5" max="300" value="<?= settings_h($settings['gps']['heartbeat_seconds']) ?>">
          <div class="form-text">Expected beacon interval from the module.</div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="dashboard-panel p-4 h-100">
      <h6 class="mb-1">Sync & Apply</h6>
      <p class="small text-secondary mb-2">Saving updates the MySQL sensor_settings table, Firebase (if configured), and the local cache for offline fallback.</p>
      <ul class="small text-secondary mb-3">
        <li>Ultrasonic calibration is used to compute fill percentage from HC-SR04 readings.</li>
        <li>Camera thresholds align with the OpenCV + YOLOv5 waste model now deployed.</li>
        <li>GPS offsets are applied to every NEO-6M fix before routing/visualization.</li>
      </ul>
      <div class="d-flex justify-content-between align-items-center">
        <div class="text-secondary small">
          <div>Last saved by <strong><?= settings_h($lastUpdatedBy) ?></strong></div>
          <div><?= settings_h($lastUpdated) ?></div>
        </div>
        <div class="text-end">
          <button type="submit" class="btn btn-success btn-sm px-4">Save Sensor Settings</button>
        </div>
      </div>
    </div>
  </div>
</form>
