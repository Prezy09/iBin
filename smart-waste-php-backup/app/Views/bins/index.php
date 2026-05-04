<?php
// app/Views/bins/index.php

function bin_percent_avg(array $compartments): int {
  if (!$compartments) {
    return 0;
  }
  $values = array_map('intval', array_values($compartments));
  $count = count($values);
  return $count ? (int) round(array_sum($values) / $count) : 0;
}

function bin_status_chip(int $percent): string {
  if ($percent >= 80) {
    return '<span class="status-chip status-err">High</span>';
  }
  if ($percent >= 60) {
    return '<span class="status-chip status-warn">Medium</span>';
  }
  return '<span class="status-chip status-ok">Low</span>';
}

function bin_format_time(?string $value): string {
  if (!$value) {
    return 'just now';
  }
  $ts = strtotime($value);
  if (!$ts) {
    return 'just now';
  }
  return date('M j, Y g:i A', $ts);
}

function bin_format_ping(?string $value): string {
  if (!$value) {
    return 'No ping yet';
  }
  $ts = strtotime($value);
  if (!$ts) {
    return 'No ping yet';
  }
  return date('M j, Y g:i A', $ts);
}

function bin_progress_value($value): int {
  $int = (int) $value;
  if ($int < 0) {
    return 0;
  }
  if ($int > 100) {
    return 100;
  }
  return $int;
}

function bin_last_ping_ts(array $bin): ?int {
  $value = $bin['last_ping'] ?? $bin['lastPing'] ?? $bin['last_ping_at'] ?? $bin['lastPingAt'] ?? $bin['last_updated'] ?? $bin['lastUpdated'] ?? null;
  if (!$value) {
    return null;
  }
  $ts = strtotime($value);
  return $ts ?: null;
}

function bin_is_online(array $bin, int $windowSeconds = 600): bool {
  $window = max(60, $windowSeconds);
  $lastPing = bin_last_ping_ts($bin);
  if ($lastPing === null) {
    return false;
  }
  return (time() - $lastPing) <= $window;
}

?>
<div class="dashboard-hero card p-4 mb-4">
  <div class="row g-4 align-items-center">
    <div class="col-lg-8">
      <div class="d-flex gap-3 align-items-start">
        <div class="hero-icon bubble bg-success-subtle text-success">
          <i class="bi bi-hdd-network"></i>
        </div>
        <div>
          <p class="text-uppercase small text-secondary fw-semibold mb-1">Smart Bin Fleet</p>
          <h4 class="mb-2">Manage <?php echo $binCount; ?> instrumented bins across Himamaylan.</h4>
          <p class="text-muted mb-0">
            <?php echo $sourceIsLive
              ? 'Streaming live Firebase telemetry for fill, battery, and connectivity.'
              : 'Previewing cached sample data until your sensors sync again.'; ?>
          </p>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="d-flex flex-wrap gap-2 justify-content-lg-end dashboard-chips">
        <span class="dashboard-chip">
          <i class="bi bi-cloud-arrow-down"></i>
          <?php echo $sourceIsLive ? 'Live Firebase' : 'Sample cache'; ?>
        </span>
        <span class="dashboard-chip <?php echo $piConnected ? '' : 'text-danger'; ?>">
          <i class="bi bi-cpu"></i>
          <?php echo $piConnected ? 'Pi heartbeat' : 'Pi offline'; ?>
        </span>
      </div>
      <p class="text-secondary small text-lg-end mt-3 mb-0">Last sync: <?php echo htmlspecialchars($lastPingDisplay, ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
  </div>
</div>

<style>
.bin-card {
    background: #ffffff;
    border: 1px solid #e3eaf5;
    border-radius: 14px;
    box-shadow: 0 10px 28px rgba(16, 38, 74, 0.08);
    transition: transform 0.18s ease, box-shadow 0.18s ease;
    max-width: 460px;
    margin-left: auto;
    margin-right: auto;
  }
  .bin-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 14px 32px rgba(16, 38, 74, 0.12);
  }
  .bin-card .chip {
    border-radius: 999px;
    padding: 6px 10px;
    font-size: 0.82rem;
  }
  .bin-card .status-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border-radius: 999px;
    font-size: 0.82rem;
    border: 1px solid transparent;
  }
  .bin-card .status-pill .status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    background: currentColor;
  }
  .bin-card .status-pill.online {
    color: #0f9d58;
    background: #e8f5ed;
    border-color: #c6e6d2;
  }
  .bin-card .status-pill.offline {
    color: #6c757d;
    background: #f1f3f5;
    border-color: #e0e5ea;
  }
  .bin-card .status-stack {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 6px;
    min-width: 90px;
  }
  .bin-card .progress {
    height: 8px;
    background: #eef2f7;
    border-radius: 12px;
  }
  #binsGrid {
    row-gap: 1rem;
  }
