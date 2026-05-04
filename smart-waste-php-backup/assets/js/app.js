// /assets/js/app.js
(function(){
  const clock = document.getElementById('clock');
  const fmt = new Intl.DateTimeFormat('en-PH', {
    timeZone: 'Asia/Manila',
    hour12: true,
    month: 'short',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  });
  if (clock){
    const render = () => {
      clock.textContent = fmt.format(new Date());
    };
    render();
    setInterval(render, 1000);
  }
})();

const SAMPLE_RESET_STORAGE_KEY = 'ibin.sampleResets';
const sessionStore = getSessionStorage();
const navigationType = detectNavigationType();
if (sessionStore && navigationType === 'reload') {
  try {
    sessionStore.removeItem(SAMPLE_RESET_STORAGE_KEY);
  } catch (err) {}
}
const SAMPLE_RESET_IDS = loadStoredResetIds();
if (typeof window !== 'undefined') {
  window.IBIN_SAMPLE_RESET_STORAGE_KEY = SAMPLE_RESET_STORAGE_KEY;
  window.IBIN_SAMPLE_RESET_IDS = Array.from(SAMPLE_RESET_IDS);
}

export const HIMAMAYLAN_CENTER = Object.freeze({ lat: 10.0989, lng: 122.8707 });
const LANDFILL_SOURCE = typeof window !== 'undefined' ? window.IBIN_LANDFILL : null;
const LANDFILL_FALLBACK = { lat: 10.090419084954268, lng: 122.86834693751759 };
export const HIMAMAYLAN_LANDFILL = Object.freeze({
  lat: Number(LANDFILL_SOURCE?.lat) || LANDFILL_FALLBACK.lat,
  lng: Number(LANDFILL_SOURCE?.lng) || LANDFILL_FALLBACK.lng
});
const BASIC_KEY_RAW = (typeof window !== 'undefined' && (window.BASIC_API_KEY || window.BASI_API_KEY)) ? (window.BASIC_API_KEY || window.BASI_API_KEY) : '';
export const BASI_API_KEY = BASIC_KEY_RAW;

const STATIC_SAMPLE_BINS = [
  {
    bin_id: 'hm_city_hall',
    name: 'City Hall Compound',
    address: 'Ylagan St., Barangay Talaban',
    status: 'Online',
    fillBio: 48,
    fillRecyclable: 40,
    fillResidual: 52,
    battery: 91,
    lastPing: new Date(Date.now() - 90000).toISOString(),
    locationLat: 10.0932,
    locationLng: 122.8695,
    gps: { lat: 10.0932, lng: 122.8695 }
  },
  {
    bin_id: 'hm_public_plaza',
    name: 'Public Plaza',
    address: 'Burgos St., Poblacion 1',
    status: 'Online',
    fillBio: 56,
    fillRecyclable: 34,
    fillResidual: 60,
    battery: 47,
    lastPing: new Date(Date.now() - 180000).toISOString(),
    locationLat: 10.0989,
    locationLng: 122.8707,
    gps: { lat: 10.0989, lng: 122.8707 }
  }
];

function normalizeSampleBins(raw){
  if (!Array.isArray(raw)) return null;
  return raw.map((bin, index) => normalizeSampleBin(bin, index));
}

function normalizeSampleBin(bin, index = 0){
  const compartments = bin?.compartments ?? {
    biodegradable: Number(bin?.fill_bio ?? bin?.fillBio ?? bin?.bio ?? bin?.biodegradable ?? 0),
    recyclable: Number(bin?.fill_rec ?? bin?.fillRecyclable ?? bin?.rec ?? bin?.recyclable ?? 0),
    residual: Number(bin?.fill_res ?? bin?.fillResidual ?? bin?.res ?? bin?.residual ?? 0)
  };
  const gpsSource = bin?.gps ?? {
    lat: bin?.location_lat ?? bin?.locationLat,
    lng: bin?.location_lng ?? bin?.locationLng
  };
  const gps = normalizeSampleGps(gpsSource);
  return {
    ...bin,
    bin_id: bin?.bin_id ?? bin?.id ?? `bin_${index + 1}`,
    name: bin?.name ?? bin?.bin_id ?? `Bin ${index + 1}`,
    address: bin?.address ?? bin?.location ?? '',
    compartments,
    battery: Number(bin?.battery ?? bin?.battery_level ?? bin?.batteryLevel ?? 0),
    gps,
    updated_at: bin?.updated_at ?? bin?.updatedAt ?? bin?.last_updated ?? bin?.last_ping_at ?? bin?.lastPing ?? new Date().toISOString()
  };
}

function normalizeSampleGps(gps){
  if (!gps) return null;
  const lat = Number(gps.lat);
  const lng = Number(gps.lng);
  if (!Number.isFinite(lat) || !Number.isFinite(lng)){
    return null;
  }
  return { lat, lng };
}

