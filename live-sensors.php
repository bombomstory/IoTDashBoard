<?php
/* ── 1. Load global config ───────────────────────── */
require_once '_vars.php';

/* ── 2. Page-specific options ────────────────────── */
$page_title = "IoT Dashboard - Live Sensors";
$use_leaflet = false; // หน้านี้ไม่ได้ใช้แผนที่

// เพิ่ม CSS เฉพาะหน้านี้ (การ์ด Node และหน้าจอ Terminal)
$extra_css = '
<style>
  /* ── Node Card ── */
  .node-card {
    background: var(--bg-card);
    border: 1px solid var(--slate-200);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
  }
  .node-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
  
  .nc-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 18px;
    border-bottom: 1px solid var(--slate-100);
    background: var(--slate-50);
  }
  .nc-title-wrap { display: flex; align-items: center; gap: 10px; }
  .nc-icon { font-size: 1.2rem; }
  .nc-name { font-family: var(--font-head); font-weight: 700; color: var(--blue-900); font-size: .95rem; }
  .nc-loc { font-size: .7rem; color: var(--slate-400); font-family: var(--font-mono); }
  
  .nc-body { padding: 18px; display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  
  .sv-item { display: flex; flex-direction: column; gap: 4px; }
  .sv-lbl { font-size: .65rem; color: var(--slate-400); font-weight: 600; text-transform: uppercase; letter-spacing: .05em; display: flex; align-items: center; gap: 5px; }
  .sv-val { font-family: var(--font-mono); font-size: 1.25rem; font-weight: 600; color: var(--slate-700); line-height: 1; }
  .sv-unit { font-size: .7rem; color: var(--slate-400); font-weight: 400; }
  
  .nc-footer {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 18px; background: var(--slate-50); border-top: 1px solid var(--slate-100);
    font-size: .68rem; color: var(--slate-500); font-family: var(--font-mono);
  }

  /* ── Terminal / Raw Data ── */
  .terminal-wrap {
    background: #0f172a; /* Slate 900 */
    border-radius: var(--radius-md);
    padding: 16px;
    height: 250px;
    overflow-y: auto;
    font-family: var(--font-mono);
    font-size: .75rem;
    color: #38bdf8; /* Sky 400 */
    box-shadow: inset 0 4px 10px rgba(0,0,0,.3);
  }
  .term-line { margin-bottom: 6px; border-bottom: 1px dashed rgba(255,255,255,.05); padding-bottom: 4px; }
  .term-time { color: #64748b; margin-right: 10px; }
  .term-topic { color: #fcd34d; font-weight: 600; margin-right: 10px; }
  .term-val { color: #4ade80; }
  
  .terminal-wrap::-webkit-scrollbar { width: 6px; }
  .terminal-wrap::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
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
    <h2>📡 Live Sensor Streams</h2>
    <div class="section-line"></div>
    <div class="section-meta">อัปเดตข้อมูลแบบ Real-time ทุกๆ 3 วินาที</div>
  </div>

  <div class="grid-3" id="nodesContainer">
    </div>

  <div class="section-header" style="margin-top: 10px;">
    <h2>💻 Raw Data Stream (MQTT Log)</h2>
    <div class="section-line"></div>
    <div class="section-meta">Topic: esp32/sensor/#</div>
  </div>

  <div class="panel">
    <div class="terminal-wrap" id="mqttTerminal">
      <div class="term-line"><span class="term-time">[System]</span><span class="term-val"> MQTT Client connected to broker...</span></div>
      <div class="term-line"><span class="term-time">[System]</span><span class="term-val"> Subscribed to esp32/sensor/#</span></div>
    </div>
  </div>

<?php
/* ── 5. Footer + Shared JS ───────────────────────── */
require_once '_footer.php';
?>

<script>
// ════════════════════════════════════
// 1. DATA & RENDER CARDS
// ════════════════════════════════════
const liveNodes = [
  { id: 'Node-01', loc: 'แปลง A (โรงเรือน 1)', status: 'online', temp: 28.4, humi: 62.1, soil: 45.2, light: 3840, pm25: 12.4, batt: 92, rssi: -48 },
  { id: 'Node-02', loc: 'แปลง B (กลางแจ้ง)', status: 'online', temp: 31.2, humi: 55.0, soil: 38.5, light: 5200, pm25: 24.1, batt: 78, rssi: -62 },
  { id: 'Node-03', loc: 'แปลง C (มุมอับ)', status: 'warn',   temp: 29.5, humi: 68.4, soil: 55.0, light: 1200, pm25: 8.6,  batt: 18, rssi: -75 },
  { id: 'Node-04', loc: 'แปลง D (โรงเรือน 2)', status: 'online', temp: 27.8, humi: 65.2, soil: 48.7, light: 3100, pm25: 18.9, batt: 65, rssi: -55 },
  { id: 'Node-05', loc: 'แปลง E (ทางเข้า)', status: 'online', temp: 30.1, humi: 58.6, soil: 42.1, light: 4500, pm25: 11.2, batt: 88, rssi: -43 }
];

function getStatusBadge(status) {
  if(status === 'online') return `<span class="badge-pill badge-online"><span class="badge-dot"></span>Online</span>`;
  if(status === 'warn') return `<span class="badge-pill badge-warn"><span class="badge-dot"></span>Warning</span>`;
  return `<span class="badge-pill badge-offline"><span class="badge-dot"></span>Offline</span>`;
}

function renderNodeCards() {
  const container = document.getElementById('nodesContainer');
  container.innerHTML = liveNodes.map((n, i) => `
    <div class="node-card" style="border-top: 3px solid ${n.status === 'warn' ? 'var(--yellow-500)' : 'var(--green-500)'}">
      <div class="nc-header">
        <div class="nc-title-wrap">
          <div class="nc-icon">📡</div>
          <div>
            <div class="nc-name">${n.id}</div>
            <div class="nc-loc">${n.loc}</div>
          </div>
        </div>
        ${getStatusBadge(n.status)}
      </div>
      <div class="nc-body">
        <div class="sv-item"><div class="sv-lbl">🌡 Temp</div><div class="sv-val" id="val-temp-${i}">${n.temp.toFixed(1)}<span class="sv-unit">°C</span></div></div>
        <div class="sv-item"><div class="sv-lbl">💧 Humi</div><div class="sv-val" id="val-humi-${i}">${n.humi.toFixed(1)}<span class="sv-unit">%</span></div></div>
        <div class="sv-item"><div class="sv-lbl">🌱 Soil</div><div class="sv-val" id="val-soil-${i}">${n.soil.toFixed(1)}<span class="sv-unit">%</span></div></div>
        <div class="sv-item"><div class="sv-lbl">🌫 PM2.5</div><div class="sv-val" id="val-pm25-${i}" style="color:${n.pm25>35?'var(--orange-500)':'var(--green-500)'}">${n.pm25.toFixed(1)}<span class="sv-unit">µg</span></div></div>
      </div>
      <div class="nc-footer">
        <div>🔋 Batt: <span id="val-batt-${i}" style="color:${n.batt<20?'var(--red-500)':'inherit'}">${n.batt}%</span></div>
        <div>📶 RSSI: <span id="val-rssi-${i}">${n.rssi} dBm</span></div>
      </div>
    </div>
  `).join('');
}

renderNodeCards();

// ════════════════════════════════════
// 2. REAL-TIME SIMULATION & MQTT LOG
// ════════════════════════════════════
function rand(a, b) { return Math.random() * (b - a) + a; }
const terminal = document.getElementById('mqttTerminal');

function addTerminalLog(nodeId, type, val) {
  const now = new Date();
  const timeStr = now.toLocaleTimeString('en-GB', { hour12: false }) + '.' + String(now.getMilliseconds()).padStart(3, '0');
  
  const div = document.createElement('div');
  div.className = 'term-line';
  div.innerHTML = `<span class="term-time">[${timeStr}]</span> <span class="term-topic">esp32/sensor/${nodeId.toLowerCase()}/${type}</span> <span class="term-val">${val}</span>`;
  
  terminal.appendChild(div);
  
  // ลบ log เก่าถ้ามีเยอะเกิน 50 บรรทัด
  if(terminal.childElementCount > 50) { terminal.removeChild(terminal.firstChild); }
  
  // Auto-scroll ลงล่างสุด
  terminal.scrollTop = terminal.scrollHeight;
}

function simulateSensorData() {
  // สุ่มเลือก 1-2 Node เพื่ออัปเดตในแต่ละรอบ (ให้เหมือนส่งข้อมูลมาไม่พร้อมกัน)
  const nodeIndex = Math.floor(Math.random() * liveNodes.length);
  const n = liveNodes[nodeIndex];
  
  // เปลี่ยนแปลงค่าเล็กน้อย
  n.temp = Math.max(20, Math.min(40, n.temp + rand(-0.5, 0.5)));
  n.humi = Math.max(30, Math.min(95, n.humi + rand(-1.0, 1.0)));
  n.soil = Math.max(10, Math.min(80, n.soil + rand(-0.8, 0.8)));
  n.pm25 = Math.max(3,  Math.min(60, n.pm25 + rand(-1.5, 1.5)));
  
  // สุ่มแกว่งสัญญาณ RSSI เล็กน้อย
  n.rssi = Math.round(n.rssi + rand(-2, 2));

  // อัปเดต UI บนการ์ด
  document.getElementById(`val-temp-${nodeIndex}`).innerHTML = `${n.temp.toFixed(1)}<span class="sv-unit">°C</span>`;
  document.getElementById(`val-humi-${nodeIndex}`).innerHTML = `${n.humi.toFixed(1)}<span class="sv-unit">%</span>`;
  document.getElementById(`val-soil-${nodeIndex}`).innerHTML = `${n.soil.toFixed(1)}<span class="sv-unit">%</span>`;
  
  const pm25El = document.getElementById(`val-pm25-${nodeIndex}`);
  pm25El.innerHTML = `${n.pm25.toFixed(1)}<span class="sv-unit">µg</span>`;
  pm25El.style.color = n.pm25 > 35 ? 'var(--orange-500)' : 'var(--green-500)';
  
  document.getElementById(`val-rssi-${nodeIndex}`).innerText = `${n.rssi} dBm`;

  // สร้าง Log โยนเข้า Terminal
  const payload = JSON.stringify({
    t: parseFloat(n.temp.toFixed(1)),
    h: parseFloat(n.humi.toFixed(1)),
    s: parseFloat(n.soil.toFixed(1)),
    p: parseFloat(n.pm25.toFixed(1))
  });
  
  addTerminalLog(n.id, 'data', payload);
}

// อัปเดตข้อมูลทุกๆ 2 วินาที (เพื่อให้หน้า Terminal ขยับบ่อยๆ ดูสมจริง)
setInterval(simulateSensorData, 2000);

</script>

</main>
</body>
</html>