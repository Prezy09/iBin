<?php
require_once __DIR__ . '/includes/auth.php';
auth_require_login();
include_once __DIR__ . '/includes/layout.php';
$binId = $_GET['id'] ?? 'hm_city_hall';
render_header('Bin Details', ['active'=>'bins', 'brand'=>APP_BRAND_FULL]);
?>
<div class="row g-3">
  <div class="col-12 col-lg-7">
    <div class="card p-3">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <strong id="binTitle">Bin: <?php echo htmlspecialchars($binId); ?></strong>
        <span id="status"></span>
      </div>
      <div class="row g-3">
        <div class="col-12 col-md-6">
          <div class="small text-secondary">Biodegradable</div>
          <div class="progress"><div id="pBio" class="progress-bar bg-success" style="width:0%"></div></div>
        </div>
        <div class="col-12 col-md-6">
          <div class="small text-secondary">Recyclable</div>
          <div class="progress"><div id="pRec" class="progress-bar bg-info" style="width:0%"></div></div>
        </div>
        <div class="col-12 col-md-6">
          <div class="small text-secondary">Residual</div>
          <div class="progress"><div id="pRes" class="progress-bar bg-warning" style="width:0%"></div></div>
        </div>
        <div class="col-12 col-md-6">
          <div class="small text-secondary">Battery</div>
          <div id="battery" class="fw-bold">—%</div>
        </div>
      </div>
    </div>
    <div class="card p-3">
      <canvas id="historyChart" height="140"></canvas>
    </div>
  </div>
  <div class="col-12 col-lg-5">
    <div class="card p-0 overflow-hidden">
      <div id="map" style="height:380px;"></div>
    </div>
  </div>
</div>

<script type="module">
  import { apiGet, percentAvg, statusChip, makeMap, HIMAMAYLAN_CENTER } from './assets/js/app.js';
  const id = "<?php echo htmlspecialchars($binId); ?>";
  const el = {
    pBio: document.getElementById('pBio'),
    pRec: document.getElementById('pRec'),
    pRes: document.getElementById('pRes'),
    battery: document.getElementById('battery'),
    status: document.getElementById('status')
  };
  let chart;
  let mapCtx;
  let mapMarkers = [];

  function updateBars(c){
    el.pBio.style.width = (c?.biodegradable||0) + '%';
    el.pRec.style.width = (c?.recyclable||0) + '%';
    el.pRes.style.width = (c?.residual||0) + '%';
    const avg = percentAvg(c);
    el.status.innerHTML = statusChip(avg);
  }

  async function ensureMap(center){
    if (!mapCtx){
      mapCtx = await makeMap('map', center || HIMAMAYLAN_CENTER, { zoom: 15 });
    }
    return mapCtx;
  }

  async function renderMapMarkers(bins, activeBin){
    const center = activeBin?.gps || bins.find(b => b?.gps)?.gps || HIMAMAYLAN_CENTER;
    const ctx = await ensureMap(center);
    const { map, L } = ctx;
    mapMarkers.forEach(marker => marker.remove());
    mapMarkers = (bins || []).filter(b => b?.gps).map(bin => {
      const isActive = (bin.bin_id || bin.id) === (activeBin?.bin_id || activeBin?.id || id);
      const latLng = [bin.gps.lat, bin.gps.lng];
      const marker = L.circleMarker(latLng, {
        radius: isActive ? 9 : 6,
        color: isActive ? '#198754' : '#0d6efd',
        weight: isActive ? 3 : 2,
        fillColor: isActive ? '#198754' : '#0d6efd',
        fillOpacity: 0.85
      }).addTo(map);
      marker.bindPopup(`
        <div class="fw-semibold mb-1">${bin.name || bin.bin_id}</div>
        <div class="small text-secondary">${bin.address || 'Collection point'}</div>
        <div class="small text-muted">Fill avg: ${percentAvg(bin.compartments)}%</div>
      `);
      return marker;
    });
    const bounds = L.latLngBounds(mapMarkers.map(marker => marker.getLatLng()));
    if (bounds.isValid()){
      map.fitBounds(bounds.pad(0.3));
    }else if (center){
      map.setView([center.lat, center.lng], 15);
    }
  }

  async function load(){
    const data = await apiGet(['api/bins.php', `api/v1/bins.php?id=${encodeURIComponent(id)}`]);
    const bins = data.bins ?? data ?? [];
    const bin = bins.find(b=> (b.bin_id||b.id) === id) || bins[0];
    try{
      await renderMapMarkers(bins, bin);
    }catch(err){
      console.error('Map render failed', err);
    }
    updateBars(bin?.compartments);
    el.battery.textContent = (bin?.battery ?? '—') + '%';

    // simple mock history
    const now = Date.now();
    const hist = Array.from({length:20}).map((_,i)=> ({
      ts: new Date(now - (19-i)*30000).toISOString(),
      compartments: {
        biodegradable: Math.max(0, Math.min(100, (bin?.compartments?.biodegradable||50) + Math.round((Math.random()-0.5)*10))),
        recyclable: Math.max(0, Math.min(100, (bin?.compartments?.recyclable||40) + Math.round((Math.random()-0.5)*10))),
        residual: Math.max(0, Math.min(100, (bin?.compartments?.residual||30) + Math.round((Math.random()-0.5)*10)))
      }
    }));

    if (chart) chart.destroy();
    chart = new Chart(document.getElementById('historyChart'), {
      type: 'line',
      data: {
        labels: hist.map(p=> new Date(p.ts).toLocaleTimeString()),
        datasets: [
          {label:'Bio', data: hist.map(p=> p.compartments.biodegradable)},
          {label:'Rec', data: hist.map(p=> p.compartments.recyclable)},
          {label:'Res', data: hist.map(p=> p.compartments.residual)}
        ]
      },
      options: { responsive:true, maintainAspectRatio:false }
    });
  }
  load();
  setInterval(load, 7000);
</script>
<?php render_footer(); ?>
