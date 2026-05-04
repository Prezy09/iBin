<?php
// app/Views/collection/index.php
?>
<div class="collection-hero card p-4 mb-3">
  <div class="row g-4 align-items-center">
    <div class="col-lg-8">
      <div class="d-flex gap-3 align-items-start">
        <div class="hero-icon bubble bg-success-subtle text-success">
          <i class="bi bi-signpost-2-fill"></i>
        </div>
        <div>
          <p class="text-uppercase small text-secondary fw-semibold mb-1">Collection Routes</p>
          <h4 class="mb-2">Coordinate smarter pick-ups</h4>
          <p class="text-muted mb-0">Tune live thresholds, pin your starting point, and share ready-to-drive itineraries.</p>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
        <span class="collection-chip">
          <i class="bi bi-hdd-network me-1"></i>
          <?php echo $isLiveSource ? 'Live gateway' : 'Sample data'; ?>
        </span>
        <span class="collection-chip">
          <i class="bi bi-dot me-1"></i>
          <?php echo $liveBinCount; ?> live bins
        </span>
        <span class="collection-chip">
          <i class="bi bi-geo-alt me-1"></i>
          <?php echo $sampleBinCount; ?> preview bins
        </span>
      </div>
    </div>
  </div>
</div>
<div class="card p-4 mb-3 collection-control-card">
  <form id="routeForm" class="row g-4 align-items-stretch">
    <div class="col-12 col-md-4 col-lg-3 <?php echo $isOperator ? 'd-none' : ''; ?>">
      <div class="control-tile h-100">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div class="d-flex align-items-center gap-2">
            <span class="tile-icon">
              <i class="bi bi-sliders"></i>
            </span>
            <div>
              <p class="text-uppercase small text-muted mb-0">Pickup Threshold</p>
              <p class="text-secondary small mb-0">Routes auto-save when the slider moves.</p>
            </div>
          </div>
          <span class="tile-value" id="thresholdValue">70%</span>
        </div>
        <input id="threshold" type="range" class="form-range" value="70" min="0" max="100" step="1">
      </div>
    </div>
    <div class="col-12 col-md-4 col-lg-3 <?php echo $isOperator ? 'd-none' : ''; ?>">
      <div class="control-tile h-100">
        <label class="form-label d-flex align-items-center gap-2 mb-2">
          <span class="tile-icon">
            <i class="bi bi-geo-alt"></i>
          </span>
          <span>Start Bin</span>
        </label>
        <select id="startBin" class="form-select"></select>
        <div class="form-hint small text-muted mt-2">Defaults to the closest bin to the landfill.</div>
      </div>
    </div>
    <div class="col-12 col-lg-6">
      <div class="control-tile h-100 route-action-tile">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <label class="form-label d-flex align-items-center gap-2 mb-1">
              <span class="tile-icon">
                <i class="bi bi-lightning-charge-fill"></i>
              </span>
              <span>Route Actions</span>
            </label>
            <p class="text-secondary small mb-0">
              <?php if ($isOperator): ?>
                Mark bins as collected, then confirm the delivery.
              <?php else: ?>
                Optimize live routes, then notify operators.
              <?php endif; ?>
            </p>
          </div>
          <span class="role-chip badge <?php echo $isOperator ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'; ?>">
            <i class="bi <?php echo $isOperator ? 'bi-people' : 'bi-diagram-3'; ?>"></i>
            <?php echo $isOperator ? 'Operator' : 'Planner'; ?>
          </span>
        </div>
        <div class="action-buttons d-flex flex-wrap gap-2">
          <button type="submit" class="btn btn-success flex-fill <?php echo $isOperator ? 'd-none' : ''; ?>">Optimize Live Route</button>
          <button type="button" class="btn <?php echo $isOperator ? 'btn-outline-success' : 'btn-outline-warning'; ?> flex-fill d-flex align-items-center justify-content-center gap-2" id="requestPickupBtn" disabled>
            <i class="bi <?php echo $isOperator ? 'bi-check-circle' : 'bi-calendar-check'; ?>" data-icon></i>
            <span data-label><?php echo $isOperator ? 'Confirm Pickup' : 'Request Pickup'; ?></span>
          </button>
          <button type="button" class="btn btn-outline-primary flex-fill <?php echo $isOperator ? 'd-none' : ''; ?>" id="sampleBtn">
            <i class="bi bi-eyeglasses me-1"></i>
            Preview Himamaylan
          </button>
          <button type="button" class="btn btn-outline-danger flex-fill <?php echo $isOperator ? 'd-none' : ''; ?>" id="clearRouteBtn">
            <i class="bi bi-x-circle me-1"></i>
            Clear Route
          </button>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-6 <?php echo $isOperator ? '' : 'd-none'; ?>">
      <div class="control-tile h-100 route-action-tile">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <label class="form-label d-flex align-items-center gap-2 mb-1">
              <span class="tile-icon">
                <i class="bi bi-building-check"></i>
              </span>
              <span>Landfill Site</span>
            </label>
            <p class="text-secondary small mb-0">Confirm once all collected bins are offloaded at the landfill.</p>
          </div>
          <span class="badge bg-success-subtle text-success">Operator</span>
        </div>
        <div class="d-flex flex-column gap-3 align-items-stretch">
          <button type="button" class="btn btn-success d-flex align-items-center justify-content-center gap-2 w-100" id="landfillDoneBtn" disabled>
            <i class="bi bi-check2-circle"></i>
            <span>Confirm Landfill Delivery</span>
          </button>
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-geo-alt text-success"></i>
            <span class="small text-muted">Route origin: landfill drop-off</span>
          </div>
          <small class="text-muted">Enabled after every stop is marked Done.</small>
        </div>
      </div>
    </div>
  </form>
