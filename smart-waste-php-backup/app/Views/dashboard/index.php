<?php
// app/Views/dashboard/index.php

function dashboard_activity_badge(string $status): array {
  switch ($status) {
    case 'warning':
      return ['label' => 'Close to Full', 'class' => 'badge bg-warning-subtle text-warning-emphasis'];
    case 'info':
      return ['label' => 'In Progress', 'class' => 'badge bg-info-subtle text-info-emphasis'];
    case 'ok':
    default:
      return ['label' => 'Completed', 'class' => 'badge bg-success-subtle text-success-emphasis'];
  }
}
?>
<style>
  .sample-bin-card {
    background: #ffffff;
    border: 1px solid #e5e8f0;
    border-radius: 14px;
    box-shadow: 0 10px 26px rgba(25, 35, 52, 0.08);
    transition: transform 0.16s ease, box-shadow 0.16s ease;
  }
  .sample-bin-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 32px rgba(25, 35, 52, 0.12);
  }
  .sample-bin-card .status-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 0.82rem;
    border: 1px solid transparent;
  }
  .sample-bin-card .status-pill.online {
    color: #0f9d58;
    background: #e8f5ed;
    border-color: #c6e6d2;
  }
  .sample-bin-card .status-pill.offline {
    color: #6c757d;
    background: #f1f3f5;
    border-color: #e0e5ea;
  }
  .sample-bin-card .status-pill .status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    background: currentColor;
  }
  .sample-bin-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
  }
  .sample-bin-header {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
  }
  .sample-bin-body {
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .sample-modal .status-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border-radius: 999px;
    font-size: 0.82rem;
    border: 1px solid transparent;
  }
  .sample-modal .status-pill.online {
    color: #0f9d58;
    background: #e8f5ed;
    border-color: #c6e6d2;
  }
  .sample-modal .status-pill.offline {
    color: #6c757d;
    background: #f1f3f5;
    border-color: #e0e5ea;
  }
  .sample-modal .status-pill .status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    background: currentColor;
  }
  .sample-modal-dialog {
    max-width: 740px;
  }
  .sample-modal .list-group-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .sample-modal .progress {
    height: 8px;
    background: #eef2f7;
  }
</style>
<div class="dashboard-hero card p-4 mb-4">
  <div class="row g-4 align-items-center">
    <div class="col-lg-8">
      <div class="d-flex gap-3 align-items-start">
        <div class="hero-icon bubble bg-success-subtle text-success">
          <i class="bi bi-recycle"></i>
        </div>
        <div>
          <p class="text-uppercase small text-secondary fw-semibold mb-1">Smart Waste Overview</p>
          <h4 class="mb-2">Welcome back, <?php echo htmlspecialchars($user_name); ?>!</h4>
          <p class="text-muted mb-0">Monitor fill levels, dispatch crews, and keep Himamaylan’s network running smoothly.</p>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="d-flex flex-wrap gap-2 justify-content-lg-end dashboard-chips">
        <span class="dashboard-chip">
          <i class="bi bi-hdd-network"></i>
          <?php echo $activeBins; ?> active bins
        </span>
        <span class="dashboard-chip">
          <i class="bi bi-geo-alt"></i>
          <?php echo htmlspecialchars($sampleNetworkRegion, ENT_QUOTES, 'UTF-8'); ?>
        </span>
      </div>
      <div class="dashboard-actions d-flex flex-wrap gap-2 mt-3 justify-content-lg-end">
        <a class="btn btn-success" href="collection">
          <i class="bi bi-signpost-split me-1"></i>
          Collection Routes
        </a>
        <a class="btn btn-outline-secondary" href="settings.php">
          <i class="bi bi-gear me-1"></i>
          Settings
        </a>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 dashboard-stats mb-4">
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="dashboard-stat-card">
      <div class="stat-icon tone-success">
        <i class="bi bi-hdd-stack"></i>
      </div>
      <div>
        <p class="stat-label">Active Bins</p>
        <div class="stat-value" id="statActiveBins"><?php echo $activeBins; ?></div>
        <p class="stat-note">Fully reporting devices</p>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="dashboard-stat-card">
      <div class="stat-icon tone-warning">
        <i class="bi bi-thermometer-high"></i>
      </div>
      <div>
        <p class="stat-label">Bins Near Full</p>
        <div class="stat-value text-warning" id="statBinsNearFull"><?php echo $binsNearFull; ?></div>
        <p class="stat-note">Triggering the 80% alert</p>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="dashboard-stat-card">
      <div class="stat-icon tone-info">
        <i class="bi bi-truck"></i>
      </div>
      <div>
        <p class="stat-label">Collections Today</p>
        <div class="stat-value" id="statCollections"><?php echo $collectionsToday; ?></div>
        <p class="stat-note">Planned dispatches</p>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="dashboard-stat-card">
      <div class="stat-icon tone-primary">
        <i class="bi bi-speedometer2"></i>
      </div>
      <div>
        <p class="stat-label">Average Fill</p>
        <div class="stat-value" id="statAvgFill"><?php echo $avgFillLevel; ?>%</div>
        <p class="stat-note">Across the entire fleet</p>
      </div>
    </div>
  </div>