const GLOBAL_SAMPLE_BINS = (typeof window !== 'undefined' && Array.isArray(window.IBIN_SAMPLE_BINS))
  ? normalizeSampleBins(window.IBIN_SAMPLE_BINS)
  : null;
const SAMPLE_SOURCE = (GLOBAL_SAMPLE_BINS && GLOBAL_SAMPLE_BINS.length) ? GLOBAL_SAMPLE_BINS : STATIC_SAMPLE_BINS;
export const SAMPLE_BINS = Object.freeze(applySampleResets(SAMPLE_SOURCE, { cloneAll: true }));

const TILE_SOURCES = [
  'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
  'https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png',
  'https://{s}.tile.openstreetmap.de/{z}/{x}/{y}.png'
];
let leafletPromise = null;

export async function apiGet(urls){
  const candidates = Array.isArray(urls) ? urls : [urls];
  for (const u of candidates){
    try{
      const r = await fetch(u, {headers:{'Accept':'application/json'}});
      if (r.ok){
        const ct = r.headers.get('content-type')||'';
        if (!ct.includes('application/json')) continue;
        const data = await r.json();
        if (data && Array.isArray(data?.bins) && shouldUseSampleAdjustments(data?.source)) {
          data.bins = applySampleResets(data.bins, { cloneAll: true });
        }
        if (data) return data;
      }
    }catch(e){}
  }
  return { source: 'sample', bins: SAMPLE_BINS };
}

export function statusChip(percent){
  if (percent >= 80) return '<span class="status-chip status-err">High</span>';
  if (percent >= 60) return '<span class="status-chip status-warn">Medium</span>';
  return '<span class="status-chip status-ok">Low</span>';
}

export function percentAvg(comp){
  const vals = Object.values(comp||{}).map(v=>Number(v||0));
  if (!vals.length) return 0;
  return Math.round(vals.reduce((a,b)=>a+b,0)/vals.length);
}

export async function makeMap(el, gps, opts = {}){
  const L = await loadLeaflet();
  const target = getMapElement(el);
  const center = normalizeCoords(gps);
  let map = target._leafletMap;
  if (!map){
    const zoom = opts.zoom ?? 14;
    map = L.map(target, opts.mapOptions || {}).setView([center.lat, center.lng], zoom);
    attachTileLayer(L, map, opts);
    target._leafletMap = map;
    // Ensure the map reflows correctly if the container was hidden or resized on init.
    map.whenReady(() => {
      setTimeout(() => map.invalidateSize(), 100);
    });
  }else if (center){
    const nextZoom = typeof opts.zoom === 'number' ? opts.zoom : map.getZoom();
    map.setView([center.lat, center.lng], nextZoom);
    setTimeout(() => map.invalidateSize(), 100);
  }
  // Reflow on window resize to avoid half-rendered tiles.
  if (!target._ibinResizeBound){
    target._ibinResizeBound = true;
    window.addEventListener('resize', () => {
      if (target._leafletMap){
        target._leafletMap.invalidateSize();
      }
    });
  }
  return { map, L };
}

function attachTileLayer(L, map, opts){
  const sources = Array.isArray(opts.tileSources) && opts.tileSources.length ? opts.tileSources : TILE_SOURCES;
  let index = 0;
  const attribution = opts.attribution || '&copy; OpenStreetMap contributors';
  const maxZoom = opts.maxZoom ?? 19;

  const addLayer = () => {
    const url = sources[index] || sources[0];
    const layer = L.tileLayer(url, { maxZoom, attribution, crossOrigin: true });
    layer.on('tileerror', () => {
      index = (index + 1) % sources.length;
      map.removeLayer(layer);
      addLayer();
    });
    layer.addTo(map);
  };

  addLayer();
}

function getMapElement(el){
  if (typeof el === 'string'){
    const node = document.getElementById(el);
    if (!node) throw new Error(`Map element "${el}" not found`);
    return node;
  }
  return el;
}

function normalizeCoords(gps){
  const lat = Number(gps?.lat ?? HIMAMAYLAN_CENTER.lat);
  const lng = Number(gps?.lng ?? HIMAMAYLAN_CENTER.lng);
  return { lat, lng };
}

function loadLeaflet(){
  if (window.L) return Promise.resolve(window.L);
  if (!leafletPromise){
    leafletPromise = new Promise((resolve, reject) => {
      const script = document.createElement('script');
      script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
      script.async = true;
      script.onload = () => {
        if (window.L){
          resolve(window.L);
        }else{
          reject(new Error('Leaflet failed to initialize.'));
        }
      };
      script.onerror = () => reject(new Error('Leaflet script failed to load.'));
      document.head.appendChild(script);
    });
  }
  return leafletPromise;
}

