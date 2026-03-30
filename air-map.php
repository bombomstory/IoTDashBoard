<?php
/* ── 1. Load global config ───────────────────────── */
require_once '_vars.php';

/* ── 2. Page-specific options ────────────────────── */
$page_title = "IoT Dashboard - Air Quality Map";
$use_leaflet = true;

// CSS เฉพาะหน้า Air Map
$extra_css = '
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
  /* ── Layout ── */
  .map-container {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 16px;
    height: calc(100vh - 160px); /* ให้ความสูงพอดีกับหน้าจอ */
    min-height: 500px;
  }
  @media (max-width: 900px) {
    .map-container { grid-template-columns: 1fr; height: auto; }
  }

  /* ── Node List Panel ── */
  .node-list-panel {
    background: var(--bg-card); border: 1px solid var(--slate-200);
    border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);
    display: flex; flex-direction: column; overflow: hidden;
  }
  .nlp-header {
    padding: 16px; border-bottom: 1px solid var(--slate-100);
    background: var(--slate-50); font-family: var(--font-head);
    font-weight: 700; color: var(--blue-900); font-size: .9rem;
  }
  .nlp-body { flex: 1; overflow-y: auto; padding: 10px; }
  .nlp-body::-webkit-scrollbar { width: 4px; }
  .nlp-body::-webkit-scrollbar-thumb { background: var(--slate-300); border-radius: 2px; }
  
  .node-list-item {
    padding: 12px; border: 1px solid var(--slate-100); border-radius: var(--radius-md);
    margin-bottom: 8px; cursor: pointer; transition: all 0.2s;
    display: flex; align-items: center; gap: 12px;
  }
  .node-list-item:hover { background: var(--blue-50); border-color: var(--blue-200); transform: translateX(2px); }
  
  .nli-badge {
    width: 38px; height: 38px; border-radius: 10px; display: grid; place-items: center;
    font-family: var(--font-mono); font-weight: 700; color: white; font-size: .85rem; flex-shrink: 0;
  }
  .nli-info { flex: 1; }
  .nli-name { font-weight: 600; color: var(--slate-700); font-size: .85rem; margin-bottom: 2px; }
  .nli-loc { font-size: .7rem; color: var(--slate-400); font-family: var(--font-mono); }
  .nli-arrow { color: var(--slate-300); font-size: 1.2rem; }
  .node-list-item:hover .nli-arrow { color: var(--blue-500); }

  /* ── Map Panel ── */
  .map-panel {
    background: var(--bg-card); border: 1px solid var(--slate-200);
    border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);
    position: relative; overflow: hidden;
  }
  #airMap { width: 100%; height: 100%; z-index: 1; }

  /* ── Custom Leaflet Markers ── */
  .aqi-marker {
    width: 34px; height: 34px; border-radius: 50%;
    border: 2px solid white; display: grid; place-items: center;
    color: white; font-family: var(--font-mono); font-weight: 700; font-size: .8rem;
    box-shadow: 0 3px 8px rgba(0,0,0,0.3);
    transition: all 0.3s;
  }
  .aqi-marker-pulse {
    position: absolute; inset: -4px; border-radius: 50%;
    border: 2px solid inherit; opacity: 0;
    animation: pulse-ring 2s ease-out infinite;
  }
  @keyframes pulse-ring { 0% {transform: scale(0.8); opacity: 0.8;} 100% {transform: scale(2); opacity: 0;} }
  
  .leaflet-popup-content-wrapper { border-radius: 12px; box-shadow: var(--shadow-md); }
  .popup-custom { font-family: var(--font-ui); margin: 4px; }
  .pop-title { font-family: var(--font-head); font-weight: 700; color: var(--blue-900); font-size: .95rem; margin-bottom: 6px; border-bottom: 1px solid var(--slate-100); padding-bottom: 4px; }
  .pop-row { display: flex; justify-content: space-between; font-size: .8rem; margin-bottom: 4px; }
  .pop-lbl { color: var(--slate-500); }
  .pop-val { font-family: var(--font-mono); font-weight: 600; color: var(--slate-700); }

  /* ── Legend Overlay ── */
  .map-legend-overlay {
    position: absolute; bottom: 20px; right: 20px; z-index: 400;
    background: rgba(255,255,255,0.9); backdrop-filter: blur(4px);
    padding: 10px 14px; border-radius: var(--radius-md); border: 1px solid var(--slate-200);
    box-shadow: var(--shadow-md); font-size: .7rem;
  }
  .ml-row { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; }
  .ml-row:last-child { margin-bottom: 0; }
  .ml-color { width: 12px; height: 12px; border-radius: 3px; }