</style>
<div class="row g-3 mb-4">
  <div class="col-12 col-sm-6 col-lg-3">
    <div class="dashboard-stat-card h-100">
      <div class="stat-icon tone-success">
        <i class="bi bi-hdd-stack"></i>
      </div>
      <div>
        <p class="stat-label">Active Bins</p>
        <div class="stat-value"><?php echo $binCount; ?></div>
        <p class="stat-note mb-0">Reporting in this view</p>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-lg-3">
    <div class="dashboard-stat-card h-100">
      <div class="stat-icon tone-warning">
        <i class="bi bi-person-plus"></i>
      </div>
      <div>
        <p class="stat-label">Registerable</p>
        <div class="stat-value"><?php echo $registerableCount; ?></div>
        <p class="stat-note mb-0">Online in the last 10 min</p>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-lg-3">
    <div class="dashboard-stat-card h-100">
      <div class="stat-icon tone-info">
        <i class="bi bi-hourglass-split"></i>
      </div>
      <div>
        <p class="stat-label">Pending Imports</p>
        <div class="stat-value"><?php echo $pendingCount; ?></div>
        <p class="stat-note mb-0">Ready from sample data</p>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-lg-3">
    <div class="dashboard-stat-card h-100">
      <div class="stat-icon tone-primary">
        <i class="bi bi-clock-history"></i>
      </div>
      <div>
        <p class="stat-label">Last Telemetry</p>
        <div class="stat-value" style="font-size:1.2rem;"><?php echo htmlspecialchars($latestPingTs ? date('g:i A', $latestPingTs) : '--', ENT_QUOTES, 'UTF-8'); ?></div>
        <p class="stat-note mb-0"><?php echo htmlspecialchars($latestPingTs ? date('M j, Y', $latestPingTs) : 'No signal yet', ENT_QUOTES, 'UTF-8'); ?></p>
      </div>
    </div>
  </div>
</div>
<div class="dashboard-panel p-4 mb-4">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
    <div>
      <p class="text-uppercase small text-secondary mb-1">Bin inventory</p>
      <h6 class="mb-0">Monitor live bins and their status</h6>
    </div>
    <span class="chart-pill live">
      <i class="bi bi-grid-3x3-gap"></i>
      <?php echo $binCount; ?> bins listed
    </span>
  </div>
  <div class="row g-3" id="binsGrid">
  <?php if ($binCount > 0): ?>
    <?php foreach ($bins as $bin): ?>
      <?php
        $compartments = $bin['compartments'] ?? [];
        $avg = bin_percent_avg($compartments);
        $binIdRaw = $bin['bin_id'] ?? $bin['id'] ?? '';
        $binId = htmlspecialchars($binIdRaw, ENT_QUOTES, 'UTF-8');
        $binName = htmlspecialchars($bin['name'] ?? $binIdRaw ?: 'Bin', ENT_QUOTES, 'UTF-8');
        $binAddress = htmlspecialchars($bin['address'] ?? 'Himamaylan City', ENT_QUOTES, 'UTF-8');
        $chipLabel = htmlspecialchars(strtoupper(substr($binIdRaw, 0, 6)), ENT_QUOTES, 'UTF-8');
        $bio = bin_progress_value($compartments['biodegradable'] ?? 0);
        $rec = bin_progress_value($compartments['recyclable'] ?? 0);
        $res = bin_progress_value($compartments['residual'] ?? 0);
        $battery = $bin['battery'] ?? null;
        $updated = htmlspecialchars(bin_format_time($bin['updated_at'] ?? $bin['updatedAt'] ?? $bin['last_updated'] ?? ''), ENT_QUOTES, 'UTF-8');
        $lastPing = htmlspecialchars(bin_format_ping($bin['last_ping'] ?? $bin['lastPing'] ?? $bin['last_ping_at'] ?? null), ENT_QUOTES, 'UTF-8');
        $isOnline = bin_is_online($bin, $offlineWindowSeconds);
      ?>
      <div class="col-12 col-md-6 col-xl-4">
        <button type="button" class="bin-card card p-3 h-100 w-100" data-bin-id="<?php echo $binId; ?>">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center gap-2">
              <span class="chip bg-success-subtle text-success-emphasis fw-semibold"><?php echo $chipLabel ?: 'BIN'; ?></span>
              <div>
                <strong class="d-block"><?php echo $binName; ?></strong>
                <span class="text-secondary small"><?php echo $binAddress; ?></span>
              </div>
            </div>
            <div class="status-stack">
              <div><?php echo bin_status_chip($avg); ?></div>
              <span class="status-pill <?php echo $isOnline ? 'online' : 'offline'; ?>">
                <span class="status-dot"></span><?php echo $isOnline ? 'Online' : 'Offline'; ?>
              </span>
            </div>
          </div>
          <div class="row g-2">
            <div class="col-6">
              <div class="text-secondary small">Biodegradable</div>
              <div class="progress" role="progressbar" aria-label="Biodegradable">
                <div class="progress-bar bg-success" style="width: <?php echo $bio; ?>%"></div>
              </div>
            </div>
            <div class="col-6">
              <div class="text-secondary small">Recyclable</div>
              <div class="progress" role="progressbar" aria-label="Recyclable">
                <div class="progress-bar bg-info" style="width: <?php echo $rec; ?>%"></div>
              </div>
            </div>
            <div class="col-12">
              <div class="text-secondary small">Residual</div>
              <div class="progress" role="progressbar" aria-label="Residual">
                <div class="progress-bar bg-warning" style="width: <?php echo $res; ?>%"></div>
              </div>
            </div>
          </div>
          <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
              <div class="small text-secondary">Battery</div>
              <div class="fw-semibold"><?php echo $battery === null ? '--' : intval($battery); ?>%</div>
            </div>
            <div class="text-end">
              <div class="small text-secondary">Updated</div>
              <div class="small"><?php echo $updated; ?></div>
              <div class="small text-muted">Last ping: <?php echo $lastPing; ?></div>
            </div>
          </div>
        </button>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="col-12">
      <div class="card p-4 text-center text-secondary">No bins detected yet.</div>
    </div>
  <?php endif; ?>
</div>
</div>

<script>
  window.IBIN_SAMPLE_BINS = <?php echo json_encode(
    $clientState['fallback'],
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES
  ); ?>;