</div>

<div class="card dashboard-panel p-4 mb-4">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
      <p class="text-uppercase small text-secondary mb-1">Network Map</p>
      <h5 class="mb-0"><?php echo htmlspecialchars($sampleNetworkRegion, ENT_QUOTES, 'UTF-8'); ?></h5>
      <p class="text-muted small mb-0">Sample placement for <?php echo $activeBins; ?> active bins.</p>
    </div>
    <span class="chart-pill live">
      <i class="bi bi-map"></i>
      OpenStreetMap
    </span>
  </div>
  <div class="rounded-4 overflow-hidden border border-light-subtle map-frame bg-white shadow-sm">
    <div id="dashboardMap" class="w-100 h-100" style="min-height: 400px;"></div>
  </div>
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mt-3">
    <p class="text-secondary small mb-0">Pins reflect latest telemetry averages from the demo dataset.</p>
    <div class="map-legend small text-secondary gap-2">
      <span class="legend-chip"><span class="map-dot" style="background:#22c55e"></span>Low (&lt;60%)</span>
      <span class="legend-chip"><span class="map-dot" style="background:#f59e0b"></span>Medium (60-79%)</span>
      <span class="legend-chip"><span class="map-dot" style="background:#ef4444"></span>High (80%+)</span>
      <span class="legend-chip"><span class="map-dot" style="background:#7c3aed"></span>Landfill</span>
    </div>
  </div>
</div>

<div class="row g-3 align-items-stretch mb-4">
  <div class="col-12 col-xl-4 d-flex">
    <div class="card dashboard-panel p-4 w-100 h-100">
      <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
          <p class="text-uppercase small text-secondary mb-1">Sample Bin Preview</p>
          <h6 class="mb-0">Mirrors the data powering this dashboard.</h6>
        </div>
        <span class="chart-pill tone-success">
          <i class="bi bi-eye"></i>
          Preview
        </span>
      </div>
      <div id="sampleBinList" class="sample-bin-list d-flex flex-column gap-3"></div>
    </div>
  </div>
  <div class="col-12 col-xl-4 d-flex">
    <div class="card chart-panel p-4 w-100 h-100">
      <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
          <p class="text-uppercase small text-secondary mb-1">Fill Trends</p>
          <h6 class="mb-0">Last 24 hours</h6>
        </div>
        <span class="chart-pill live">
          <i class="bi bi-activity"></i>
          Live
        </span>
      </div>
      <div class="chart-area position-relative" style="min-height:250px;">
        <canvas id="fillLevelChart" class="w-100 h-100"></canvas>
      </div>
      <div id="fillLevelSummary" class="mt-3 small text-secondary chart-summary"></div>
    </div>
  </div>
  <div class="col-12 col-xl-4 d-flex">
    <div class="card chart-panel p-4 w-100 h-100">
      <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
          <p class="text-uppercase small text-secondary mb-1">Bin Details Snapshot</p>
          <h6 class="mb-0">Compartment breakdown</h6>
        </div>
        <span class="chart-pill preview">
          <i class="bi bi-pie-chart"></i>
          Snapshot
        </span>
      </div>
      <div class="chart-area position-relative" style="min-height:250px;">
        <canvas id="binDetailsChart" class="w-100 h-100"></canvas>
      </div>
      <div id="binDetailsSummary" class="mt-3 small text-secondary chart-summary"></div>
    </div>
  </div>
