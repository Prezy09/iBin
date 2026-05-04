<?php
require_once __DIR__ . '/includes/auth.php';
auth_require_role(['Admin']);
include_once __DIR__ . '/includes/layout.php';
render_header('Reports', ['active' => 'reports', 'brand' => APP_BRAND_FULL]);
?>
<div class="dashboard-hero card p-4 mb-4">
  <div class="row g-4 align-items-center">
    <div class="col-lg-8">
      <div class="d-flex gap-3 align-items-start">
        <div class="hero-icon bubble bg-primary-subtle text-primary">
          <i class="bi bi-clipboard-data"></i>
        </div>
        <div>
          <p class="text-uppercase small text-secondary fw-semibold mb-1">Reports & telemetry</p>
          <h4 class="mb-2">Analyze fill, battery, and alert trends.</h4>
          <p class="text-muted mb-0">Choose a time window, monitor summary stats, and export CSV snapshots for LGU records.</p>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="dashboard-chips d-flex flex-wrap gap-2 justify-content-lg-end">
        <span class="dashboard-chip" id="rangeChipSummary">Live window</span>
        <span class="dashboard-chip text-primary">
          <i class="bi bi-file-earmark-arrow-down"></i>
          CSV Ready
        </span>
      </div>
    </div>
  </div>
</div>
<div class="dashboard-panel p-4 mb-4 reports-filter">
  <div class="d-flex flex-wrap align-items-center gap-3 justify-content-between">
    <div class="d-flex flex-wrap align-items-center gap-3">
      <div>
        <div class="small text-secondary mb-1">Report window</div>
        <div class="dropdown">
          <button class="btn btn-outline-success dropdown-toggle" type="button" id="rangeToggle" data-bs-toggle="dropdown" aria-expanded="false">
            Last 7 days
          </button>
          <ul class="dropdown-menu">
            <li><button class="dropdown-item" data-range="week">Last 7 days</button></li>
            <li><button class="dropdown-item" data-range="month">Last 30 days</button></li>
            <li><button class="dropdown-item" data-range="year">Last 365 days</button></li>
          </ul>
        </div>
      </div>
      <div>
        <div class="small text-secondary mb-1">Coverage</div>
        <div id="rangeLabel" class="fw-semibold">--</div>
      </div>
    </div>
    <div class="d-flex align-items-center gap-2">
      <span class="badge text-bg-secondary" id="dataSource">Detecting source...</span>
      <button type="button" class="btn btn-success" id="exportCsv">
        <i class="bi bi-download me-1"></i>Export CSV
      </button>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-12 col-sm-6 col-lg-3">
    <div class="dashboard-stat-card h-100">
      <div class="stat-icon tone-primary">
        <i class="bi bi-collection"></i>
      </div>
      <div>
        <p class="stat-label">Records</p>
        <div class="stat-value" id="statRecords">--</div>
        <p class="stat-note mb-0">Bins: <span class="badge bg-light text-secondary" id="statBins">--</span></p>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-lg-3">
    <div class="dashboard-stat-card h-100">
      <div class="stat-icon tone-success">
        <i class="bi bi-recycle"></i>
      </div>
      <div>
        <p class="stat-label">Average Fill</p>
        <div class="stat-value" id="statFill">--</div>
        <p class="stat-note mb-0">Across the selected window</p>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-lg-3">
    <div class="dashboard-stat-card h-100">
      <div class="stat-icon tone-warning">
        <i class="bi bi-exclamation-triangle"></i>
      </div>
      <div>
        <p class="stat-label">High-Fill Events (&ge;80%)</p>
        <div class="stat-value" id="statHigh">--</div>
        <p class="stat-note mb-0">Trigger collection readiness</p>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-lg-3">
    <div class="dashboard-stat-card h-100">
      <div class="stat-icon tone-info">
        <i class="bi bi-battery-half"></i>
      </div>
      <div>
        <p class="stat-label">Average Battery</p>
        <div class="stat-value" id="statBattery">--</div>
        <p class="stat-note mb-0">Sensor health across bins</p>
      </div>
    </div>
  </div>
</div>