</div>
<div class="row g-3 mb-3">
  <div class="col-12 col-md-4">
    <div class="card route-stat-card h-100 p-4 text-center">
      <div class="stat-icon bg-success-subtle text-success">
        <i class="bi bi-rulers"></i>
      </div>
      <p class="text-secondary text-uppercase small fw-semibold mb-1">Optimized Distance</p>
      <div class="display-6 mb-0" id="distanceStat">0 km</div>
      <p class="small text-muted mb-0">Great-circle estimate</p>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="card route-stat-card h-100 p-4 text-center">
      <div class="stat-icon bg-warning-subtle text-warning">
        <i class="bi bi-stopwatch"></i>
      </div>
      <p class="text-secondary text-uppercase small fw-semibold mb-1">Estimated Travel Time</p>
      <div class="display-6 mb-0" id="travelStat">0 min</div>
      <p class="small text-muted mb-0">Assuming 25 km/h average</p>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="card route-stat-card h-100 p-4 text-center">
      <div class="stat-icon bg-primary-subtle text-primary">
        <i class="bi bi-inboxes"></i>
      </div>
      <p class="text-secondary text-uppercase small fw-semibold mb-1">Bins Scheduled</p>
      <div class="display-6 mb-0" id="binCountStat">0</div>
      <p class="small text-muted mb-0">Above current threshold</p>
    </div>
  </div>
</div>
<div class="card travel-breakdown-card p-4 mb-3" id="travelCallout" hidden>
  <div class="d-flex align-items-center gap-2 mb-2">
    <div class="hero-icon bubble bg-primary-subtle text-primary small">
      <i class="bi bi-clock-history"></i>
    </div>
    <div>
      <h6 class="mb-0">Travel Time Breakdown</h6>
      <p class="text-secondary small mb-0">Based on an average collection speed of <span class="fw-semibold">25 km/h</span>.</p>
    </div>
  </div>
  <div id="travelDetails" class="small text-dark"></div>
</div>
<div class="row g-3">
  <div class="col-12 col-xl-7">
    <div class="card map-panel p-0 overflow-hidden">
      <div id="map" style="height:420px;"></div>
    </div>
  </div>
  <div class="col-12 col-xl-5">
    <div class="card p-4 mb-3 collection-panel">
      <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
          <p class="text-uppercase text-secondary small fw-semibold mb-1">Optimized Route Order</p>
          <h6 class="mb-1">Stops arranged by nearest-neighbor path.</h6>
          <p class="text-muted small mb-0">Landfill origin is always first.</p>
        </div>
        <span class="mode-pill" id="usingSampleBadge">Sample</span>
      </div>
      <ol id="routeList" class="m-0 ps-0 list-unstyled route-timeline"></ol>
    </div>
    <div class="card p-4 collection-panel mb-3">
      <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
          <p class="text-uppercase text-secondary small fw-semibold mb-1">Himamaylan Sample Bins</p>
          <h6 class="mb-1">Preloaded for quick planning preview.</h6>
          <p class="text-muted small mb-0">Matches the latest bins dataset.</p>
        </div>
        <span class="mode-pill tone-primary" id="sampleCount"></span>
      </div>
      <div id="sampleList"></div>
    </div>
  </div>
</div>

<script>
  window.IBIN_CLIENT_STATE = <?php echo $clientStateJson; ?>;
  window.IBIN_SAMPLE_BINS = window.IBIN_CLIENT_STATE.sample || [];