</script>
<div class="modal fade" id="binModal" tabindex="-1" aria-labelledby="binModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content rounded-4">
      <div class="modal-header border-0">
        <div>
          <h5 class="modal-title" id="binModalLabel">Bin Details</h5>
          <p class="text-secondary small mb-0" id="binModalMeta"></p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-0" id="binModalBody">
      </div>
      <div class="modal-footer border-0">
        <a class="btn btn-outline-success" id="binModalLink" href="#" target="_self">Open Full View</a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="addBinModal" tabindex="-1" aria-labelledby="addBinModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content rounded-4" id="addBinForm">
      <div class="modal-header border-0">
        <div>
          <h5 class="modal-title" id="addBinModalLabel">Register a Bin</h5>
          <p class="text-secondary small mb-0">Publishes an empty bin record to Firebase so your Raspberry Pi can sync.</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-0">
        <div id="addBinAlert" class="alert alert-danger d-none" role="alert"></div>
        <div class="mb-3">
          <label class="form-label d-flex justify-content-between align-items-center" for="binIdInput">
            <span>Bin ID</span>
            <span class="badge bg-success-subtle text-success-emphasis d-none" id="binLookupStatus">Prefilled</span>
          </label>
          <div class="input-group">
            <input type="text" class="form-control" id="binIdInput" name="bin_id" placeholder="Scan or select a bin ID" list="binIdOptions" autocomplete="off" required>
            <button class="btn btn-outline-primary" type="button" id="binLookupBtn">
              <i class="bi bi-search"></i>
              <span class="d-none d-sm-inline">Scan</span>
            </button>
          </div>
          <datalist id="binIdOptions">
            <?php foreach ($registerableBins as $bin): ?>
              <option value="<?php echo htmlspecialchars($bin['bin_id'], ENT_QUOTES); ?>" label="<?php echo htmlspecialchars($bin['name'] !== $bin['bin_id'] ? "{$bin['name']} ({$bin['bin_id']})" : $bin['bin_id']); ?>"></option>
            <?php endforeach; ?>
          </datalist>
          <div class="form-text">Select an online bin or enter a new ID and press Scan to load database details.</div>
          <?php if (empty($registerableBins)): ?>
            <div class="alert alert-warning mt-2 mb-0 small" role="alert">
              No online bins were detected from Firebase. Ensure your Raspberry Pi devices are connected.
            </div>
          <?php endif; ?>
          <?php if (!empty($pendingBins)): ?>
            <div class="small text-success mt-2">Detected <?php echo count($pendingBins); ?> new <?php echo count($pendingBins) === 1 ? 'bin' : 'bins'; ?> awaiting import.</div>
          <?php endif; ?>
          <div class="small mt-1 text-secondary" id="binLookupFeedback"></div>
        </div>
        <div class="mb-3">
          <label for="binNameInput" class="form-label">Display name</label>
          <input type="text" class="form-control" id="binNameInput" name="name" placeholder="City Hall Compound">
        </div>
        <div class="mb-3">
          <label for="binAddressInput" class="form-label">Address / Location label</label>
          <input type="text" class="form-control" id="binAddressInput" name="address" placeholder="Barangay Talaban, Himamaylan City">
        </div>
        <div class="alert alert-info d-none small" id="binPrefillPreview">
          <div class="d-flex justify-content-between align-items-start gap-3">
            <div>
              <div class="fw-semibold">Database details loaded</div>
              <div>Coordinates: <span data-prefill-field="coords">--</span></div>
              <div>Battery: <span data-prefill-field="battery">--</span></div>
              <div>Fill Levels (Bio / Recycle / Residual):
                <span data-prefill-field="fillBio">--</span> /
                <span data-prefill-field="fillRec">--</span> /
                <span data-prefill-field="fillRes">--</span>
              </div>
            </div>
            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0" id="binPrefillClearBtn">Clear</button>
          </div>
        </div>
        <div class="form-text mt-2">Bins start Offline with 0% fill levels. The Raspberry Pi will update these fields once it comes online.</div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-2" id="addBinSubmit">
          <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="addBinSpinner"></span>
          <span>Save Bin</span>
        </button>
      </div>
    </form>
  </div>