</style>
';

/* ── 3. Output <head> ────────────────────────────── */
require_once '_head.php';
?>
<body>

<?php
/* ── 4. Sidebar & Topbar ─────────────────────────── */
require_once '_sidebar.php';
require_once '_topbar.php';
?>

<main class="main">

  <div class="section-header">
    <h2>🗺️ Interactive Air Quality Map</h2>
    <div class="section-line"></div>
    <div class="section-meta">Nakhon Phanom / Kalasin Mesh Network</div>
  </div>

  <div class="map-container">
    
    <div class="node-list-panel">
      <div class="nlp-header">
        📍 Sensor Nodes (<span id="nodeCount">0</span>)
      </div>
      <div class="nlp-body" id="nodeListContainer">
        </div>
    </div>

    <div class="map-panel">
      <div id="airMap"></div>
      
      <div class="map-legend-overlay">
        <div style="font-weight:700; margin-bottom:6px; color:var(--slate-600); text-transform:uppercase; letter-spacing:.05em;">AQI Legend</div>
        <div class="ml-row"><div class="ml-color" style="background:var(--aqi-good)"></div>ดี (≤12)</div>
        <div class="ml-row"><div class="ml-color" style="background:var(--aqi-moderate)"></div>ปานกลาง (≤35.4)</div>
        <div class="ml-row"><div class="ml-color" style="background:var(--aqi-sensitive)"></div>กลุ่มเสี่ยง (≤55.4)</div>
        <div class="ml-row"><div class="ml-color" style="background:var(--aqi-unhealthy)"></div>ไม่ดี (≤150.4)</div>
        <div class="ml-row"><div class="ml-color" style="background:var(--aqi-very)"></div>อันตราย (>150.4)</div>
      </div>
    </div>

  </div>

<?php
/* ── 5. Footer + Shared JS ───────────────────────── */
require_once '_footer.php';
?>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// ════════════════════════════════════
// 1. DATA & MAP INITIALIZATION
// ════════════════════════════════════

// พิกัดศูนย์กลาง (โซนกาฬสินธุ์ / นครพนม ตามที่พี่ทูลใช้งาน)
const mapCenter = [16.432, 103.506];
const airMap = L.map('airMap', { zoomControl: false }).setView(mapCenter, 15);

// ขยับปุ่ม Zoom ไปไว้ขวาบน จะได้ไม่ทับเมนู
L.control.zoom({ position: 'topright' }).addTo(airMap);

// แผนที่ดาวเทียม Google Satellite Hybrid (ชัดเจน เห็นหลังคาบ้าน)
L.tileLayer('http://mt0.google.com/vt/lyrs=y&hl=en&x={x}&y={y}&z={z}', {
    maxZoom: 20,
    attribution: 'Map data &copy; Google'
}).addTo(airMap);

// ข้อมูลจำลองพิกัด Node (กระจายตัวในรัศมี)
const nodesData = [
  { id: 'Node-01', loc: 'แปลง A (โรงเรือน 1)', lat: 16.4335, lng: 103.5042, pm25: 12.4, temp: 28.4, humi: 62 },
  { id: 'Node-02', loc: 'แปลง B (กลางแจ้ง)',   lat: 16.4310, lng: 103.5085, pm25: 24.1, temp: 31.2, humi: 55 },
  { id: 'Node-03', loc: 'แปลง C (มุมอับ)',     lat: 16.4355, lng: 103.5070, pm25: 8.6,  temp: 27.5, humi: 68 },
  { id: 'Node-04', loc: 'แปลง D (โรงเรือน 2)', lat: 16.4295, lng: 103.5020, pm25: 42.5, temp: 29.8, humi: 60 },
  { id: 'Node-05', loc: 'แปลง E (ทางเข้า)',    lat: 16.4300, lng: 103.5100, pm25: 18.2, temp: 30.1, humi: 58 }
];

document.getElementById('nodeCount').innerText = nodesData.length;