</script>
<script type="module">
  import { apiGet, makeMap, percentAvg, SAMPLE_BINS as FALLBACK_SAMPLE_BINS, HIMAMAYLAN_CENTER, HIMAMAYLAN_LANDFILL, BASI_API_KEY, applySampleResets, recordSampleReset } from './assets/js/app.js';

  const AVG_SPEED_KMH = 25;
  const SERVER_STATE = window.IBIN_CLIENT_STATE || {};
  const sourceLabel = String(SERVER_STATE?.source || '').toLowerCase();
  const usingSampleSource = sourceLabel !== 'firebase';
  const bootstrapSeed = Array.isArray(SERVER_STATE.bins) ? SERVER_STATE.bins.map(normalizeBin) : [];
  const bootstrapBins = usingSampleSource ? applySampleResets(bootstrapSeed, { cloneAll: true }) : bootstrapSeed;
  const previewSeed = (Array.isArray(SERVER_STATE.sample) && SERVER_STATE.sample.length ? SERVER_STATE.sample : FALLBACK_SAMPLE_BINS).map(normalizeBin);
  let previewBins = [];
  function refreshPreviewBins(){
    previewBins = applySampleResets(previewSeed, { cloneAll: true });
    if (SERVER_STATE && Array.isArray(SERVER_STATE.sample)) {
      SERVER_STATE.sample = previewBins.slice();
    }
    if (Array.isArray(window.IBIN_SAMPLE_BINS)) {
      window.IBIN_SAMPLE_BINS = previewBins.slice();
    }
  }
  refreshPreviewBins();
  const ROUTING_URL = 'https://api.openrouteservice.org/v2/directions/driving-car/geojson';
  const BASI_ENABLED = Boolean(BASI_API_KEY);
  const landfillSite = {
    bin_id: 'landfill_site',
    name: 'Himamaylan Landfill',
    address: 'San Jose Village, Himamaylan City',
    gps: HIMAMAYLAN_LANDFILL,
    isLandfill: true,
    compartments: {}
  };
  const USER_ROLE = window.IBIN_USER_ROLE || 'User';
  const IS_OPERATOR = USER_ROLE === 'Operator';
  const CAN_COMPLETE_STOPS = IS_OPERATOR;
  const ROUTE_STATUS_POLL_MS = 5000;
  const COLLECTION_REFRESH_KEY = 'ibin_collection_refresh';

  const routeForm = document.getElementById('routeForm');
  const thresholdInput = document.getElementById('threshold');
  const thresholdDisplay = document.getElementById('thresholdValue');
  const startInput = document.getElementById('startBin');
  const sampleBtn = document.getElementById('sampleBtn');
  const clearRouteBtn = document.getElementById('clearRouteBtn');
  const routeList = document.getElementById('routeList');
  const distanceStat = document.getElementById('distanceStat');
  const travelStat = document.getElementById('travelStat');
  const binCountStat = document.getElementById('binCountStat');
  const travelCallout = document.getElementById('travelCallout');
  const travelDetails = document.getElementById('travelDetails');
  const usingSampleBadge = document.getElementById('usingSampleBadge');
  const sampleList = document.getElementById('sampleList');
  const sampleCount = document.getElementById('sampleCount');
  const requestPickupBtn = document.getElementById('requestPickupBtn');
  const landfillDoneBtn = document.getElementById('landfillDoneBtn');

  let mapCtx;
  let markerPins = [];
  let routePolyline = null;
  let lastMapData = bootstrapBins.length ? bootstrapBins.slice() : previewBins.slice();
  let routeStatuses = new Map();
  let routeStatusPollTimer = null;
  let lastRouteStatusSignature = '';
  let lastRouteStructureSignature = '';
  let lastRouteState = 'idle';
  const sessionRefreshToken = `${Date.now()}-${Math.random()}`;
  const ESCAPE_LOOKUP = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
  function escapeHTML(value){
    return String(value ?? '').replace(/[&<>"']/g, char => ESCAPE_LOOKUP[char] || char);
  }

  function broadcastCollectionRefresh(action){
    if (typeof localStorage === 'undefined') {
      return;
    }
    try{
      const payload = {
        action: action || 'update',
        at: Date.now(),
        role: USER_ROLE,
        token: sessionRefreshToken
      };
      localStorage.setItem(COLLECTION_REFRESH_KEY, JSON.stringify(payload));
    }catch(err){
      console.warn('Collection refresh broadcast failed', err);
    }
  }

  function handleIncomingCollectionRefresh(payload){
    if (!payload) {
      return;
    }
    document.dispatchEvent(new CustomEvent('ibin:refresh-notifications', {
      detail: { source: 'collection-sync', action: payload.action || 'update' }
    }));
    if (IS_OPERATOR) {
      loadRouteStatus({ refreshUI: true, skipOperatorHydrate: false });
    } else {
      loadRouteStatus({ refreshUI: false, skipOperatorHydrate: true });
    }
  }

  window.addEventListener('storage', (event) => {
    if (event.key !== COLLECTION_REFRESH_KEY || !event.newValue) {
      return;
    }
    try{
      const payload = JSON.parse(event.newValue);
      handleIncomingCollectionRefresh(payload);
    }catch(err){
      console.warn('Unable to parse collection refresh payload', err);
    }
  });

  async function ensureMap(center){
    if (!mapCtx){
      mapCtx = await makeMap('map', center || HIMAMAYLAN_LANDFILL || HIMAMAYLAN_CENTER, { zoom: 14 });
    }
    return mapCtx;
  }
  const state = {
    usingSample: bootstrapBins.length === 0,
    lastStats: null,
    lastRoute: [landfillSite],
    lastLegs: [],
    bins: bootstrapBins.length ? bootstrapBins.slice() : previewBins.slice(),
    lastSummary: null,
    lastMeta: null,
    routeScheduled: false,
    scheduling: false,
    canSchedule: false,
    confirmReady: false,
    routeStatus: 'idle'
  };

  syncThresholdDisplay();
  updateRequestPickupState();
  if (thresholdInput) {
    thresholdInput.addEventListener('input', syncThresholdDisplay);
    thresholdInput.addEventListener('change', () => {
      syncThresholdDisplay();
      markRouteDirty();
      load();
    });
  }

  routeForm.addEventListener('submit', (event) => {
    event.preventDefault();
    state.usingSample = false;
    markRouteDirty();
    load();
  });

  if (sampleBtn){
    sampleBtn.addEventListener('click', () => {
      state.usingSample = true;
      markRouteDirty();
      load();
    });
  }

  routeList?.addEventListener('click', (event) => {
    const btn = event.target.closest('.mark-done-btn');
    if (!btn) return;
    const binId = btn.getAttribute('data-bin-id');
    if (binId) {
      markStopDone(binId);
    }
  });

  if (clearRouteBtn){
    clearRouteBtn.addEventListener('click', () => {
      clearRoute();
    });
  }
  if (requestPickupBtn){
    const pickupHandler = IS_OPERATOR ? confirmPickupCompletion : schedulePickupRoute;
    requestPickupBtn.addEventListener('click', pickupHandler);
  }
  if (landfillDoneBtn){
    landfillDoneBtn.addEventListener('click', () => {
      if (landfillDoneBtn.disabled) return;
      confirmPickupCompletion();
    });
  }

  function showTravelDetails(options = {}){
    if (!state.lastStats || state.lastStats.totalBins === 0){
      travelCallout.hidden = true;
      travelDetails.innerHTML = '';
      return;
    }
    const { scrollIntoView = false } = options;
    travelCallout.hidden = false;
    travelDetails.innerHTML = `
      <div class="travel-detail">
        <i class="bi bi-rulers"></i>
        <div>
          <div class="fw-semibold">${state.lastStats.distanceText}</div>
          <div class="text-muted small">Across ${state.lastStats.totalBins} scheduled stops</div>
        </div>
      </div>
      <div class="travel-detail">
        <i class="bi bi-stopwatch"></i>
        <div>
          <div class="fw-semibold">${state.lastStats.durationText}</div>
          <div class="text-muted small">At ${AVG_SPEED_KMH} km/h average speed</div>
        </div>
      </div>
    `;
    if (scrollIntoView){
      travelCallout.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  }

  function renderSampleList(){
    if (!previewBins.length){
      sampleCount.textContent = '0 bins';
      sampleList.innerHTML = '<div class="text-secondary small">No preview bins available.</div>';
      return;
    }
    sampleCount.textContent = previewBins.length + ' bins';
    sampleList.innerHTML = previewBins.map(bin => {
      const avg = percentAvg(bin.compartments);
      const tone = avg >= 80 ? 'danger' : (avg >= 60 ? 'warning' : 'success');
      const iconClass = tone === 'danger'
        ? 'bi-exclamation-triangle-fill'
        : (tone === 'warning' ? 'bi-recycle' : 'bi-trash3-fill');
      const coordText = bin.gps ? `Lat ${bin.gps.lat.toFixed(4)}, Lng ${bin.gps.lng.toFixed(4)}` : 'No GPS data';
      return `
        <div class="sample-bin-item py-3 border-bottom d-flex align-items-start gap-3">
          <div class="sample-bin-icon tone-${tone}">
            <i class="bi ${iconClass}"></i>
          </div>
          <div>
            <div class="d-flex flex-wrap align-items-center gap-2">
              <div class="fw-semibold">${bin.name}</div>
              <span class="badge rounded-pill fill-pill tone-${tone}">${avg}%</span>
            </div>
            <div class="small text-secondary">${bin.address || 'Collection point'}</div>
            <div class="small text-muted">${coordText}</div>
          </div>
        </div>
      `;
    }).join('') + '<div class="small text-muted pt-2 sample-list-hint">Preview data mirrors the Bins page dataset.</div>';
  }

  renderSampleList();
  window.addEventListener('ibin:sample-resets-changed', () => {
    refreshPreviewBins();
    renderSampleList();
    if (state.usingSample) {
      load();
    }
  });
  populateStartBinOptions(lastMapData);
  async function loadRouteStatus(options = {}){
    const { refreshUI = true, skipOperatorHydrate = false } = options;
    try{
      const response = await fetch('api/route_activity.php');
      if (!response.ok) return null;
      const data = await response.json();
      const stops = Array.isArray(data?.stops) ? data.stops : [];
      routeStatuses = new Map();
      const signatureParts = [];
      const structureParts = [];
      stops.forEach(stop => {
        if (!stop?.bin_id) return;
        const status = stop.status || 'scheduled';
        routeStatuses.set(stop.bin_id, status);
        signatureParts.push(`${stop.bin_id}:${status}`);
        structureParts.push(stop.bin_id);
      });
      const signature = signatureParts.join('|');
      const structureSignature = structureParts.join('|');
      const statusValue = typeof data?.status === 'string' ? data.status : 'idle';
      const statusChanged = signature !== lastRouteStatusSignature || statusValue !== lastRouteState;
      const structureChanged = structureSignature !== lastRouteStructureSignature;
      lastRouteStatusSignature = signature;
      lastRouteStructureSignature = structureSignature;
      lastRouteState = statusValue;
      state.routeStatus = statusValue;
      applyOperatorRouteState(stops, statusValue);
      if (IS_OPERATOR) {
        if (!skipOperatorHydrate && structureChanged) {
          await loadOperatorSchedule(data);
        } else if (refreshUI && statusChanged && state.lastRoute && state.lastLegs) {
          renderRoute(state.lastRoute, state.lastLegs);
        }
      } else if (refreshUI && statusChanged && state.lastRoute && state.lastLegs) {
        renderRoute(state.lastRoute, state.lastLegs);
      }
      return data;
    }catch(err){
      console.error('Failed to load route activity', err);
      return null;
    }
  }
  function applyOperatorRouteState(stops, statusValue){
    if (!IS_OPERATOR){
      state.confirmReady = false;
      return;
    }
    const deliverableStops = (Array.isArray(stops) ? stops : []).filter(stop => !stop?.isLandfill);
    const totalStops = deliverableStops.length;
    const deliveredCount = deliverableStops.filter(stop => (stop?.status || 'scheduled') === 'delivered').length;
    const pendingCount = totalStops - deliveredCount;
    const awaitingConfirmation = totalStops > 0 && pendingCount === 0 && statusValue !== 'completed';
    state.confirmReady = awaitingConfirmation;
    if (statusValue === 'completed' || totalStops === 0){
      state.confirmReady = false;
    }
    updateRequestPickupState();
  }
  loadRouteStatus({ refreshUI: false, skipOperatorHydrate: IS_OPERATOR });
  routeStatusPollTimer = setInterval(() => {
    loadRouteStatus({ refreshUI: true });
  }, ROUTE_STATUS_POLL_MS);

  function syncThresholdDisplay(){
    if (!thresholdDisplay || !thresholdInput) return;
    const value = Number(thresholdInput.value || 0);
    thresholdDisplay.textContent = `${value}%`;
  }

  function getThresholdValue(){
    if (!thresholdInput) {
      return 70;
    }
    return Number(thresholdInput.value || 70);
  }

  function markRouteDirty(){
    state.routeScheduled = false;
    state.canSchedule = false;
    updateRequestPickupState();
  }

  function updateRequestPickupState(){
    if (!requestPickupBtn) return;
    const icon = requestPickupBtn.querySelector('[data-icon]');
    const label = requestPickupBtn.querySelector('[data-label]');
    if (IS_OPERATOR){
      requestPickupBtn.classList.remove('btn-outline-warning');
      requestPickupBtn.classList.toggle('btn-success', state.routeStatus === 'completed');
      requestPickupBtn.classList.toggle('btn-outline-success', state.routeStatus !== 'completed');
      const operatorDisabled = state.routeStatus === 'completed'
        ? true
        : (!state.confirmReady || state.scheduling);
      requestPickupBtn.disabled = operatorDisabled;
      if (landfillDoneBtn) {
        landfillDoneBtn.disabled = operatorDisabled;
        landfillDoneBtn.classList.toggle('btn-success', !operatorDisabled);
        landfillDoneBtn.classList.toggle('btn-outline-success', operatorDisabled);
      }
      if (label) {
        if (state.scheduling) {
          label.textContent = 'Confirming...';
        } else if (state.routeStatus === 'completed') {
          label.textContent = 'Pickup Confirmed';
        } else {
          label.textContent = 'Confirm Pickup';
        }
      }
      if (icon) {
        icon.className = state.scheduling
          ? 'bi bi-arrow-repeat'
          : (state.routeStatus === 'completed' ? 'bi bi-check2-circle' : 'bi bi-check-circle');
      }
      return;
    }
    if (state.routeScheduled){
      requestPickupBtn.disabled = true;
      requestPickupBtn.classList.remove('btn-outline-warning');
      requestPickupBtn.classList.add('btn-success');
      if (icon) icon.className = 'bi bi-check2-circle';
      if (label) label.textContent = 'Pickup Scheduled';
      return;
    }
    requestPickupBtn.classList.add('btn-outline-warning');
    requestPickupBtn.classList.remove('btn-success', 'btn-outline-success');
    requestPickupBtn.disabled = !state.canSchedule || state.scheduling;
    if (label) {
      label.textContent = state.scheduling ? 'Requesting...' : 'Request Pickup';
    }
    if (icon) {
      icon.className = state.scheduling ? 'bi bi-arrow-repeat' : 'bi bi-calendar-check';
    }
  }

  async function clearRoute(){
    state.lastRoute = [landfillSite];
    const summary = summarizeRoute(state.lastRoute);
    renderRoute(state.lastRoute, summary.legs, 'Route cleared. Adjust the slider or add bins to plan again.');
    updateStats(state.lastRoute, lastMapData || [], summary.totalDistance, summary.totalMinutes);
    showTravelDetails();
    await drawMap(lastMapData && lastMapData.length ? lastMapData : previewBins, state.lastRoute);
    state.canSchedule = false;
    state.routeScheduled = false;
    updateRequestPickupState();
  }

  function populateStartBinOptions(bins){
    if (!(startInput instanceof HTMLSelectElement)){
      return;
    }
    const select = startInput;
    const previous = select.value;
    select.innerHTML = '';

    const autoOption = new Option('Auto-pick nearest to landfill', '');
    select.appendChild(autoOption);

    (Array.isArray(bins) ? bins : []).filter(hasCoords).forEach(bin => {
      const key = getBinKey(bin);
      if (!key) return;
      const label = `${bin.name || key} (${percentAvg(bin.compartments)}%)`;
      select.appendChild(new Option(label, key));
    });

    const hasPrevious = previous && Array.from(select.options).some(opt => opt.value === previous);
    select.value = hasPrevious ? previous : '';
  }

  function normalizeBin(bin, index = 0){
    const comp = bin?.compartments ?? {
      biodegradable: Number(bin?.fill_bio ?? bin?.bio ?? bin?.biodegradable ?? 0),
      recyclable: Number(bin?.fill_rec ?? bin?.rec ?? bin?.recyclable ?? 0),
      residual: Number(bin?.fill_res ?? bin?.res ?? bin?.residual ?? 0)
    };
    const gps = normalizeGps(bin?.gps) || normalizeGps({ lat: bin?.location_lat, lng: bin?.location_lng });
    return {
      ...bin,
      bin_id: bin?.bin_id ?? bin?.id ?? `bin_${index + 1}`,
      name: bin?.name ?? bin?.bin_id ?? `Bin ${index + 1}`,
      compartments: comp,
      gps
    };
  }

  function hasCoords(bin){
    return Boolean(bin?.gps) &&
      Number.isFinite(Number(bin.gps.lat)) &&
      Number.isFinite(Number(bin.gps.lng));
  }

  function normalizeGps(gps){
    if (!gps) return null;
    const lat = Number(gps.lat);
    const lng = Number(gps.lng);
    if (!Number.isFinite(lat) || !Number.isFinite(lng)){
      return null;
    }
    return { lat, lng };
  }

  async function load(){
    travelCallout.hidden = true;
    routeList.innerHTML = '<li class="route-step empty"><div class="route-step-card text-secondary small">Loading route...</div></li>';
    if (IS_OPERATOR) {
      usingSampleBadge.textContent = 'Assigned Route';
      usingSampleBadge?.classList.remove('preview');
      usingSampleBadge?.classList.add('live');
      state.usingSample = false;
      const activity = await loadRouteStatus({ refreshUI: false, skipOperatorHydrate: true });
      await loadOperatorSchedule(activity);
      return;
    }
    usingSampleBadge.textContent = state.usingSample ? 'Preview Route' : 'Live Route';
    usingSampleBadge?.classList.remove('preview', 'live');
    usingSampleBadge?.classList.add(state.usingSample ? 'preview' : 'live');
    const threshold = getThresholdValue();
    const startId = (startInput.value || '').trim();
    const dataset = state.usingSample ? { bins: previewBins } : await apiGet(['api/bins.php', 'api/v1/bins.php']);
    const fetched = Array.isArray(dataset) ? dataset : (dataset?.bins ?? dataset ?? []);
    let allBins = Array.isArray(fetched) ? fetched.map(normalizeBin) : [];
    if (!allBins.length && !state.usingSample && bootstrapBins.length){
      allBins = bootstrapBins;
    }
    if (!allBins.length){
      allBins = previewBins;
    }
    await loadRouteStatus();
    const binsAboveThreshold = allBins.filter(b => percentAvg(b.compartments) >= threshold);
    const binsWithCoords = binsAboveThreshold.filter(hasCoords);
    const mapData = allBins.length ? allBins : previewBins;
    populateStartBinOptions(binsWithCoords.length ? binsWithCoords : mapData);

    if (!binsAboveThreshold.length){
      const idleRoute = [landfillSite];
      const summary = summarizeRoute(idleRoute);
      state.lastRoute = idleRoute;
      state.lastLegs = summary.legs;
      renderRoute(idleRoute, summary.legs, 'No bins above the threshold for this view.');
      updateStats(idleRoute, allBins, summary.totalDistance, summary.totalMinutes);
      showTravelDetails();
      await drawMap(mapData, idleRoute);
      state.canSchedule = false;
      state.routeScheduled = false;
      updateRequestPickupState();
      return;
    }

    if (!binsWithCoords.length){
      const idleRoute = [landfillSite];
      const summary = summarizeRoute(idleRoute);
      state.lastRoute = idleRoute;
      state.lastLegs = summary.legs;
      renderRoute(idleRoute, summary.legs, 'Bins above the threshold need GPS coordinates before routing.');
      updateStats(idleRoute, allBins, summary.totalDistance, summary.totalMinutes);
      showTravelDetails();
      await drawMap(mapData, idleRoute);
      state.canSchedule = false;
      state.routeScheduled = false;
      updateRequestPickupState();
      return;
    }

    const route = buildNearestRoute(landfillSite, binsWithCoords, startId);
    if (route.length > 1) {
      route.push({ ...landfillSite, name: 'Return to Landfill', isLandfill: true });
    }
    state.lastRoute = route;

    const summary = summarizeRoute(route);
    state.lastLegs = summary.legs;
    state.lastSummary = summary;
    state.lastMeta = {
      threshold,
      startId,
      candidateBins: binsWithCoords.length,
      usingSample: state.usingSample
    };
    renderRoute(route, summary.legs);
    updateStats(route, allBins, summary.totalDistance, summary.totalMinutes);
    showTravelDetails();
    const actionableStops = route.filter(stop => !stop.isLandfill).length;
    state.canSchedule = !state.usingSample && actionableStops > 0;
    if (!state.canSchedule) {
      state.routeScheduled = false;
    }
    updateRequestPickupState();
    await drawMap(mapData, route);
    lastMapData = mapData || [];
  }

  async function loadOperatorSchedule(activityData = null){
    const routeData = activityData || await loadRouteStatus({ refreshUI: false });
    const stops = Array.isArray(routeData?.stops) ? routeData.stops : [];
    const dataset = await apiGet(['api/bins.php', 'api/v1/bins.php']);
    const fetched = Array.isArray(dataset) ? dataset : (dataset?.bins ?? dataset ?? []);
    let allBins = Array.isArray(fetched) ? fetched.map(normalizeBin) : [];
    if (!allBins.length && bootstrapBins.length){
      allBins = bootstrapBins.slice();
    }
    if (!allBins.length){
      allBins = previewBins.slice();
    }
    const binLookup = new Map();
    (allBins || []).forEach(bin => {
      const key = (getBinKey(bin) || '').toLowerCase();
      if (key) {
        binLookup.set(key, bin);
      }
    });
    const route = [landfillSite];
    stops.forEach((stop, index) => {
      const hydrated = hydrateActivityStop(stop, binLookup, index);
      if (hydrated) {
        route.push(hydrated);
      }
    });
    if (route.length > 1) {
      route.push({ ...landfillSite, name: 'Return to Landfill', isLandfill: true });
    }
    const summary = summarizeRoute(route);
    state.lastRoute = route;
    state.lastLegs = summary.legs;
    state.lastSummary = summary;
    state.lastMeta = null;
    renderRoute(route, summary.legs, 'No pickup routes have been scheduled by the admins yet.');
    updateStats(route, allBins, summary.totalDistance, summary.totalMinutes);
    showTravelDetails();
    const mapData = allBins.length ? allBins : previewBins;
    await drawMap(mapData, route);
    lastMapData = mapData || [];
    state.canSchedule = false;
    state.routeScheduled = false;
    updateRequestPickupState();
  }

  function buildNearestRoute(origin, bins, preferredStartId){
    const route = [];
    if (origin) {
      route.push(origin);
    }
    if (!bins.length){
      return route;
    }
    const lookup = new Map();
    bins.forEach(bin => {
      const key = getBinKey(bin);
      if (key) {
        lookup.set(key, bin);
      }
    });
    const remaining = new Map(lookup);
    let current = null;

    if (preferredStartId && remaining.has(preferredStartId)){
      current = remaining.get(preferredStartId);
      remaining.delete(preferredStartId);
      route.push(current);
    }else{
      const first = closestByCoords(origin?.gps, remaining);
      if (first){
        const firstKey = getBinKey(first);
        if (firstKey) remaining.delete(firstKey);
        route.push(first);
        current = first;
      }
    }

    while (remaining.size){
      const next = closestByCoords(current?.gps || origin?.gps, remaining);
      if (!next){
        const iterator = remaining.values();
        const fallback = iterator.next().value;
        if (!fallback) break;
        route.push(fallback);
        const fallbackKey = getBinKey(fallback);
        if (fallbackKey) remaining.delete(fallbackKey);
        current = fallback;
        continue;
      }
      route.push(next);
      const key = getBinKey(next);
      if (key) remaining.delete(key);
      current = next;
    }

    return route;
  }

  function hydrateActivityStop(stop, binLookup, index){
    if (!stop) {
      return null;
    }
    const rawId = stop.bin_id || stop.id || '';
    if (!rawId) {
      return null;
    }
    const key = rawId.toLowerCase();
    const reference = key && binLookup.has(key) ? binLookup.get(key) : null;
    const merged = {
      ...(reference || {}),
      ...stop,
      bin_id: rawId,
      id: rawId
    };
    if (!merged.compartments && reference?.compartments) {
      merged.compartments = reference.compartments;
    }
    if (!merged.gps && reference?.gps) {
      merged.gps = reference.gps;
    }
    if (!merged.compartments) {
      const avg = Number(stop.fill_avg ?? reference?.fill_avg ?? 0);
      merged.compartments = {
        biodegradable: avg,
        recyclable: avg,
        residual: avg
      };
    }
    if (!merged.address) {
      merged.address = reference?.address || 'Collection point';
    }
    return normalizeBin(merged, index + 1);
  }

  function getBinKey(bin){
    return bin?.bin_id || bin?.id || bin?.name || '';
  }

  function closestByCoords(source, pool){
    if (!source || !Number.isFinite(Number(source.lat)) || !Number.isFinite(Number(source.lng))){
      return null;
    }
    let winner = null;
    let bestDistance = Infinity;
    pool.forEach(candidate => {
      if (!candidate?.gps) return;
      const distance = haversine(source, candidate.gps);
      if (distance < bestDistance){
        bestDistance = distance;
        winner = candidate;
      }
    });
    return winner;
  }

  function summarizeRoute(route){
    let totalDistance = 0;
    let totalMinutes = 0;
    const legs = route.map((bin, index) => {
      if (index === 0) return { distance: 0, minutes: 0 };
      const prev = route[index - 1];
      if (!prev?.gps || !bin?.gps) return { distance: 0, minutes: 0 };
      const distance = haversine(prev.gps, bin.gps);
      totalDistance += distance;
      const minutes = (distance / AVG_SPEED_KMH) * 60;
      totalMinutes += minutes;
      return { distance, minutes };
    });
    return { legs, totalDistance, totalMinutes };
  }

  function renderRoute(route, legs, emptyMessage = 'No bins above the threshold for this view.'){
    if (!route.length){
      routeList.innerHTML = '<li class="py-2 text-secondary">No route generated.</li>';
      return;
    }
    const markup = route.map((stop, index) => {
      const leg = legs[index] || { distance: 0, minutes: 0 };
      const isLandfill = Boolean(stop.isLandfill);
      const avgVal = percentAvg(stop.compartments);
      const badge = isLandfill
        ? '<span class="badge route-badge origin">Origin</span>'
        : `<span class="badge route-badge">${avgVal}% full</span>`;
      const title = isLandfill ? 'Landfill Site (Origin)' : `${index}. ${stop.name || stop.bin_id}`;
      const subtitle = isLandfill ? formatCoords(stop.gps) : (stop.address || 'Collection point');
      const legInfo = index === 0
        ? 'Launch point for collection crews.'
        : `${leg.distance.toFixed(2)} km / ${Math.round(leg.minutes)} min from previous stop`;
      const rawId = stop.bin_id || stop.id || '';
      const safeId = escapeHTML(rawId || '');
      let actionHtml = '';
      if (!isLandfill && CAN_COMPLETE_STOPS) {
        const status = routeStatuses.get(rawId) || 'scheduled';
        if (status === 'delivered') {
          actionHtml = '<span class="badge bg-success-subtle text-success-emphasis"><i class="bi bi-check2-circle me-1"></i>Picked up</span>';
        } else {
          actionHtml = `<button type="button" class="btn btn-sm btn-outline-success mark-done-btn" data-bin-id="${safeId}"><i class="bi bi-check-lg me-1"></i>Done</button>`;
        }
      }
      const markerContent = isLandfill
        ? '<i class="bi bi-signpost-2-fill"></i>'
        : `<span>${String(index).padStart(2, '0')}</span>`;
      return `
        <li class="route-step ${isLandfill ? 'is-landfill' : ''}">
          <div class="route-step-marker">
            ${markerContent}
          </div>
          <div class="route-step-card">
            <div class="d-flex justify-content-between align-items-start gap-3">
              <div>
                <div class="fw-semibold">${title}</div>
                <div class="small text-secondary">${subtitle}</div>
              </div>
              <div class="d-flex flex-wrap align-items-center gap-2">
                ${badge}
                ${actionHtml}
              </div>
            </div>
            <div class="route-leg small text-muted mt-2">
              <i class="bi bi-arrow-right-short me-1"></i>${legInfo}
            </div>
          </div>
        </li>
      `;
    }).join('');
    routeList.innerHTML = markup + (route.length === 1 ? `<li class="route-step empty"><div class="route-step-card text-secondary small">${emptyMessage}</div></li>` : '');
  }

  async function markStopDone(binId){
    if (!CAN_COMPLETE_STOPS || !binId) {
      return;
    }
    try{
      const response = await fetch('api/route_activity.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'mark_done', bin_id: binId })
      });
      if (!response.ok) {
        throw new Error('Unable to update status');
      }
      await loadRouteStatus();
      state.lastRoute = applySampleResets(state.lastRoute || [], { cloneAll: true });
      state.bins = applySampleResets(state.bins || [], { cloneAll: true });
      lastMapData = applySampleResets(lastMapData || [], { cloneAll: true });
      renderRoute(state.lastRoute, state.lastLegs || [], 'Route cleared.');
      recordSampleReset(binId);
      document.dispatchEvent(new CustomEvent('ibin:refresh-notifications', {
        detail: { source: 'collection', binId }
      }));
      broadcastCollectionRefresh('stop-delivered');
    }catch(err){
      console.error('Status update failed', err);
      alert('Unable to update the bin status. Please try again.');
    }
  }

  async function confirmPickupCompletion(){
    if (!IS_OPERATOR) {
      return;
    }
    if (!state.confirmReady){
      alert('Complete every scheduled stop before confirming the pickup.');
      return;
    }
    state.scheduling = true;
    updateRequestPickupState();
    try{
      const response = await fetch('api/route_activity.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'confirm_route' })
      });
      if (!response.ok) {
        const message = response.status === 409
          ? 'Complete every scheduled stop before confirming the pickup.'
          : 'Unable to confirm the pickup. Please try again.';
        console.warn('Pickup confirmation failed', response.status);
        alert(message);
        return;
      }
      await loadRouteStatus({ refreshUI: true });
      alert('Pickup confirmation sent. Admins have been notified.');
      document.dispatchEvent(new CustomEvent('ibin:refresh-notifications', {
        detail: { source: 'collection', action: 'pickup-confirmed' }
      }));
      broadcastCollectionRefresh('pickup-confirmed');
    }catch(err){
      console.error('Failed to confirm pickup', err);
      alert('Unable to confirm the pickup. Please try again.');
    }finally{
      state.scheduling = false;
      updateRequestPickupState();
    }
  }

  async function schedulePickupRoute(){
    if (!requestPickupBtn) return;
    if (state.usingSample || !state.canSchedule || state.routeScheduled) {
      alert('Optimize a live route before requesting pickups.');
      return;
    }
    if (!state.lastRoute || state.lastRoute.length <= 1) {
      alert('Optimize a live route before requesting pickups.');
      return;
    }
    const summary = state.lastSummary || summarizeRoute(state.lastRoute);
    const meta = state.lastMeta || {
      threshold: getThresholdValue(),
      startId: (startInput.value || '').trim(),
      candidateBins: Math.max(state.lastRoute.length - 1, 0),
      usingSample: state.usingSample
    };
    if (meta.usingSample) {
      alert('Switch to Live Route mode before requesting pickups.');
      return;
    }
    state.scheduling = true;
    updateRequestPickupState();
    const success = await persistRoutePlan(state.lastRoute, summary, meta);
    state.scheduling = false;
    if (success) {
      state.routeScheduled = true;
      await loadRouteStatus({ refreshUI: true });
      document.dispatchEvent(new CustomEvent('ibin:refresh-notifications', {
        detail: { source: 'collection', action: 'pickup-request' }
      }));
      alert('Pickup request sent to operators.');
      broadcastCollectionRefresh('pickup-scheduled');
    } else {
      alert('Unable to schedule the pickup right now. Please try again.');
    }
    updateRequestPickupState();
  }

  async function persistRoutePlan(route, summary, meta = {}){
    if (!route?.length || meta.usingSample){
      return false;
    }
    const payload = {
      threshold: Number(meta.threshold ?? getThresholdValue()),
      start_bin: meta.startId || '',
      using_sample: Boolean(meta.usingSample),
      avg_speed_kmh: AVG_SPEED_KMH,
      total_distance_km: Number((summary.totalDistance ?? 0).toFixed(3)),
      travel_minutes: Number((summary.totalMinutes ?? 0).toFixed(2)),
      bins_scheduled: Math.max(route.length - 1, 0),
      candidate_bins: meta.candidateBins ?? 0,
      optimized_at: new Date().toISOString(),
      source: 'collection_ui',
      stops: route.map((stop, index) => {
        const gps = normalizeGps(stop.gps);
        return {
          order: index,
          bin_id: stop.bin_id || stop.id || null,
          name: stop.name || stop.bin_id || (stop.isLandfill ? 'Landfill' : 'Stop'),
          address: stop.address || '',
          gps,
          compartments: stop.compartments ? { ...stop.compartments } : null,
          fill_avg: percentAvg(stop.compartments),
          is_landfill: Boolean(stop.isLandfill),
          battery: typeof stop.battery === 'number' ? stop.battery : null
        };
      })
    };
    try{
      const routeResponse = await fetch('api/route.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const activityResponse = await fetch('api/route_activity.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'sync_route',
          stops: route
        })
      });
      if (!routeResponse.ok || !activityResponse.ok) {
        throw new Error('Route persistence failed');
      }
      return true;
    }catch(err){
      console.warn('Failed to persist route plan', err);
      return false;
    }
  }

  function updateStats(route, dataset, totalDistance = 0, totalMinutes = null){
    const stops = Math.max(route.length - 1, 0);
    const distanceText = `${totalDistance.toFixed(1)} km`;
    const travelMinutes = typeof totalMinutes === 'number'
      ? totalMinutes
      : totalDistance > 0
        ? (totalDistance / AVG_SPEED_KMH) * 60
        : 0;
    const durationText = formatDuration(travelMinutes);

    distanceStat.textContent = distanceText;
    travelStat.textContent = durationText;
    binCountStat.textContent = stops.toString();
    state.lastStats = {
      distanceKm: totalDistance,
      travelMinutes,
      durationText,
      distanceText,
      totalBins: stops
    };
  }

  async function updateMap(markerData, route){
    const candidates = Array.isArray(markerData) ? markerData.filter(hasCoords) : [];
    const nextCenter = route[1]?.gps || candidates[0]?.gps || HIMAMAYLAN_LANDFILL || HIMAMAYLAN_CENTER;
    const ctx = await ensureMap(nextCenter);
    const { map, L } = ctx;

    markerPins.forEach(layer => layer.remove());
    markerPins = [];
    if (routePolyline){
      routePolyline.remove();
      routePolyline = null;
    }

    const landfillLatLng = [HIMAMAYLAN_LANDFILL.lat, HIMAMAYLAN_LANDFILL.lng];
    const landfillMarker = L.marker(landfillLatLng, { title: 'Himamaylan Landfill' }).addTo(map);
    landfillMarker.bindPopup('<strong>Himamaylan Landfill</strong><br>Route origin for collection crews.');
    markerPins.push(landfillMarker);

    const routeOrder = new Map();
    route.forEach((stop, index) => {
      if (index === 0) return;
      const key = getBinKey(stop);
      if (key) routeOrder.set(key, index);
    });

    candidates.forEach(bin => {
      const order = routeOrder.get(getBinKey(bin));
      const color = typeof order === 'number' ? '#198754' : '#6c757d';
      const marker = L.circleMarker([bin.gps.lat, bin.gps.lng], {
        radius: typeof order === 'number' ? 9 : 6,
        color,
        weight: 2,
        fillColor: color,
        fillOpacity: 0.85
      }).addTo(map);
        const label = typeof order === 'number' ? `Stop #${order}` : 'Bin';
      marker.bindPopup(`
        <div class="fw-semibold mb-1">${bin.name || bin.bin_id}</div>
        <div class="small text-secondary">${bin.address || 'Collection point'}</div>
        <div class="small text-muted">${label} &middot; Fill ${percentAvg(bin.compartments)}%</div>
        <div class="small text-muted">Lat ${Number(bin.gps.lat).toFixed(4)}, Lng ${Number(bin.gps.lng).toFixed(4)}</div>
      `);
      markerPins.push(marker);
    });

    const geometry = await buildRouteGeometry(route);
    if (geometry.length >= 2){
      routePolyline = L.polyline(geometry, {
        color: '#198754',
        weight: 5,
        opacity: 0.85
      }).addTo(map);
    }

    const bounds = L.latLngBounds([
      ...geometry,
      ...markerPins.map(marker => marker.getLatLng?.()).filter(Boolean)
    ]);
    if (bounds.isValid()){
      map.fitBounds(bounds.pad(0.2));
    }else if (nextCenter){
      map.setView([nextCenter.lat, nextCenter.lng], 14);
    }
  }

  async function drawMap(markerData, route){
    try{
      await updateMap(markerData, route);
    }catch(err){
      console.error('Map update failed', err);
    }
  }

  async function buildRouteGeometry(route){
    const points = route.filter(stop => hasCoords(stop));
    if (points.length < 2){
      return points.map(stop => [stop.gps.lat, stop.gps.lng]);
    }
    if (BASI_API_KEY){
      try{
        const coordinates = points.map(stop => [Number(stop.gps.lng), Number(stop.gps.lat)]);
        const response = await fetch(ROUTING_URL, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': BASI_API_KEY
          },
          body: JSON.stringify({ coordinates })
        });
        if (!response.ok){
          throw new Error(`Routing failed with status ${response.status}`);
        }
        const data = await response.json();
        const geometry = data?.features?.[0]?.geometry?.coordinates;
        if (Array.isArray(geometry)){
          return geometry.map(([lng, lat]) => [lat, lng]);
        }
      }catch(err){
        console.warn('Routing API (BASI) unavailable, trying OSRM fallback.', err);
      }
    }
    try{
      const coordString = points.map(stop => `${Number(stop.gps.lng)},${Number(stop.gps.lat)}`).join(';');
      const url = `https://router.project-osrm.org/route/v1/driving/${coordString}?overview=full&geometries=geojson`;
      const res = await fetch(url);
      if (res.ok){
        const data = await res.json();
        const coords = data?.routes?.[0]?.geometry?.coordinates;
        if (Array.isArray(coords)){
          return coords.map(([lng, lat]) => [lat, lng]);
        }
      }
    }catch(err){
      console.warn('OSRM routing unavailable, falling back to straight segments.', err);
    }
    return points.map(stop => [stop.gps.lat, stop.gps.lng]);
  }

  function haversine(a, b){
    const R = 6371;
    const dLat = toRad(b.lat - a.lat);
    const dLng = toRad(b.lng - a.lng);
    const lat1 = toRad(a.lat);
    const lat2 = toRad(b.lat);

    const h = Math.sin(dLat / 2) ** 2 +
      Math.sin(dLng / 2) ** 2 * Math.cos(lat1) * Math.cos(lat2);
    return 2 * R * Math.asin(Math.sqrt(Math.max(0, h)));
  }

  function toRad(value){
    return value * Math.PI / 180;
  }

  function formatDuration(minutes){
    if (!minutes) return '0 min';
    const totalMinutes = Math.round(minutes);
    const hours = Math.floor(totalMinutes / 60);
    const mins = totalMinutes % 60;
    if (hours && mins){
      return `${hours}h ${mins}m`;
    }
    if (hours){
      return `${hours}h`;
    }
    return `${mins} min`;
  }

  function formatCoords(gps){
    if (!gps) return 'Coordinates unavailable';
    const lat = Number(gps.lat);
    const lng = Number(gps.lng);
    if (!Number.isFinite(lat) || !Number.isFinite(lng)){
      return 'Coordinates unavailable';
    }
    return `Lat ${lat.toFixed(4)}, Lng ${lng.toFixed(4)}`;
  }

  load();
</script>