</div>
<script type="module">
  import { applySampleResets } from './assets/js/app.js';
  if (Array.isArray(window.IBIN_SAMPLE_BINS)) {
    window.IBIN_SAMPLE_BINS = applySampleResets(window.IBIN_SAMPLE_BINS, { cloneAll: true });
  }
  const SERVER_STATE = <?php echo json_encode(
    $clientState,
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES
  ); ?>;
  const CONNECTION_STATE = SERVER_STATE?.connection || {};
  const BIN_TEMPLATE = SERVER_STATE?.bin_template || {};
  const REGISTERABLE_BINS = Array.isArray(SERVER_STATE?.registerable_bins)
    ? SERVER_STATE.registerable_bins
    : [];
  const firebaseReady = Boolean(CONNECTION_STATE.firebase_ready);
  const PI_WINDOW_MS = 5 * 60 * 1000;
  const STALE_MS = <?php echo (int) ($offlineWindowSeconds * 1000); ?>;
  const sourceLabel = String(SERVER_STATE?.source || '').toLowerCase();
  const usingSampleSource = sourceLabel !== 'firebase';
  let SAMPLE_BINS = (SERVER_STATE && Array.isArray(SERVER_STATE.fallback)) ? SERVER_STATE.fallback : [];
  if (usingSampleSource) {
    SAMPLE_BINS = applySampleResets(SAMPLE_BINS, { cloneAll: true });
  }
  const grid = document.getElementById('binsGrid');
  const modalEl = document.getElementById('binModal');
  const modalBody = document.getElementById('binModalBody');
  const modalTitle = document.getElementById('binModalLabel');
  const modalMeta = document.getElementById('binModalMeta');
  const modalLink = document.getElementById('binModalLink');
  const addBinModalEl = document.getElementById('addBinModal');
  const addBinForm = document.getElementById('addBinForm');
  const addBinAlert = document.getElementById('addBinAlert');
  const addBinSubmit = document.getElementById('addBinSubmit');
  const addBinSpinner = document.getElementById('addBinSpinner');
  const binIdInput = document.getElementById('binIdInput');
  const binIdOptions = document.getElementById('binIdOptions');
  const binLookupBtn = document.getElementById('binLookupBtn');
  const binLookupFeedback = document.getElementById('binLookupFeedback');
  const binLookupStatus = document.getElementById('binLookupStatus');
  const binPrefillPreview = document.getElementById('binPrefillPreview');
  const binPrefillClearBtn = document.getElementById('binPrefillClearBtn');
  const binNameInput = document.getElementById('binNameInput');
  const binAddressInput = document.getElementById('binAddressInput');
  const prefillFields = {
    coords: document.querySelector('[data-prefill-field="coords"]'),
    battery: document.querySelector('[data-prefill-field="battery"]'),
    fillBio: document.querySelector('[data-prefill-field="fillBio"]'),
    fillRec: document.querySelector('[data-prefill-field="fillRec"]'),
    fillRes: document.querySelector('[data-prefill-field="fillRes"]')
  };
  const pendingBins = Array.isArray(SERVER_STATE?.pending_bins) ? SERVER_STATE.pending_bins : [];
  const pendingBinMap = new Map();
  pendingBins.forEach(bin => {
    const key = String(bin?.bin_id || bin?.id || '').trim().toLowerCase();
    if (key) {
      pendingBinMap.set(key, bin);
    }
  });
  let modalInstance = null;
  let addBinModal = null;
  let lastRenderSignature = '';
  const initialBins = (SERVER_STATE && Array.isArray(SERVER_STATE.bins) && SERVER_STATE.bins.length)
    ? (usingSampleSource ? applySampleResets(SERVER_STATE.bins, { cloneAll: true }) : SERVER_STATE.bins)
    : SAMPLE_BINS.slice();
  const state = {
    bins: initialBins
  };
  let registerableOptions = REGISTERABLE_BINS.slice();
  let registerableSignature = '';
  let binPrefillState = null;

  [modalEl, addBinModalEl].forEach(modal => {
    if (modal && modal.parentElement !== document.body) {
      document.body.appendChild(modal);
    }
  });

  const ESCAPE_LOOKUP = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };

  function escapeHtml(value){
    return String(value ?? '').replace(/[&<>"']/g, (char) => ESCAPE_LOOKUP[char] || char);
  }

  function clampPercent(value){
    const num = Math.round(Number(value) || 0);
    if (num < 0) return 0;
    if (num > 100) return 100;
    return num;
  }

  function formatTime(value){
    if (!value) return 'just now';
    const d = new Date(value);
    return Number.isNaN(d.getTime()) ? 'just now' : d.toLocaleString();
  }

  function formatPingTime(value){
    if (!value) return 'No ping yet';
    const d = new Date(value);
    return Number.isNaN(d.getTime()) ? 'No ping yet' : d.toLocaleString();
  }

  function percentAvg(compartments){
    const values = Object.values(compartments || {}).map(value => Number(value) || 0);
    if (!values.length) return 0;
    return Math.round(values.reduce((sum, value) => sum + value, 0) / values.length);
  }

  function statusChip(percent){
    if (percent >= 80) return '<span class="status-chip status-err">High</span>';
    if (percent >= 60) return '<span class="status-chip status-warn">Medium</span>';
    return '<span class="status-chip status-ok">Low</span>';
  }

  function binsSignature(bins, nowMs = Date.now()){
    try {
      const timeBucket = Math.floor(nowMs / STALE_MS);
      return JSON.stringify((bins || []).map(bin => {
        const ping = parseTimestamp(bin?.last_ping || bin?.lastPing || bin?.last_ping_at || bin?.lastPingAt || bin?.updated_at || bin?.last_updated);
        const online = isBinActive(bin, nowMs);
        const staleBucket = ping ? Math.floor(Math.max(0, nowMs - ping) / STALE_MS) : 'no-ping';
        return {
          id: bin.bin_id || bin.id || '',
          updated: bin.updated_at || bin.updatedAt || bin.last_updated || '',
          battery: bin.battery,
          compartments: bin.compartments || {},
          online,
          staleBucket,
          timeBucket
        };
      }));
    } catch (err) {
      return String(Date.now());
    }
  }

  function buildAddCard(firebaseOnline, piOnline){
    const firebaseClass = firebaseOnline ? 'online' : 'offline';
    const piClass = piOnline ? 'online' : 'offline';
    const note = !firebaseOnline
      ? 'Connect Firebase before registering bins.'
      : (piOnline ? 'Firebase link is healthy.' : 'Waiting for Raspberry Pi heartbeat.');
    const firebaseState = firebaseOnline ? 'Online' : 'Offline';
    const piState = piOnline ? 'Online' : 'Offline';
    return `
      <div class="col-12 col-md-6 col-xl-4">
        <button type="button" class="bin-card add-bin-card card p-3 h-100 w-100" id="addBinCardClient">
          <div class="d-flex flex-column align-items-center justify-content-center text-center gap-2 h-100">
            <div class="add-icon d-inline-flex align-items-center justify-content-center rounded-circle">
              <i class="bi bi-plus-lg"></i>
            </div>
            <div>
              <div class="fw-semibold">Add a Bin</div>
              <div class="text-secondary small">Register hardware or connect an active unit.</div>
            </div>
            <div class="connection-block mt-3" data-connection-card>
              <div class="connection-status">
                <span class="connection-chip ${firebaseClass}" data-role="firebase">
                  <span class="status-dot"></span>
                  <span class="connection-label">Firebase</span>
                  <span class="connection-state">${firebaseState}</span>
                </span>
                <span class="connection-chip ${piClass}" data-role="pi">
                  <span class="status-dot"></span>
                  <span class="connection-label">Raspberry Pi</span>
                  <span class="connection-state">${piState}</span>
                </span>
              </div>
              <small class="connection-note text-secondary" data-connection-note>${escapeHtml(note)}</small>
            </div>
          </div>
        </button>
      </div>`;
  }

  function card(bin, nowMs = Date.now()){
    const avg = percentAvg(bin.compartments);
    const rawId = (bin.bin_id || bin.id || '').trim();
    const shortId = rawId.slice(0, 6).toUpperCase() || 'BIN';
    const safeId = escapeHtml(rawId);
    const safeName = escapeHtml(bin.name || rawId || 'Bin');
    const safeAddress = escapeHtml(bin.address || 'Himamaylan City');
    const bio = clampPercent(bin.compartments?.biodegradable);
    const rec = clampPercent(bin.compartments?.recyclable);
    const res = clampPercent(bin.compartments?.residual);
    const battery = Number.isFinite(Number(bin.battery)) ? `${Math.round(Number(bin.battery))}%` : '--';
    const updated = escapeHtml(formatTime(bin.updated_at || bin.updatedAt || bin.last_updated));
    const lastPing = escapeHtml(formatPingTime(bin.last_ping || bin.lastPing || bin.last_ping_at || bin.lastPingAt || bin.updated_at || bin.last_updated));
    const isOnline = isBinActive(bin, nowMs);
    return `
      <div class="col-12 col-md-6 col-xl-4">
        <button type="button" class="bin-card card p-3 h-100 w-100" data-bin-id="${safeId}">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center gap-2">
              <span class="chip bg-success-subtle text-success-emphasis fw-semibold">${escapeHtml(shortId)}</span>
              <div>
                <strong class="d-block">${safeName}</strong>
                <span class="text-secondary small">${safeAddress}</span>
              </div>
            </div>
            <div class="text-end">
              <div>${statusChip(avg)}</div>
              <span class="status-pill ${isOnline ? 'online' : 'offline'}"><span class="status-dot"></span>${isOnline ? 'Online' : 'Offline'}</span>
            </div>
          </div>
          <div class="row g-2">
            <div class="col-6">
              <div class="text-secondary small">Biodegradable</div>
              <div class="progress" role="progressbar" aria-label="Biodegradable">
                <div class="progress-bar bg-success" style="width:${bio}%"></div>
              </div>
            </div>
            <div class="col-6">
              <div class="text-secondary small">Recyclable</div>
              <div class="progress" role="progressbar" aria-label="Recyclable">
                <div class="progress-bar bg-info" style="width:${rec}%"></div>
              </div>
            </div>
            <div class="col-12">
              <div class="text-secondary small">Residual</div>
              <div class="progress" role="progressbar" aria-label="Residual">
                <div class="progress-bar bg-warning" style="width:${res}%"></div>
              </div>
            </div>
          </div>
          <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
              <div class="small text-secondary">Battery</div>
              <div class="fw-semibold">${escapeHtml(battery)}</div>
            </div>
            <div class="text-end">
              <div class="small text-secondary">Updated</div>
              <div class="small">${updated}</div>
              <div class="small text-muted">Last ping: ${lastPing}</div>
            </div>
          </div>
        </button>
      </div>`;
  }

  function parseTimestamp(value){
    if (!value) return null;
    const ts = Date.parse(value);
    return Number.isNaN(ts) ? null : ts;
  }

  function computePiConnection(bins){
    let latest = null;
    (bins || []).forEach(bin => {
      const ts = parseTimestamp(bin?.last_ping || bin?.lastPing || bin?.last_ping_at || bin?.lastPingAt || bin?.updated_at || bin?.last_updated);
      if (ts && (!latest || ts > latest)) {
        latest = ts;
      }
    });
    if (!latest && CONNECTION_STATE.pi_last_ping_at) {
      const fallback = parseTimestamp(CONNECTION_STATE.pi_last_ping_at);
      if (fallback) {
        latest = fallback;
      }
    }
    if (!latest) {
      return { connected: false, lastPing: null };
    }
    return {
      connected: (Date.now() - latest) < PI_WINDOW_MS,
      lastPing: new Date(latest)
    };
  }

  function describeRelativeTime(date){
    if (!(date instanceof Date) || Number.isNaN(date.getTime())) {
      return 'No heartbeat detected';
    }
    const diff = Date.now() - date.getTime();
    if (diff < 60000) return 'Just now';
    if (diff < 3600000) return `${Math.max(1, Math.round(diff / 60000))}m ago`;
    return date.toLocaleString();
  }

  function setChipState(el, isOnline){
    if (!el) return;
    el.classList.toggle('online', isOnline);
    el.classList.toggle('offline', !isOnline);
    const stateLabel = el.querySelector('.connection-state');
    if (stateLabel) {
      stateLabel.textContent = isOnline ? 'Online' : 'Offline';
    }
  }

  function updateConnectionBadges(bins){
    const piState = computePiConnection(bins);
    document.querySelectorAll('.connection-chip[data-role="firebase"]').forEach(el => setChipState(el, firebaseReady));
    document.querySelectorAll('.connection-chip[data-role="pi"]').forEach(el => setChipState(el, piState.connected));
    const note = firebaseReady
      ? (piState.connected ? `Last ping ${describeRelativeTime(piState.lastPing)}` : 'Awaiting Raspberry Pi heartbeat')
      : 'Connect Firebase before registering bins.';
    document.querySelectorAll('[data-connection-note]').forEach(el => {
      el.textContent = note;
    });
  }

  function deriveRegisterableOptions(bins){
    const now = Date.now();
    return (Array.isArray(bins) ? bins : [])
      .map(bin => {
        const binId = bin?.bin_id || bin?.id || '';
        if (!binId) return null;
        if (!isBinActive(bin, now)) return null;
        return {
          bin_id: binId,
          name: bin?.name || binId,
          address: bin?.address || 'Himamaylan City'
        };
      })
      .filter(Boolean);
  }

  function isBinActive(bin, nowMs){
    const pingDate = parseTimestamp(bin?.last_ping || bin?.lastPing || bin?.last_ping_at || bin?.lastPingAt || bin?.updated_at || bin?.last_updated);
    if (!pingDate) return false;
    return (nowMs - pingDate) <= STALE_MS;
  }

  function updateBinOptions(options){
    if (!binIdOptions) return;
    if (Array.isArray(options)) {
      registerableOptions = options.slice();
    }
    const signature = JSON.stringify((registerableOptions || []).map(opt => opt.bin_id));
    if (registerableSignature === signature) {
      return;
    }
    registerableSignature = signature;
    binIdOptions.innerHTML = '';
    registerableOptions.forEach(opt => {
      const option = document.createElement('option');
      option.value = opt.bin_id;
      option.label = opt.name && opt.name !== opt.bin_id ? `${opt.name} (${opt.bin_id})` : opt.bin_id;
      binIdOptions.appendChild(option);
    });
  }

  updateBinOptions(registerableOptions);
  binLookupBtn?.addEventListener('click', (event) => {
    event.preventDefault();
    lookupBinInDatabase(binIdInput?.value || '');
  });
  binPrefillClearBtn?.addEventListener('click', (event) => {
    event.preventDefault();
    clearBinPrefill();
    setLookupFeedback('');
  });
  binIdInput?.addEventListener('input', () => {
    const current = String(binIdInput.value || '').trim().toLowerCase();
    if (binPrefillState && current !== binPrefillState.id) {
      clearBinPrefill();
    }
    if (!current) {
      setLookupFeedback('');
    }
  });

  function ensureAddBinModal(){
    if (addBinModal) {
      return addBinModal;
    }
    if (!addBinModalEl || !(window.bootstrap && typeof window.bootstrap.Modal === 'function')) {
      return null;
    }
    addBinModal = new window.bootstrap.Modal(addBinModalEl);
    return addBinModal;
  }

  function openAddBinModal(){
    const modal = ensureAddBinModal();
    if (modal) {
      modal.show();
    }
  }

  function resetAddBinForm(){
    if (!addBinForm) return;
    addBinForm.reset();
    clearAddBinError();
    clearBinPrefill();
    setLookupFeedback('');
  }

  function clearAddBinError(){
    if (!addBinAlert) return;
    addBinAlert.classList.add('d-none');
    addBinAlert.textContent = '';
  }

  function showAddBinError(message){
    if (!addBinAlert) {
      alert(message);
      return;
    }
    addBinAlert.textContent = message;
    addBinAlert.classList.remove('d-none');
  }

  function setLookupFeedback(message = '', variant = 'secondary'){
    if (!binLookupFeedback) return;
    binLookupFeedback.textContent = message || '';
    binLookupFeedback.className = `small mt-1 text-${variant}`;
  }

  function clearBinPrefill(){
    binPrefillState = null;
    if (binLookupStatus) {
      binLookupStatus.classList.add('d-none');
    }
    if (binPrefillPreview) {
      binPrefillPreview.classList.add('d-none');
    }
    Object.values(prefillFields).forEach(field => {
      if (field) {
        field.textContent = '--';
      }
    });
  }

  function updatePrefillPreview(data){
    if (!binPrefillPreview || !data) {
      return;
    }
    const formatCoord = (value) => (Number.isFinite(value) ? value.toFixed(4) : '--');
    if (prefillFields.coords) {
      prefillFields.coords.textContent = `${formatCoord(data.locationLat)}, ${formatCoord(data.locationLng)}`;
    }
    if (prefillFields.battery) {
      prefillFields.battery.textContent = `${data.battery ?? 0}%`;
    }
    if (prefillFields.fillBio) {
      prefillFields.fillBio.textContent = `${data.fillBio ?? 0}%`;
    }
    if (prefillFields.fillRec) {
      prefillFields.fillRec.textContent = `${data.fillRec ?? 0}%`;
    }
    if (prefillFields.fillRes) {
      prefillFields.fillRes.textContent = `${data.fillRes ?? 0}%`;
    }
    binPrefillPreview.classList.remove('d-none');
  }

  function applyBinPrefill(bin){
    if (!bin) {
      return;
    }
    const rawId = String(bin?.bin_id || bin?.id || '').trim();
    if (!rawId) {
      setLookupFeedback('Bin record is missing an ID.', 'danger');
      return;
    }
    if (binIdInput) {
      binIdInput.value = rawId;
    }
    if (binNameInput) {
      binNameInput.value = bin.name || rawId;
    }
    if (binAddressInput) {
      binAddressInput.value = bin.address || bin.barangay || bin.location || 'Himamaylan City';
    }
    const coordsSource = bin.gps && typeof bin.gps === 'object'
      ? bin.gps
      : {
          lat: bin.location_lat ?? bin.locationLat,
          lng: bin.location_lng ?? bin.locationLng
        };
    const lat = Number(coordsSource?.lat);
    const lng = Number(coordsSource?.lng);
    const safeLat = Number.isFinite(lat) ? lat : 0;
    const safeLng = Number.isFinite(lng) ? lng : 0;
    const battery = clampPercent(bin.battery ?? bin.battery_level ?? bin.batteryLevel);
    const fillBio = clampPercent(bin.compartments?.biodegradable ?? bin.fill_bio ?? bin.fillBio);
    const fillRec = clampPercent(bin.compartments?.recyclable ?? bin.fill_rec ?? bin.fillRecyclable);
    const fillRes = clampPercent(bin.compartments?.residual ?? bin.fill_res ?? bin.fillResidual);
    binPrefillState = {
      id: rawId.toLowerCase(),
      data: {
        locationLat: safeLat,
        locationLng: safeLng,
        battery,
        fillBio,
        fillRec,
        fillRes
      }
    };
    if (binLookupStatus) {
      binLookupStatus.classList.remove('d-none');
    }
    updatePrefillPreview(binPrefillState.data);
  }

  async function lookupBinInDatabase(binId){
    const id = String(binId || '').trim();
    if (!id) {
      setLookupFeedback('Enter a bin ID to scan.', 'danger');
      return;
    }
    const normalized = id.toLowerCase();
    if (pendingBinMap.has(normalized)) {
      applyBinPrefill(pendingBinMap.get(normalized));
      setLookupFeedback('Loaded details from the database.', 'success');
      return;
    }
    setLookupFeedback('Scanning database...', 'secondary');
    try{
      const response = await fetch(`api/bin_lookup.php?id=${encodeURIComponent(id)}`);
      const data = await response.json().catch(() => ({}));
      if (!response.ok || !data?.ok || !data?.bin) {
        const message = data?.error || 'No bin record found.';
        setLookupFeedback(message, 'danger');
        return;
      }
      const bin = data.bin;
      pendingBinMap.set(normalized, bin);
      applyBinPrefill(bin);
      setLookupFeedback('Loaded details from the database.', 'success');
    }catch(err){
      setLookupFeedback('Unable to scan the database right now.', 'danger');
    }
  }

  function setAddBinLoading(isLoading){
    if (addBinSubmit) {
      addBinSubmit.disabled = isLoading;
    }
    if (addBinSpinner) {
      addBinSpinner.classList.toggle('d-none', !isLoading);
    }
  }

  async function submitAddBin(payload){
    clearAddBinError();
    setAddBinLoading(true);
    try {
      const response = await fetch('api/bin_create.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await response.json().catch(() => ({}));
      if (!response.ok || !data?.ok) {
        throw new Error(data?.error || 'Unable to register bin.');
      }
      const normalizedPrefillId = binPrefillState ? binPrefillState.id : null;
      state.bins = [data.bin, ...state.bins.filter(bin => (bin.bin_id || bin.id) !== data.bin.bin_id)];
      render(state.bins);
      if (normalizedPrefillId) {
        pendingBinMap.delete(normalizedPrefillId);
      }
      ensureAddBinModal()?.hide();
      addBinForm?.reset();
      clearBinPrefill();
      setLookupFeedback('');
    } catch (error) {
      const message = error.message || 'Unable to register bin.';
      showAddBinError(message);
      alert(message);
    } finally {
      setAddBinLoading(false);
    }
  }

  function render(bins){
    if (!grid) return;
    const safeBins = Array.isArray(bins) ? bins : [];
    const nowMs = Date.now();
    // Skip rerender if nothing actually changed to prevent flicker/animation loops.
    const signature = binsSignature(safeBins, nowMs);
    if (signature === lastRenderSignature) {
      state.bins = safeBins;
      return;
    }
    lastRenderSignature = signature;
    const piState = computePiConnection(safeBins);
    const body = safeBins.length
      ? safeBins.map(bin => card(bin, nowMs)).join('')
      : '<div class="col-12"><div class="card p-4 text-center text-secondary">No bins detected yet.</div></div>';
    state.bins = safeBins;
    grid.innerHTML = body;
    updateConnectionBadges(safeBins);
    const derivedOptions = deriveRegisterableOptions(safeBins);
    updateBinOptions(derivedOptions);
  }
  if (state.bins.length) {
    render(state.bins);
  } else {
    render(SAMPLE_BINS);
  }

  grid?.addEventListener('click', (event) => {
    const card = event.target.closest('[data-bin-id]');
    if (!card) return;
    const id = card.getAttribute('data-bin-id');
    const bin = state.bins.find(b => (b.bin_id || b.id) === id);
    if (bin) {
      openModal(bin);
    }
  });

  function bindAddCard(){
    const addBtn = document.getElementById('addBinCardClient') || document.getElementById('addBinCard');
    if (!addBtn) return;
    addBtn.addEventListener('click', () => {
      const eventDetail = { action: 'add-bin', timestamp: Date.now() };
      document.dispatchEvent(new CustomEvent('ibin:add-bin', { detail: eventDetail }));
      if (!firebaseReady) {
        alert('Configure Firebase before registering bins.');
        return;
      }
      resetAddBinForm();
      openAddBinModal();
    });
  }

  addBinForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!firebaseReady) {
      showAddBinError('Firebase connection required before adding bins.');
      return;
    }
    const formData = new FormData(addBinForm);
    const binId = String(formData.get('bin_id') || '').trim();
    if (!/^[A-Za-z0-9_-]{3,64}$/.test(binId)) {
      showAddBinError('Bin ID must be 3-64 characters using letters, numbers, dash, or underscore.');
      return;
    }
    const payload = {
      bin_id: binId,
      name: String(formData.get('name') || '').trim(),
      address: String(formData.get('address') || '').trim(),
    };
    if (!payload.name) {
      payload.name = binId;
    }
    if (!payload.address) {
      payload.address = 'Himamaylan City';
    }
    if (binPrefillState && binPrefillState.id === binId.toLowerCase()) {
      const data = binPrefillState.data || {};
      payload.locationLat = Number.isFinite(data.locationLat) ? data.locationLat : 0;
      payload.locationLng = Number.isFinite(data.locationLng) ? data.locationLng : 0;
      payload.battery = Number.isFinite(data.battery) ? data.battery : 0;
      payload.fill_bio = Number.isFinite(data.fillBio) ? data.fillBio : 0;
      payload.fill_rec = Number.isFinite(data.fillRec) ? data.fillRec : 0;
      payload.fill_res = Number.isFinite(data.fillRes) ? data.fillRes : 0;
    }
    await submitAddBin(payload);
  });

  function getModalInstance(){
    if (modalInstance) {
      return modalInstance;
    }
    if (!modalEl) {
      return null;
    }
    if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
      modalInstance = new window.bootstrap.Modal(modalEl);
      return modalInstance;
    }
    console.warn('Bootstrap modal is not available yet.');
    return null;
  }

  function openModal(bin){
    const modal = getModalInstance();
    if (!modal) return;
    const avg = percentAvg(bin.compartments);
    const rawId = bin.bin_id || bin.id || '';
    const safeName = bin.name || rawId || 'Bin';
    const lastPing = formatPingTime(bin.last_ping || bin.lastPing || bin.last_ping_at || bin.lastPingAt || bin.updated_at || bin.last_updated);
    const lastUpdated = formatTime(bin.updated_at || bin.updatedAt || bin.last_updated);
    modalTitle.textContent = safeName;
    modalMeta.textContent = `${bin.address || 'Himamaylan City'} - Updated ${lastUpdated} - Last ping ${lastPing}`;
    modalLink.href = `bin_view.php?id=${encodeURIComponent(rawId)}`;
    const avgLabel = escapeHtml(String(avg));
    const coords = bin.gps ? `${Number(bin.gps.lat).toFixed(4)}, ${Number(bin.gps.lng).toFixed(4)}` : 'Not available';
    modalBody.innerHTML = `
      <div class="row g-3">
        <div class="col-12 col-md-7">
          <div class="border rounded-3 p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="text-secondary small">Status</span>
              ${statusChip(avg)}
            </div>
            <div class="mb-2">
              <div class="text-secondary small">Biodegradable</div>
              <div class="progress" role="progressbar"><div class="progress-bar bg-success" style="width:${bin.compartments?.biodegradable ?? 0}%"></div></div>
            </div>
            <div class="mb-2">
              <div class="text-secondary small">Recyclable</div>
              <div class="progress" role="progressbar"><div class="progress-bar bg-info" style="width:${bin.compartments?.recyclable ?? 0}%"></div></div>
            </div>
            <div class="mb-2">
              <div class="text-secondary small">Residual</div>
              <div class="progress" role="progressbar"><div class="progress-bar bg-warning" style="width:${bin.compartments?.residual ?? 0}%"></div></div>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-5">
          <ul class="list-group list-group-flush rounded-3 border">
            <li class="list-group-item d-flex justify-content-between">
              <span class="text-secondary">Average Fill</span>
              <strong>${avgLabel}%</strong>
            </li>
            <li class="list-group-item d-flex justify-content-between">
              <span class="text-secondary">Battery</span>
              <strong>${escapeHtml(Number.isFinite(Number(bin.battery)) ? `${Math.round(Number(bin.battery))}%` : '--')}</strong>
            </li>
            <li class="list-group-item d-flex justify-content-between">
              <span class="text-secondary">Last Ping</span>
              <strong>${escapeHtml(lastPing)}</strong>
            </li>
            <li class="list-group-item d-flex justify-content-between">
              <span class="text-secondary">Last Update</span>
              <strong>${escapeHtml(lastUpdated)}</strong>
            </li>
            <li class="list-group-item">
              <div class="text-secondary small">Coordinates</div>
              <div>${escapeHtml(coords)}</div>
            </li>
            <li class="list-group-item">
              <div class="text-secondary small">Bin ID</div>
              <div class="fw-semibold">${escapeHtml(rawId || 'N/A')}</div>
            </li>
          </ul>
        </div>
      </div>
      <div class="mt-3 small text-secondary">
        Want to drill deeper? Use the "Open Full View" button to inspect live history and the network map.
      </div>
    `;
    modal.show();
  }

  async function apiGet(urls){
    const candidates = Array.isArray(urls) ? urls : [urls];
    for (const url of candidates){
      try{
        const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!response.ok) continue;
        const type = response.headers.get('content-type') || '';
        if (!type.includes('application/json')) continue;
        const data = await response.json();
        if (data && Array.isArray(data?.bins) && String(data?.source || '').toLowerCase() !== 'firebase') {
          data.bins = applySampleResets(data.bins, { cloneAll: true });
        }
        if (data) return data;
      }catch(err){}
    }
    return { source: 'sample', bins: SAMPLE_BINS };
  }

  async function load(){
    try{
      const data = await apiGet(['api/bins.php','api/v1/bins.php']);
      const bins = data?.bins ?? (Array.isArray(data) ? data : []);
      if (bins.length){
        render(bins);
        return;
      }
    }catch(err){
      console.error('Bins preview falling back to sample data.', err);
    }
    state.bins = SAMPLE_BINS.slice();
    render(state.bins);
  }

  load();
  setInterval(load, 5000);
</script>