export function applySampleResets(bins, options = {}){
  const cloneAll = Boolean(options.cloneAll);
  if (!Array.isArray(bins)) {
    return [];
  }
  if (!SAMPLE_RESET_IDS.size && !cloneAll) {
    return bins;
  }
  return bins.map(bin => {
    const binId = bin?.bin_id || bin?.id || '';
    const shouldZero = binId && SAMPLE_RESET_IDS.has(binId);
    if (!shouldZero && !cloneAll) {
      return bin;
    }
    const copy = cloneBin(bin);
    if (shouldZero) {
      zeroBin(copy);
    }
    return copy;
  });
}

export function recordSampleReset(binId){
  if (!binId) return;
  if (SAMPLE_RESET_IDS.has(binId)) {
    broadcastSampleReset(binId);
    return;
  }
  SAMPLE_RESET_IDS.add(binId);
  persistSampleResets();
  syncSampleGlobals();
  broadcastSampleReset(binId);
}

export function getSampleResetIds(){
  return Array.from(SAMPLE_RESET_IDS);
}

export function clearSampleResets(){
  if (!SAMPLE_RESET_IDS.size) return;
  SAMPLE_RESET_IDS.clear();
  persistSampleResets();
  syncSampleGlobals();
  broadcastSampleReset();
}

function zeroBin(target){
  const compartments = { biodegradable: 0, recyclable: 0, residual: 0 };
  target.compartments = compartments;
  target.fill_bio = target.fillBio = 0;
  target.fill_rec = target.fillRecyclable = 0;
  target.fill_res = target.fillResidual = 0;
  return target;
}

function cloneBin(bin){
  if (!bin || typeof bin !== 'object') {
    return { compartments: { biodegradable: 0, recyclable: 0, residual: 0 } };
  }
  const copy = { ...bin };
  if (bin.compartments && typeof bin.compartments === 'object') {
    copy.compartments = { ...bin.compartments };
  } else {
    copy.compartments = { biodegradable: 0, recyclable: 0, residual: 0 };
  }
  if (bin.gps && typeof bin.gps === 'object') {
    copy.gps = { ...bin.gps };
  }
  return copy;
}

function getSessionStorage(){
  if (typeof window === 'undefined') return null;
  try {
    return window.sessionStorage;
  } catch (err) {
    return null;
  }
}

function detectNavigationType(){
  if (typeof performance === 'undefined') return 'navigate';
  if (typeof performance.getEntriesByType === 'function'){
    const entries = performance.getEntriesByType('navigation');
    if (entries && entries.length){
      return entries[0].type || 'navigate';
    }
  }
  if (performance.navigation){
    switch (performance.navigation.type){
      case 1: return 'reload';
      case 2: return 'back_forward';
      default: return 'navigate';
    }
  }
  return 'navigate';
}

function loadStoredResetIds(){
  if (!sessionStore) return new Set();
  try{
    const raw = sessionStore.getItem(SAMPLE_RESET_STORAGE_KEY);
    if (!raw) return new Set();
    const list = JSON.parse(raw);
    if (Array.isArray(list)){
      return new Set(list.map(id => String(id)));
    }
  }catch(err){}
  return new Set();
}

function persistSampleResets(){
  if (!sessionStore) return;
  try{
    sessionStore.setItem(SAMPLE_RESET_STORAGE_KEY, JSON.stringify(Array.from(SAMPLE_RESET_IDS)));
  }catch(err){}
}

function syncSampleGlobals(){
  if (typeof window === 'undefined') return;
  window.IBIN_SAMPLE_RESET_IDS = Array.from(SAMPLE_RESET_IDS);
  if (Array.isArray(window.IBIN_SAMPLE_BINS)){
    window.IBIN_SAMPLE_BINS = applySampleResets(window.IBIN_SAMPLE_BINS, { cloneAll: true });
  }
  if (window.IBIN_CLIENT_STATE && Array.isArray(window.IBIN_CLIENT_STATE.sample)){
    window.IBIN_CLIENT_STATE.sample = applySampleResets(window.IBIN_CLIENT_STATE.sample, { cloneAll: true });
  }
}

function broadcastSampleReset(binId){
  if (typeof window === 'undefined' || typeof window.dispatchEvent !== 'function') return;
  window.dispatchEvent(new CustomEvent('ibin:sample-resets-changed', {
    detail: {
      binId: binId || null,
      binIds: Array.from(SAMPLE_RESET_IDS)
    }
  }));
}

function shouldUseSampleAdjustments(source){
  return String(source || '').toLowerCase() !== 'firebase';
}

if (typeof window !== 'undefined'){
  syncSampleGlobals();
  window.IBIN_applySampleResets = (bins, options) => applySampleResets(bins, options);
  window.IBIN_recordSampleReset = (binId) => recordSampleReset(binId);
  window.IBIN_clearSampleResets = () => clearSampleResets();
}