</div>

<div class="card dashboard-panel p-4 mb-4">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
      <p class="text-uppercase small text-secondary mb-1">Recent Activity</p>
      <h6 class="mb-0">Latest sync and dispatch events</h6>
    </div>
    <span class="chart-pill tone-primary">
      <i class="bi bi-bell"></i>
      Feed
    </span>
  </div>
  <div class="table-responsive">
    <table class="table align-middle mb-0 dashboard-activity-table">
      <thead>
        <tr>
          <th>Time</th>
          <th>Bin</th>
          <th>Event</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($recentActivity): ?>
          <?php foreach ($recentActivity as $activity): ?>
            <?php
              $binId = $activity['bin_id'] ?? '';
              $bin = $binId && isset($sampleBinLookup[$binId]) ? $sampleBinLookup[$binId] : null;
              $binName = htmlspecialchars($bin['name'] ?? $binId ?: 'Bin', ENT_QUOTES, 'UTF-8');
              $event = htmlspecialchars($activity['event'] ?? 'Event', ENT_QUOTES, 'UTF-8');
              $time = htmlspecialchars($activity['time'] ?? '--:--', ENT_QUOTES, 'UTF-8');
              $badge = dashboard_activity_badge($activity['status'] ?? '');
            ?>
            <tr>
              <td><?php echo $time; ?></td>
              <td><?php echo $binName; ?></td>
              <td><?php echo $event; ?></td>
              <td><span class="<?php echo $badge['class']; ?>"><?php echo htmlspecialchars($badge['label'], ENT_QUOTES, 'UTF-8'); ?></span></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="4" class="text-center text-secondary py-4">No activity yet.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="sampleBinModal" tabindex="-1" aria-labelledby="sampleModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable sample-modal-dialog">
    <div class="modal-content rounded-4">
      <div class="modal-header border-0">
        <div>
          <h5 class="modal-title" id="sampleModalTitle">Bin Details</h5>
          <p class="text-secondary small mb-0" id="sampleModalMeta"></p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-0" id="sampleModalBody"></div>
      <div class="modal-footer border-0">
        <a class="btn btn-outline-success" id="sampleModalLink" href="#" target="_blank" rel="noopener">Open Full View</a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    const sampleData = <?php echo json_encode(
      $dashboardSampleSeed,
      JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES
    ); ?>;
    window.DASHBOARD_SAMPLE_DATA = sampleData;
    window.DASHBOARD_SAMPLE_BINS = sampleData && sampleData.network && Array.isArray(sampleData.network.bins)
      ? sampleData.network.bins
      : [];
  })();