// Helper หาค่าสีตามระดับ PM2.5 AQI
function getAQIColor(val) {
  if (val <= 12) return 'var(--aqi-good)';
  if (val <= 35.4) return 'var(--aqi-moderate)';
  if (val <= 55.4) return 'var(--aqi-sensitive)';
  if (val <= 150.4) return 'var(--aqi-unhealthy)';
  return 'var(--aqi-very)';
}

// ════════════════════════════════════
// 2. RENDER MARKERS & LIST
// ════════════════════════════════════
let markers = {}; // เก็บ object marker อ้างอิงด้วย ID

function renderMapAndList() {
  const listContainer = document.getElementById('nodeListContainer');
  listContainer.innerHTML = '';

  nodesData.forEach((n, index) => {
    const color = getAQIColor(n.pm25);
    
    // 1. สร้าง Custom Marker ที่มีตัวเลขอยู่ตรงกลาง
    const iconHtml = `
      <div style="position: relative;">
        <div class="aqi-marker-pulse" style="border-color: ${color}"></div>
        <div class="aqi-marker" style="background-color: ${color};">${Math.round(n.pm25)}</div>
      </div>
    `;
    const customIcon = L.divIcon({
      className: '',
      html: iconHtml,
      iconSize: [34, 34],
      iconAnchor: [17, 17], // ให้จุดศูนย์กลางอยู่กลางวงกลม
      popupAnchor: [0, -18]
    });

    // 2. สร้าง Popup ข้อมูลแบบสวยงาม
    const popupHtml = `
      <div class="popup-custom">
        <div class="pop-title">📡 ${n.id}</div>
        <div class="pop-row"><span class="pop-lbl">Location:</span> <span class="pop-val">${n.loc}</span></div>
        <div class="pop-row"><span class="pop-lbl">PM2.5:</span> <span class="pop-val" style="color:${color}">${n.pm25.toFixed(1)} µg/m³</span></div>
        <div class="pop-row"><span class="pop-lbl">Temp:</span> <span class="pop-val">${n.temp.toFixed(1)} °C</span></div>
        <div class="pop-row"><span class="pop-lbl">Humidity:</span> <span class="pop-val">${n.humi} %</span></div>
      </div>
    `;

    // 3. ปักหมุดลงบนแผนที่
    if(markers[n.id]) {
      // ถ้ามีหมุดอยู่แล้วแค่อัปเดตข้อมูล (สำหรับ realtime)
      markers[n.id].setIcon(customIcon);
      markers[n.id].getPopup().setContent(popupHtml);
    } else {
      // สร้างหมุดใหม่
      markers[n.id] = L.marker([n.lat, n.lng], {icon: customIcon})
        .bindPopup(popupHtml)
        .addTo(airMap);
    }

    // 4. สร้างรายการด้านซ้าย (List)
    const listItem = document.createElement('div');
    listItem.className = 'node-list-item';
    listItem.innerHTML = `
      <div class="nli-badge" style="background-color: ${color}; box-shadow: 0 0 8px ${color}80;">
        ${Math.round(n.pm25)}
      </div>
      <div class="nli-info">
        <div class="nli-name">${n.id}</div>
        <div class="nli-loc">${n.loc}</div>
      </div>
      <div class="nli-arrow">›</div>
    `;
    
    // พอกดที่รายการ ให้ซูมและแพนแผนที่ไปหาจุดนั้น พร้อมเปิด Popup
    listItem.onclick = () => {
      airMap.flyTo([n.lat, n.lng], 18, { duration: 1.5 });
      markers[n.id].openPopup();
    };

    listContainer.appendChild(listItem);
  });
}

// ════════════════════════════════════
// 3. REAL-TIME SIMULATION
// ════════════════════════════════════
function simulateMapRealtime() {
  nodesData.forEach(n => {
    // สุ่มเปลี่ยนค่า PM2.5 เล็กน้อย
    n.pm25 = Math.max(5, Math.min(160, n.pm25 + (Math.random() * 6 - 3)));
    n.temp = Math.max(20, Math.min(40, n.temp + (Math.random() * 0.4 - 0.2)));
  });
  renderMapAndList(); // วาดหมุดและลิสต์ใหม่
}

// วาดครั้งแรก
renderMapAndList();

// อัปเดตข้อมูลทุกๆ 3 วินาที
setInterval(simulateMapRealtime, 3000);

</script>

</main>
</body>
</html>