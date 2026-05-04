<?php
// app/Views/analytics/index.php
?>
<div class="analytics-screen">
  <div class="analytics-hero card p-4">
    <div class="row g-4 align-items-center">
      <div class="col-lg-8">
        <div class="d-flex gap-3 align-items-start">
          <div class="hero-icon bubble bg-primary-subtle text-primary">
            <i class="bi bi-activity"></i>
          </div>
          <div>
            <p class="text-uppercase small text-secondary fw-semibold mb-1">Telemetry &amp; Insights</p>
            <h4 class="mb-2">Monitor fill trends across Himamaylan</h4>
            <p class="text-muted mb-0">Live compartment data refreshes automatically so dispatchers can prioritize the next route.</p>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
          <span class="analytics-chip">
            <i class="bi bi-clock-history"></i>
            Refreshing every 8s
          </span>
          <span class="analytics-chip">
            <i class="bi bi-shield-lock"></i>
            Admin visibility
          </span>
        </div>
      </div>
    </div>
  </div>

  <div class="analytics-stats row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3">
    <div class="col">
      <div class="card insight-card p-4 h-100">
        <div class="d-flex gap-3 align-items-start">
          <div class="insight-icon tone-primary">
            <i class="bi bi-hdd-network"></i>
          </div>
          <div>
            <p class="text-uppercase small text-secondary mb-1">Active Bins</p>
            <div class="insight-value" id="statTotal">--</div>
            <div class="small text-muted" id="statStale">&nbsp;</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="card insight-card p-4 h-100">
        <div class="d-flex gap-3 align-items-start">
          <div class="insight-icon tone-danger">
            <i class="bi bi-exclamation-triangle"></i>
          </div>
          <div>
            <p class="text-uppercase small text-secondary mb-1">Collection Alerts (&ge;80%)</p>
            <div class="insight-value text-danger" id="statAlerts">--</div>
            <div class="small text-muted">Critical fills awaiting pickup</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="card insight-card p-4 h-100">
        <div class="d-flex gap-3 align-items-start">
          <div class="insight-icon tone-success">
            <i class="bi bi-recycle"></i>
          </div>
          <div>
            <p class="text-uppercase small text-secondary mb-1">Average Fill</p>
            <div class="insight-value" id="statFill">--</div>
            <div class="small text-muted">Network mean for all bins</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="card insight-card p-4 h-100">
        <div class="d-flex gap-3 align-items-start">
          <div class="insight-icon tone-info">
            <i class="bi bi-battery-charging"></i>
          </div>
          <div>
            <p class="text-uppercase small text-secondary mb-1">Average Battery</p>
            <div class="insight-value" id="statBattery">--</div>
            <div class="small text-muted" id="statUpdated">&nbsp;</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card p-4 analytics-card chart-panel">
    <div class="card-heading d-flex justify-content-between align-items-start mb-3">
      <div>
        <p class="text-uppercase text-secondary small fw-semibold mb-1">Fill levels</p>
        <h6 class="mb-0">Average by compartment</h6>
      </div>
      <span class="chart-pill preview">
        <i class="bi bi-graph-up-arrow"></i>
        Live
      </span>
    </div>
    <div class="chart-wrapper">
      <canvas id="avgChart"></canvas>
    </div>
  </div>

  <div class="card p-4 analytics-card chart-panel">
    <div class="card-heading d-flex justify-content-between align-items-start mb-3">
      <div>
        <p class="text-uppercase text-secondary small fw-semibold mb-1">Health check</p>
        <h6 class="mb-0">Battery levels by bin</h6>
      </div>
      <span class="chart-pill live">
        <i class="bi bi-broadcast"></i>
        Streaming
      </span>
    </div>
    <div class="chart-wrapper">
      <canvas id="batChart"></canvas>
    </div>
  </div>

  <div class="card p-4 analytics-card chart-panel">
    <div class="card-heading d-flex justify-content-between align-items-start mb-3">
      <div>
        <p class="text-uppercase text-secondary small fw-semibold mb-1">Collection priority</p>
        <h6 class="mb-0">Peak fill vs. sensor battery</h6>
      </div>
      <span class="chart-pill tone-danger">
        <i class="bi bi-lightning-charge"></i>
        Focus bins
      </span>
    </div>
    <div class="chart-wrapper">
      <canvas id="priorityChart"></canvas>
    </div>
  </div>

  <div class="card p-4 analytics-card chart-panel">
    <div class="card-heading d-flex justify-content-between align-items-start mb-3">
      <div>
        <p class="text-uppercase text-secondary small fw-semibold mb-1">Network load mix</p>
        <h6 class="mb-0">Healthy vs warning vs critical</h6>
      </div>
      <span class="chart-pill tone-success">
        <i class="bi bi-pie-chart"></i>
        Snapshot
      </span>
    </div>
    <div class="chart-wrapper">
      <canvas id="statusChart"></canvas>
    </div>
  </div>

  <div class="card p-4 analytics-card analytics-table">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
      <div>
        <p class="text-uppercase text-secondary small fw-semibold mb-1">Highest priority bins</p>
        <h6 class="mb-0">Top fill levels vs sensor freshness</h6>
      </div>
      <span class="chart-pill">
        <i class="bi bi-list-check"></i>
        Top 5
      </span>
    </div>
    <div class="table-responsive mt-4">
      <table class="table table-sm align-middle mb-0 analytics-priority-table">
        <thead>
          <tr>
            <th>Bin</th>
            <th>Avg Fill</th>
            <th>Peak Fill</th>
            <th>Battery</th>
            <th>Last Update</th>
          </tr>
        </thead>
        <tbody id="priorityTable">
          <tr>
            <td colspan="5" class="text-center text-secondary py-4">Waiting for sensor data...</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script type="module">
  import { apiGet, percentAvg } from './assets/js/app.js';
  const statEls = {
    total: document.getElementById('statTotal'),
    alerts: document.getElementById('statAlerts'),
    fill: document.getElementById('statFill'),
    battery: document.getElementById('statBattery'),
    stale: document.getElementById('statStale'),
    updated: document.getElementById('statUpdated'),
    table: document.getElementById('priorityTable')
  };
  let avgC, batC, priorityC, loadC;
  const ALERT_THRESHOLD = 80;

  const minutesSince = (value) => {
    if (!value) return Infinity;
    const ts = new Date(value).getTime();
    return Number.isFinite(ts) ? Math.max(0, Math.round((Date.now() - ts) / 60000)) : Infinity;
  };

  const formatAge = (mins) => {
    if (!Number.isFinite(mins)) return 'no signal';
    if (mins < 1) return 'just now';
    if (mins < 60) return `${mins}m ago`;
    const hours = Math.floor(mins / 60);
    const minutes = mins % 60;
    return minutes ? `${hours}h ${minutes}m ago` : `${hours}h ago`;
  };

  function normalizeBins(raw) {
    return raw.map((bin, index) => {
      const comp = bin.compartments ?? {
        biodegradable: Number(bin.fill_bio ?? bin.bio ?? bin.biodegradable ?? 0),
        recyclable: Number(bin.fill_rec ?? bin.rec ?? bin.recyclable ?? 0),
        residual: Number(bin.fill_res ?? bin.res ?? bin.residual ?? 0)
      };
      const updated = bin.updatedAt ?? bin.updated_at ?? bin.last_updated ?? null;
      const compVals = Object.values(comp).map(v => Number(v) || 0);
      const maxFill = compVals.length ? Math.max(...compVals) : 0;
      const avgFill = percentAvg(comp);
      return {
        ...bin,
        bin_id: bin.bin_id ?? bin.id ?? `bin_${index + 1}`,
        name: bin.name ?? bin.bin_id ?? `Bin ${index + 1}`,
        battery: Number(bin.battery ?? bin.battery_level ?? bin.batteryLevel ?? 0),
        compartments: comp,
        updatedAt: updated,
        minutesSinceUpdate: minutesSince(updated),
        avgFill,
        maxFill
      };
    });
  }

  function updateStats(bins) {
    const total = bins.length;
    if (!total) {
      statEls.total.textContent = '0';
      statEls.alerts.textContent = '0';
      statEls.fill.textContent = '--';
      statEls.battery.textContent = '--';
      statEls.stale.textContent = 'No telemetry yet';
      statEls.updated.textContent = '';
      return;
    }
    const alerts = bins.filter(b => b.maxFill >= ALERT_THRESHOLD).length;
    const avgFill = Math.round(bins.reduce((sum, b) => sum + b.avgFill, 0) / total);
    const avgBattery = Math.round(bins.reduce((sum, b) => sum + (b.battery || 0), 0) / total);
    const stale = bins.filter(b => b.minutesSinceUpdate > 30).length;
    statEls.total.textContent = total;
    statEls.alerts.textContent = alerts;
    statEls.fill.textContent = `${avgFill}%`;
    statEls.battery.textContent = `${avgBattery}%`;
    statEls.stale.textContent = stale ? `${stale} stale sensor${stale > 1 ? 's' : ''}` : 'All reporting';
    statEls.updated.textContent = `Updated ${new Date().toLocaleTimeString()}`;
  }

  function renderTable(bins) {
    if (!statEls.table) return;
    if (!bins.length) {
      statEls.table.innerHTML = '<tr><td colspan="5" class="text-center text-secondary">Waiting for sensor data...</td></tr>';
      return;
    }
    const top = [...bins].sort((a, b) => b.maxFill - a.maxFill).slice(0, 5);
    statEls.table.innerHTML = top.map(b => `
      <tr>
        <td>${b.name || b.bin_id}</td>
        <td>${b.avgFill}%</td>
        <td>${b.maxFill}%</td>
        <td>${b.battery || 0}%</td>
        <td>${formatAge(b.minutesSinceUpdate)}</td>
      </tr>`).join('');
  }

  function destroyCharts() {
    [avgC, batC, priorityC, loadC].forEach(ch => { if (ch) ch.destroy(); });
    avgC = batC = priorityC = loadC = null;
  }

  function renderCharts(bins) {
    if (!bins.length) {
      destroyCharts();
      return;
    }
    const totals = bins.reduce((acc, b) => {
      acc.bio += b.compartments?.biodegradable || 0;
      acc.rec += b.compartments?.recyclable || 0;
      acc.res += b.compartments?.residual || 0;
      return acc;
    }, { bio: 0, rec: 0, res: 0 });
    const divisor = bins.length || 1;
    const bio = Math.round(totals.bio / divisor);
    const rec = Math.round(totals.rec / divisor);
    const res = Math.round(totals.res / divisor);
    const labels = bins.map(b => b.name || b.bin_id);
    if (avgC) avgC.destroy();
    avgC = new Chart(document.getElementById('avgChart'), {
      type: 'bar',
      data: {
        labels: ['Biodegradable', 'Recyclable', 'Residual'],
        datasets: [{
          label: 'Average %',
          backgroundColor: ['#198754', '#0dcaf0', '#ffc107'],
          data: [bio, rec, res]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: { beginAtZero: true, suggestedMax: 100 }
        }
      }
    });

    if (batC) batC.destroy();
    batC = new Chart(document.getElementById('batChart'), {
      type: 'line',
      data: {
        labels,
        datasets: [{
          label: 'Battery %',
          data: bins.map(b => b.battery || 0),
          tension: 0.35,
          borderColor: '#0d6efd',
          backgroundColor: 'rgba(13,110,253,0.15)',
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true, suggestedMax: 100 } }
      }
    });

    const priority = [...bins].sort((a, b) => b.maxFill - a.maxFill).slice(0, 6);
    if (priorityC) priorityC.destroy();
    priorityC = new Chart(document.getElementById('priorityChart'), {
      data: {
        labels: priority.map(b => b.name || b.bin_id),
        datasets: [
          {
            type: 'bar',
            label: 'Peak Fill %',
            data: priority.map(b => b.maxFill),
            backgroundColor: 'rgba(220,53,69,0.75)'
          },
          {
            type: 'line',
            label: 'Battery %',
            data: priority.map(b => b.battery || 0),
            borderColor: '#0d6efd',
            tension: 0.3,
            borderWidth: 2,
            fill: false,
            yAxisID: 'y1'
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: { beginAtZero: true, suggestedMax: 100 },
          y1: {
            beginAtZero: true,
            suggestedMax: 100,
            position: 'right',
            grid: { drawOnChartArea: false }
          }
        },
        plugins: { legend: { position: 'bottom' } }
      }
    });

    const tiers = bins.reduce((acc, b) => {
      if (b.avgFill >= ALERT_THRESHOLD) acc.critical += 1;
      else if (b.avgFill >= 60) acc.warning += 1;
      else acc.healthy += 1;
      return acc;
    }, { healthy: 0, warning: 0, critical: 0 });
    if (loadC) loadC.destroy();
    loadC = new Chart(document.getElementById('statusChart'), {
      type: 'doughnut',
      data: {
        labels: ['Healthy (<60%)', 'Warning (60-79%)', 'Critical (80% +)'],
        datasets: [{
          data: [tiers.healthy, tiers.warning, tiers.critical],
          backgroundColor: ['#198754', '#ffc107', '#dc3545'],
          borderWidth: 0
        }]
      },
      options: {
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
      }
    });
  }

  async function load() {
    const data = await apiGet(['api/bins.php', 'api/v1/bins.php']);
    const binsRaw = data.bins ?? data ?? [];
    const bins = normalizeBins(binsRaw);
    updateStats(bins);
    renderTable(bins);
    renderCharts(bins);
  }

  load();
  setInterval(load, 8000);
</script>