</script>
<script type="module">
  import { makeMap, percentAvg, HIMAMAYLAN_LANDFILL, applySampleResets, getFillDataset, getBinSnapshotData, renderFillSummary, renderBinSnapshotSummary, formatTime } from './assets/js/app.js';
  const SAMPLE_DATA = window.DASHBOARD_SAMPLE_DATA || {};
  const NETWORK = SAMPLE_DATA.network || {};
  const sampleSeed = Array.isArray(NETWORK.bins)
    ? NETWORK.bins
    : [];
  let SAMPLE_BINS = applySampleResets(sampleSeed, { cloneAll: true });
  const STALE_MS = <?php echo (int) ($offlineWindowSeconds * 1000); ?>;
  function refreshDashboardSampleBins(){
    SAMPLE_BINS = applySampleResets(sampleSeed, { cloneAll: true });
    if (window.DASHBOARD_SAMPLE_DATA?.network){
      window.DASHBOARD_SAMPLE_DATA.network.bins = SAMPLE_BINS;
    }
  }
  refreshDashboardSampleBins();
  const FILL_HISTORY = SAMPLE_DATA.fill_history || {};
  const BIN_SNAPSHOT = SAMPLE_DATA.bin_snapshot || {};
  const NETWORK_REGION = NETWORK.region || 'Himamaylan City, Negros Occidental';
  const sampleList = document.getElementById('sampleBinList');
  const modalEl = document.getElementById('sampleBinModal');
  const modalTitle = document.getElementById('sampleModalTitle');
  const modalMeta = document.getElementById('sampleModalMeta');
  const modalBody = document.getElementById('sampleModalBody');
  const modalLink = document.getElementById('sampleModalLink');
  const sampleModal = modalEl ? new bootstrap.Modal(modalEl) : null;
  const fillSummary = document.getElementById('fillLevelSummary');
  const binSummary = document.getElementById('binDetailsSummary');
  const statActiveEl = document.getElementById('statActiveBins');
  const statNearFullEl = document.getElementById('statBinsNearFull');
  const statCollectionsEl = document.getElementById('statCollections');
  const statAvgFillEl = document.getElementById('statAvgFill');

  const ESCAPE_LOOKUP = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
  const escapeHTML = (value) => String(value ?? '').replace(/[&<>"']/g, char => ESCAPE_LOOKUP[char] || char);

  function readLastPingTs(bin){
    const value = bin?.last_ping || bin?.lastPing || bin?.last_ping_at || bin?.lastPingAt || bin?.last_updated || bin?.updated_at;
    const ts = value ? Date.parse(value) : NaN;
    return Number.isNaN(ts) ? null : ts;
  }

  function isBinOnline(bin, nowMs = Date.now()){
    const ts = readLastPingTs(bin);
    if (!ts) return false;
    return (nowMs - ts) <= STALE_MS;
  }

  function formatPingTime(value){
    if (!value) return 'No ping yet';
    const date = new Date(value);
    return Number.isNaN(date.getTime()) ? 'No ping yet' : date.toLocaleString();
  }

  document.addEventListener('DOMContentLoaded', () => {
    initMap();
    initCharts();
    renderSampleList();
    updateKeyStats();
  });
  window.addEventListener('ibin:sample-resets-changed', () => {
    refreshDashboardSampleBins();
    updateKeyStats();
    renderSampleList();
  });
  sampleList?.addEventListener('click', (event) => {
    const card = event.target.closest('[data-bin-id]');
    if (!card) return;
    const id = card.getAttribute('data-bin-id');
    const bin = SAMPLE_BINS.find(item => (item.bin_id || item.id) === id);
    if (bin) {
      openSampleModal(bin);
    }
  });
  setInterval(() => {
    renderSampleList();
    updateKeyStats();
  }, Math.min(STALE_MS, 30000));

  async function initMap(){
    const container = document.getElementById('dashboardMap');
    if (!container) return;
    if (!SAMPLE_BINS.length){
      container.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 text-secondary">No map data available</div>';
      return;
    }
    try{
      const ctx = await makeMap(container, SAMPLE_BINS[0]?.gps, { zoom: 14 });
      const { map, L } = ctx;
      const bounds = L.latLngBounds();
      const colors = { low: '#22c55e', medium: '#f59e0b', high: '#ef4444', landfill: '#7c3aed' };
      SAMPLE_BINS.forEach((bin, index) => {
        if (!bin?.gps) return;
        const avg = percentAvg(bin.compartments);
        const color = avg >= 80 ? colors.high : avg >= 60 ? colors.medium : colors.low;
        const latLng = [bin.gps.lat, bin.gps.lng];
        bounds.extend(latLng);
        const marker = L.circleMarker(latLng, {
          title: bin.name,
          radius: 11,
          color,
          fillColor: color,
          fillOpacity: 0.92,
          weight: 3,
          opacity: 0.9
        });
        marker.bindPopup(`
          <div class="fw-semibold mb-1">${bin.name}</div>
          <div class="small text-secondary">${bin.address}</div>
          <div class="small">Fill Avg: ${percentAvg(bin.compartments)}% &middot; Battery: ${bin.battery ?? '--'}%</div>
        `);
        marker.addTo(map).bindTooltip(String(index + 1), { permanent: true, direction: 'top', offset: [0, -14], className: 'map-pin-label small fw-semibold' });
        marker.on('mouseover', () => marker.openPopup());
        marker.on('mouseout', () => marker.closePopup());
      });
      if (HIMAMAYLAN_LANDFILL && Number.isFinite(Number(HIMAMAYLAN_LANDFILL.lat)) && Number.isFinite(Number(HIMAMAYLAN_LANDFILL.lng))) {
        const landfillLatLng = [Number(HIMAMAYLAN_LANDFILL.lat), Number(HIMAMAYLAN_LANDFILL.lng)];
        bounds.extend(landfillLatLng);
        const landfillMarker = L.circleMarker(landfillLatLng, {
          title: 'City Landfill',
          radius: 13,
          color: colors.landfill,
          fillColor: colors.landfill,
          fillOpacity: 0.95,
          weight: 4
        });
        landfillMarker.bindPopup(`
          <div class="fw-semibold mb-1">City Landfill</div>
          <div class="small text-secondary">Primary disposal site</div>
          <div class="small">Coordinates: ${landfillLatLng[0].toFixed(4)}, ${landfillLatLng[1].toFixed(4)}</div>
        `);
        landfillMarker.addTo(map).bindTooltip('Landfill', { permanent: true, direction: 'top', offset: [0, -16], className: 'map-pin-label small fw-semibold' });
      }
      if (bounds.isValid()){
        map.fitBounds(bounds.pad(0.15));
      }
    }catch(err){
      console.error('Dashboard map failed', err);
      container.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 text-secondary">Map failed to load</div>';
    }
  }

  function initCharts(){
    const fillData = getFillDataset ? getFillDataset() : {labels:[], dataset:[], thresholdValue:80};
    const snapshotData = getBinSnapshotData ? getBinSnapshotData() : {labels:[], values:[], colors:[]};
    if(renderFillSummary) renderFillSummary(fillData);
    if(renderBinSnapshotSummary) renderBinSnapshotSummary(snapshotData);
    if (!window.Chart) return;

    const fillCtx = document.getElementById('fillLevelChart');
    if (fillCtx) {
      const { labels, dataset, thresholdValue } = fillData;
      new Chart(fillCtx, {
        type: 'line',
        data: {
          labels,
          datasets: [
            {
              label: 'Average Fill %',
              data: dataset,
              tension: 0.35,
              borderColor: 'rgb(25, 135, 84)',
              backgroundColor: 'rgba(25, 135, 84, 0.15)',
              fill: true,
              pointRadius: 4,
              pointBackgroundColor: 'rgb(25, 135, 84)'
            },
            {
              label: 'Alert Threshold',
              data: new Array(labels.length).fill(thresholdValue),
              borderColor: 'rgba(220, 53, 69, 0.6)',
              borderDash: [4, 4],
              fill: false,
              pointRadius: 0
            }
          ]
        },
        options: {
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom'
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              suggestedMax: 100,
              ticks: {
                callback: value => value + '%'
              }
            }
          }
        }
      });
    }

    const binCtx = document.getElementById('binDetailsChart');
    if (binCtx && snapshotData.labels.length) {
      const { labels, values, colors } = snapshotData;
      new Chart(binCtx, {
        type: 'doughnut',
        data: {
          labels,
          datasets: [
            {
              data: values,
              backgroundColor: colors,
              borderWidth: 0
            }
          ]
        },
        options: {
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom'
            },
            tooltip: {
              callbacks: {
                label: context => `${context.label}: ${context.parsed}%`
              }
            }
          }
        }
      });
    }
  }

  function renderSampleList(){
    if (!sampleList) return;
    if (!SAMPLE_BINS.length){
      sampleList.innerHTML = '<div class="p-3 text-secondary text-center">Sample dataset missing.</div>';
      return;
    }
    const nowMs = Date.now();
    sampleList.innerHTML = SAMPLE_BINS.map(bin => {
      const avg = percentAvg(bin.compartments || {});
      const tone = avg >= 80 ? 'danger' : (avg >= 60 ? 'warning' : 'success');
      const iconClass = tone === 'danger'
        ? 'bi-exclamation-triangle-fill'
        : tone === 'warning'
          ? 'bi-recycle'
          : 'bi-trash3-fill';
      const lastPing = escapeHTML(formatPingTime(bin.last_ping || bin.lastPing || bin.last_ping_at || bin.lastPingAt || bin.last_updated || bin.updated_at));
      const lastUpdated = escapeHTML(formatTime(bin.updated_at || bin.updatedAt || bin.last_updated));
      const isOnline = isBinOnline(bin, nowMs);
      const statusClass = isOnline ? (bin.status === 'warning' ? 'text-warning' : 'text-secondary') : 'text-danger';
      const statusPill = `<span class="status-pill ${isOnline ? 'online' : 'offline'}"><span class="status-dot"></span>${isOnline ? 'Online' : 'Offline'}</span>`;
      return `
        <button type="button" class="sample-bin-card text-start" data-bin-id="${bin.bin_id}">
          <div class="d-flex justify-content-between align-items-start gap-3">
            <div class="d-flex align-items-center gap-3 sample-bin-header">
              <span class="sample-bin-icon tone-${tone}">
                <i class="bi ${iconClass}"></i>
              </span>
              <div>
                <div class="d-flex align-items-center gap-2">
                  <span class="fw-semibold">${bin.name}</span>
                  ${statusPill}
                </div>
                <div class="small text-secondary">${bin.address || NETWORK_REGION}</div>
                <div class="small text-muted">Controller ${bin.hardware?.controller_id || 'N/A'}</div>
                <div class="small text-muted">Last ping: ${lastPing}</div>
                <div class="small text-muted">Updated: ${lastUpdated}</div>
              </div>
            </div>
            <div class="sample-bin-meta text-end">
              <div class="sample-bin-value">${avg}%</div>
              <div class="small text-secondary">${bin.battery ?? '--'}% battery</div>
              <div class="small ${statusClass}">${isOnline ? 'ONLINE' : 'OFFLINE'} &middot; ${bin.connectivity || 'N/A'}</div>
            </div>
          </div>
        </button>`;
    }).join('');
  }

  function updateKeyStats(){
    if (!statActiveEl || !statNearFullEl || !statCollectionsEl || !statAvgFillEl) return;
    const nowMs = Date.now();
    const activeBins = SAMPLE_BINS.filter(bin => isBinOnline(bin, nowMs));
    const nearFull = activeBins.filter(bin => percentAvg(bin.compartments || {}) >= 80).length;
    const avgFill = activeBins.length
      ? Math.round(activeBins.reduce((sum, bin) => sum + percentAvg(bin.compartments || {}), 0) / activeBins.length)
      : 0;
    const collections = Math.min(activeBins.length, Math.max(1, nearFull + 1));
    statActiveEl.textContent = activeBins.length.toString();
    statNearFullEl.textContent = nearFull.toString();
    statCollectionsEl.textContent = collections.toString();
    statAvgFillEl.textContent = `${avgFill}%`;
  }

  function openSampleModal(bin){
    if (!sampleModal) return;
    const avg = percentAvg(bin.compartments);
    const updated = formatTime(bin.updated_at || bin.updatedAt || bin.last_updated);
    const lastPing = formatPingTime(bin.last_ping || bin.lastPing || bin.last_ping_at || bin.lastPingAt || bin.last_updated || bin.updated_at);
    const coords = bin.gps ? `${Number(bin.gps.lat).toFixed(4)}, ${Number(bin.gps.lng).toFixed(4)}` : 'Not available';
    const controllerId = bin.hardware?.controller_id || 'N/A';
    const metaLocation = bin.address || bin.barangay || NETWORK_REGION;
    const isOnline = isBinOnline(bin);
    const statusPill = `<span class="status-pill ${isOnline ? 'online' : 'offline'}"><span class="status-dot"></span>${isOnline ? 'Online' : 'Offline'}</span>`;
    modalTitle.textContent = bin.name || bin.bin_id || 'Bin';
    modalMeta.textContent = `${metaLocation} - Controller ${controllerId} - Updated ${updated} - Last ping ${lastPing}`;
    modalLink.href = `bin_view.php?id=${encodeURIComponent(bin.bin_id || bin.id || '')}`;
    modalBody.innerHTML = `
      <div class="sample-modal">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <div class="fw-semibold">Average Fill Level</div>
            <div class="display-6">${avg}%</div>
          </div>
          ${statusPill}
        </div>
        <div class="mb-3">
          <div class="text-secondary small mb-1">GPS Coordinates</div>
          <div class="fw-semibold">${coords}</div>
        </div>
      </div>
    `;
    sampleModal.show();
  }
</script>