<div class="dashboard-panel p-4">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
      <h6 class="mb-0">Bin performance rollup</h6>
      <div class="small text-secondary" id="tableHint">Loading report data...</div>
    </div>
    <span class="chart-pill tone-success" id="rangeChip">Last 7 days</span>
  </div>
  <div class="table-responsive mt-3">
    <table class="table align-middle report-table">
      <thead>
        <tr>
          <th>Bin</th>
          <th>Records</th>
          <th>Avg Fill</th>
          <th>Peak Fill</th>
          <th>Avg Battery</th>
          <th>Last Report</th>
        </tr>
      </thead>
      <tbody id="rows">
        <tr>
          <td colspan="6" class="text-center text-secondary">Preparing reports...</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<script type="module">
  import { SAMPLE_BINS, percentAvg } from './assets/js/app.js';

  const PERIODS = {
    week: { label: 'Last 7 days', days: 7 },
    month: { label: 'Last 30 days', days: 30 },
    year: { label: 'Last 365 days', days: 365 }
  };

  const el = {
    rows: document.getElementById('rows'),
    statRecords: document.getElementById('statRecords'),
    statBins: document.getElementById('statBins'),
    statFill: document.getElementById('statFill'),
    statHigh: document.getElementById('statHigh'),
    statBattery: document.getElementById('statBattery'),
    rangeLabel: document.getElementById('rangeLabel'),
    rangeChip: document.getElementById('rangeChip'),
    heroChip: document.getElementById('rangeChipSummary'),
    tableHint: document.getElementById('tableHint'),
    exportBtn: document.getElementById('exportCsv'),
    rangeToggle: document.getElementById('rangeToggle'),
    dataSource: document.getElementById('dataSource')
  };

  const state = { range: 'week', raw: [], filtered: [] };
  const fmtDate = new Intl.DateTimeFormat('en', { month: 'short', day: 'numeric' });
  const fmtDateTime = new Intl.DateTimeFormat('en', { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });

  const clamp = (v) => Math.max(0, Math.min(100, Math.round(Number(v) || 0)));
  const rand = (min, max) => Math.random() * (max - min) + min;

  async function fetchHistory() {
    const endpoints = ['api/v1/history.php', 'api/history.php'];
    for (const url of endpoints) {
      try {
        const r = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!r.ok) continue;
        const data = await r.json();
        const hist = Array.isArray(data) ? data : (data.history || data.data || data.items || []);
        const normalized = normalizeHistory(hist);
        if (normalized.length) {
          el.dataSource.textContent = `Live API (${url})`;
          el.dataSource.className = 'badge text-bg-success';
          return normalized;
        }
      } catch (err) {}
    }
    const fallback = normalizeHistory(makeFallbackHistory());
    el.dataSource.textContent = 'Sample telemetry';
    el.dataSource.className = 'badge text-bg-secondary';
    return fallback;
  }

  function normalizeHistory(raw) {
    const fallbackIds = SAMPLE_BINS.map(bin => bin.bin_id);
    return (raw || []).map((row, idx) => {
      const tsValue = row.ts ?? row.timestamp ?? row.created_at ?? row.time ?? row.date;
      const tsMs = Date.parse(tsValue);
      if (!Number.isFinite(tsMs)) return null;
      const binId = row.bin_id ?? row.bin ?? row.id ?? fallbackIds[idx % (fallbackIds.length || 1)] ?? `bin_${idx + 1}`;
      const compRaw = row.compartments ?? {
        biodegradable: row.fill_bio ?? row.bio ?? row.biodegradable ?? 0,
        recyclable: row.fill_rec ?? row.rec ?? row.recyclable ?? 0,
        residual: row.fill_res ?? row.res ?? row.residual ?? 0
      };
      const compartments = {
        biodegradable: clamp(compRaw.biodegradable),
        recyclable: clamp(compRaw.recyclable),
        residual: clamp(compRaw.residual)
      };
      const peak = Math.max(compartments.biodegradable, compartments.recyclable, compartments.residual);
      const avgFill = percentAvg(compartments);
      return {
        ts: new Date(tsMs).toISOString(),
        tsMs,
        bin_id: binId,
        compartments,
        peak,
        avgFill,
        battery: clamp(row.battery ?? row.battery_level ?? row.batteryLevel ?? 0)
      };
    }).filter(Boolean).sort((a, b) => b.tsMs - a.tsMs);
  }

  function makeFallbackHistory() {
    const ids = (SAMPLE_BINS.map(bin => bin.bin_id) || ['hm_city_hall', 'hm_public_plaza']);
    const now = Date.now();
    const days = 365;
    const rows = [];
    ids.forEach((id, idx) => {
      let battery = 92 - idx * 4;
      for (let d = 0; d < days; d++) {
        const tsMs = now - d * 86400000 - idx * 3600000;
        const seasonal = Math.sin((d / 18) + idx) * 12;
        const base = 42 + idx * 6;
        const trend = (d % 14) * 0.4;
        const compartments = {
          biodegradable: clamp(base + seasonal + trend + rand(-6, 8)),
          recyclable: clamp(base - 6 + seasonal * 0.6 + rand(-5, 7)),
          residual: clamp(base - 2 + seasonal * 0.8 + rand(-4, 8))
        };
        battery = clamp(battery - 0.15 + rand(-0.6, 0.3));
        rows.push({
          ts: new Date(tsMs).toISOString(),
          bin_id: id,
          compartments,
          battery
        });
      }
    });
    return rows;
  }

  function applyRange(rangeKey) {
    state.range = PERIODS[rangeKey] ? rangeKey : 'week';
    const days = PERIODS[state.range].days;
    const cutoff = Date.now() - days * 86400000;
    state.filtered = state.raw.filter(r => r.tsMs >= cutoff);
    setRangeLabels();
    renderSummary();
    renderTable();
  }

  function setRangeLabels() {
    const period = PERIODS[state.range];
    if (el.rangeToggle) el.rangeToggle.textContent = period.label;
    if (el.rangeChip) el.rangeChip.textContent = period.label;
    if (el.heroChip) el.heroChip.textContent = period.label;
    const earliest = state.filtered[state.filtered.length - 1]?.tsMs;
    const latest = state.filtered[0]?.tsMs;
    if (earliest && latest) {
      el.rangeLabel.textContent = `${fmtDate.format(earliest)} - ${fmtDate.format(latest)}`;
    } else {
      el.rangeLabel.textContent = 'No data yet';
    }
    document.querySelectorAll('[data-range]').forEach(btn => {
      btn.classList.toggle('active', btn.dataset.range === state.range);
    });
  }

  function renderSummary() {
    const rows = state.filtered;
    const records = rows.length;
    const bins = new Set(rows.map(r => r.bin_id)).size;
    const avgFill = records ? Math.round(rows.reduce((sum, r) => sum + r.avgFill, 0) / records) : 0;
    const highEvents = rows.filter(r => r.peak >= 80).length;
    const avgBattery = records ? Math.round(rows.reduce((sum, r) => sum + r.battery, 0) / records) : 0;
    el.statRecords.textContent = records.toLocaleString();
    el.statBins.textContent = `${bins || 0} bins`;
    el.statFill.textContent = records ? `${avgFill}%` : '--';
    el.statHigh.textContent = records ? `${highEvents}` : '--';
    el.statBattery.textContent = records ? `${avgBattery}%` : '--';
  }

  function aggregateByBin(rows) {
    const acc = new Map();
    rows.forEach(row => {
      const binId = row.bin_id;
      const current = acc.get(binId) || { bin_id: binId, count: 0, fillSum: 0, peak: 0, batterySum: 0, last: 0 };
      current.count += 1;
      current.fillSum += row.avgFill;
      current.peak = Math.max(current.peak, row.peak);
      current.batterySum += row.battery;
      current.last = Math.max(current.last, row.tsMs);
      acc.set(binId, current);
    });
    return Array.from(acc.values()).map(item => ({
      ...item,
      avgFill: Math.round(item.fillSum / item.count),
      avgBattery: Math.round(item.batterySum / item.count)
    })).sort((a, b) => b.avgFill - a.avgFill);
  }

  function renderTable() {
    const rows = state.filtered;
    const perBin = aggregateByBin(rows);
    if (!perBin.length) {
      el.rows.innerHTML = '<tr><td colspan="6" class="text-center text-secondary">No report data available for this window.</td></tr>';
      el.tableHint.textContent = 'Switch to a different window to see rollups.';
      return;
    }
    el.tableHint.textContent = `Aggregated from ${rows.length.toLocaleString()} records across ${perBin.length} bins.`;
    el.rows.innerHTML = perBin.map(bin => `
      <tr>
        <td class="fw-semibold">${bin.bin_id}</td>
        <td>${bin.count.toLocaleString()}</td>
        <td>${bin.avgFill}%</td>
        <td>${bin.peak}%</td>
        <td>${bin.avgBattery}%</td>
        <td>${fmtDateTime.format(bin.last)}</td>
      </tr>
    `).join('');
  }

  function exportCsv() {
    if (!state.filtered.length) return;
    const header = ['timestamp','bin_id','bio','recyclable','residual','avg_fill','peak','battery'];
    const lines = state.filtered.map(row => [
      row.ts,
      row.bin_id,
      row.compartments.biodegradable,
      row.compartments.recyclable,
      row.compartments.residual,
      row.avgFill,
      row.peak,
      row.battery
    ].join(','));
    const csv = [header.join(','), ...lines].join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `reports-${state.range}-${new Date().toISOString().slice(0,10)}.csv`;
    link.click();
    URL.revokeObjectURL(url);
  }

  document.querySelectorAll('[data-range]').forEach(btn => {
    btn.addEventListener('click', ev => {
      ev.preventDefault();
      applyRange(btn.dataset.range);
    });
  });
  el.exportBtn?.addEventListener('click', exportCsv);

  (async function init(){
    state.raw = await fetchHistory();
    applyRange(state.range);
  })();
</script>
<?php render_footer(); ?>
