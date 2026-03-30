<?php
/* ── 1. Load global config ───────────────────────── */
require_once '_vars.php';

/* ── 2. Page-specific options ────────────────────── */
$page_title = "IoT Dashboard - Overview";
$use_leaflet = true; // เปิดใช้งาน Leaflet สำหรับแผนที่ดาวเทียม

// 💡 คำบรรยายของ AI สำหรับหน้า Index
$ai_index_text = "สวัสดีครับพี่ทูล! นี่คือรายงานสรุปภาพรวมของ พรศิริฟาร์มสุข จากหน้า Dashboard หลักนะครับ<br><br>";
$ai_index_text .= "ขณะนี้เซ็นเซอร์ทั้ง 5 โหนดบนแผนที่ดาวเทียมทำงานออนไลน์ 100% ครับ สภาพอากาศโดยรวม อุณหภูมิเฉลี่ย <strong>28.4 °C</strong> ความชื้น <strong>62.1%</strong> และความชื้นในดิน <strong>44.7%</strong> ถือว่าระบบจัดการน้ำและอากาศในโรงเรือนทำงานได้ยอดเยี่ยมมากครับ<br><br>";
$ai_index_text .= "ส่วนคุณภาพอากาศ ค่าฝุ่น PM2.5 อยู่ที่ <strong>12.4 µg/m³</strong> จัดอยู่ในเกณฑ์ <strong>ดี (Good)</strong> อากาศบริสุทธิ์เหมาะกับการทำงานในฟาร์มครับ พี่ทูลสามารถติดตามแนวโน้มแบบเรียลไทม์ได้จากกราฟและหน้าปัดด้านบนเลยครับ!";

// โหลด CSS ผสมกัน (Leaflet Map + Stat Cards + AI Assistant)
$extra_css = '
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
  /* ── Leaflet Map ── */
  #map { height: 250px; border-radius: var(--radius-md); z-index: 1; }
  .leaflet-marker-icon { border-radius: 50%; border: 2px solid white; box-shadow: 0 0 10px rgba(0,0,0,0.5); animation: nodeFloat 2.5s ease-in-out infinite; }
  .marker-online { background: var(--green-500); }
  .marker-warn { background: var(--yellow-500); box-shadow: 0 0 10px rgba(234,179,8,.8); }
  .leaflet-popup-content-wrapper { font-family: var(--font-ui); border-radius: 8px; }
  .leaflet-popup-content { font-size: 0.8rem; margin: 10px 14px; }

  /* ── Compact Stat Cards ── */
  .overview-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px; }
  .stat-card { 
    background: var(--bg-card); border: 1px solid var(--slate-200); 
    border-radius: var(--radius-lg); padding: 18px; 
    display: flex; align-items: center; gap: 16px; 
    box-shadow: var(--shadow-sm); transition: transform 0.2s;
  }
  .stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
  
  .stat-icon { width: 50px; height: 50px; border-radius: 12px; display: grid; place-items: center; font-size: 1.5rem; flex-shrink: 0; }
  .si-orange { background: var(--orange-50); color: var(--orange-500); }
  .si-blue { background: var(--blue-50); color: var(--blue-500); }
  .si-green { background: var(--green-50); color: var(--green-500); }
  .si-yellow { background: var(--yellow-50); color: var(--yellow-600); }
  
  .stat-info { flex: 1; }
  .stat-label { font-size: .7rem; color: var(--slate-400); text-transform: uppercase; letter-spacing: .05em; font-weight: 600; margin-bottom: 2px; }
  .stat-value { font-family: var(--font-mono); font-size: 1.5rem; font-weight: 700; color: var(--blue-900); line-height: 1; }
  .stat-trend { font-size: .7rem; font-family: var(--font-mono); font-weight: 600; margin-top: 4px; }
  .trend-up { color: var(--green-500); }
  .trend-down { color: var(--red-500); }

  /* ── AI Insight Section Styles ── */
  .ai-insight-panel {
    background: var(--bg-card); border: 1px solid var(--slate-200); 
    border-radius: var(--radius-xl); padding: 24px; 
    box-shadow: var(--shadow-md); transition: transform 0.2s;
    margin-bottom: 20px; display: flex; align-items: flex-start; gap: 24px;
  }
  .ai-insight-panel:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }
  
  .ai-avatar-wrap {
    width: 100px; height: 100px; border-radius: 50%;
    overflow: hidden; flex-shrink: 0;
    border: 4px solid var(--blue-100); box-shadow: var(--shadow-blue);
    background: #eef2f9; display: grid; place-items: center;
  }
  .ai-avatar-img { width: 85%; height: 85%; object-fit: contain; }
  
  .ai-speech-bubble {
    background: var(--blue-50); border: 1px solid var(--blue-200); 
    border-radius: 16px; border-top-left-radius: 0; padding: 18px 22px; 
    position: relative; flex: 1; min-height: 120px;
  }
  .ai-speech-bubble::after {
    content: ""; position: absolute; left: -1px; top: -1px; width: 0; height: 0;
    border: 10px solid transparent; border-right-color: var(--blue-200);
    border-top-color: var(--blue-200); border-left: 0; border-top: 0;
    margin-left: -10px; margin-top: 0px;
  }
  
  .ai-text-container { font-size: 0.9rem; color: var(--blue-900); line-height: 1.7; position: relative; }
  .ai-text-container strong { color: var(--blue-700); font-weight: 700; }
  
  /* Blinking cursor effect */
  .ai-text-container.typing::after {
    content: "|"; position: absolute; margin-left: 2px;
    color: var(--blue-500); animation: blinkCursor 0.8s infinite;
  }
  @keyframes blinkCursor { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }
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
    <h2>📟 Sensor Overview</h2>
    <div class="section-line"></div>
    <div class="section-meta" id="lastSyncLabel">Last sync: --:--</div>
  </div>

  <div class="overview-grid">
    <div class="stat-card">
      <div class="stat-icon si-orange">🌡️</div>
      <div class="stat-info">
        <div class="stat-label">Temperature</div>
        <div class="stat-value"><span id="v-temp">28.4</span> <span style="font-size: 1rem; color: var(--slate-400);">°C</span></div>
        <div class="stat-trend trend-up">▲ +0.3°C</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon si-blue">💧</div>
      <div class="stat-info">
        <div class="stat-label">Humidity</div>
        <div class="stat-value"><span id="v-humi">62.1</span> <span style="font-size: 1rem; color: var(--slate-400);">%</span></div>
        <div class="stat-trend trend-down">▼ -1.8%</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon si-green">🌱</div>
      <div class="stat-info">
        <div class="stat-label">Soil Moisture</div>
        <div class="stat-value"><span id="v-soil">44.7</span> <span style="font-size: 1rem; color: var(--slate-400);">%</span></div>
        <div class="stat-trend trend-up">▲ +2.1%</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon si-yellow">☀️</div>
      <div class="stat-info">
        <div class="stat-label">Light Lux</div>
        <div class="stat-value"><span id="v-light">3840</span> <span style="font-size: 1rem; color: var(--slate-400);">lx</span></div>
        <div class="stat-trend trend-up">▲ +120 lx</div>
      </div>
    </div>
    <div class="stat-card" id="sc-pm25-card">
      <div class="stat-icon" style="background:var(--green-100); color:var(--green-600)" id="sc-pm25-icon">😊</div>
      <div class="stat-info">
        <div class="stat-label">PM2.5 AQI</div>
        <div class="stat-value" id="sc-pm25-val" style="color:var(--aqi-good)"><span id="v-pm25">12.4</span> <span style="font-size: 1rem; color: var(--slate-400);">µg</span></div>
        <div class="stat-trend trend-down" id="d-pm25">▼ -1.2 µg</div>
      </div>
    </div>
  </div>

  <div class="section-header">
    <h2>🌫️ Air Quality — PM2.5</h2>
    <div class="section-line"></div>
    <div class="section-meta">PMS5003 Sensor · Node-01, 02, 05</div>
  </div>

  <div class="pm25-banner" id="pm25Banner">
    <div class="pm25-main-val">
      <div class="pm25-big" id="pm25BigVal">12.4</div>
      <div class="pm25-unit-lbl">µg/m³  ·  PM2.5</div>
    </div>
    <div class="pm25-info">
      <div class="aqi-badge" id="aqiBadge"><span class="aqi-dot"></span><span id="aqiLabel">ดี (Good)</span></div>
      <div class="pm25-desc" id="pm25Desc">คุณภาพอากาศอยู่ในระดับดี ฝุ่นละออง PM2.5 อยู่ในเกณฑ์มาตรฐาน WHO (<15 µg/m³) เหมาะสำหรับกิจกรรมกลางแจ้งทุกประเภท</div>
      <div class="pm25-recommendation"><span>💚</span><span id="pm25Rec">สามารถทำกิจกรรมกลางแจ้งได้ตามปกติ ไม่จำเป็นต้องสวมหน้ากากอนามัย</span></div>
    </div>
    <div class="pm25-scale">
      <div style="font-size:.65rem;font-weight:700;color:var(--slate-400);letter-spacing:.1em;text-transform:uppercase;margin-bottom:4px;">AQI SCALE</div>
      <div class="aqi-row" id="aqiRow0"><div class="aqi-pip" style="background:#22c55e"></div><span>ดี</span><span class="aqi-range">0–12</span></div>
      <div class="aqi-row" id="aqiRow1"><div class="aqi-pip" style="background:#eab308"></div><span>ปานกลาง</span><span class="aqi-range">12–35</span></div>
      <div class="aqi-row" id="aqiRow2"><div class="aqi-pip" style="background:#f97316"></div><span>กลุ่มเสี่ยง</span><span class="aqi-range">35–55</span></div>
      <div class="aqi-row" id="aqiRow3"><div class="aqi-pip" style="background:#ef4444"></div><span>ไม่ดี</span><span class="aqi-range">55–150</span></div>
      <div class="aqi-row" id="aqiRow4"><div class="aqi-pip" style="background:#a855f7"></div><span>อันตราย</span><span class="aqi-range">150+</span></div>
    </div>
  </div>

  <div class="pm25-chart-section">
    <div class="panel">
      <div class="panel-header">
        <div class="panel-dot pd-blue"></div>
        <div class="panel-title">PM2.5 Trend (24h)</div>
      </div>
      <div class="panel-body">
        <div style="height:210px;position:relative"><canvas id="pm25Chart"></canvas></div>
      </div>
    </div>
    <div class="panel">
      <div class="panel-header">
        <div class="panel-dot pd-purple"></div>
        <div class="panel-title">PM2.5 by Node</div>
      </div>
      <div class="panel-body" style="padding-bottom:0">
        <div id="pm25NodeBars" style="display:flex;flex-direction:column;gap:12px"></div>
      </div>
    </div>
  </div>

  <div class="section-header" style="margin-top:6px">
    <h2>📈 Sensor Trends & Devices</h2>
    <div class="section-line"></div>
  </div>

  <div class="grid-2-1">
    <div class="panel">
      <div class="panel-header">
        <div class="panel-dot pd-blue"></div>
        <div class="panel-title">Temperature & Humidity (24h)</div>
      </div>
      <div class="panel-body">
        <div style="height:200px;position:relative"><canvas id="trendChart"></canvas></div>
      </div>
    </div>
    <div class="panel">
      <div class="panel-header">
        <div class="panel-dot pd-green"></div>
        <div class="panel-title">Node Status</div>
      </div>
      <div class="panel-body-sm">
        <table class="device-table" id="deviceTable">
          <thead><tr><th>Node</th><th>Status</th><th>WiFi</th><th>Batt</th></tr></thead>
          <tbody id="deviceBody"></tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="grid-3">
    <div class="panel">
      <div class="panel-header"><div class="panel-dot pd-blue"></div><div class="panel-title">Live Gauges</div></div>
      <div class="panel-body">
        <div class="gauge-grid" id="gaugeGrid">
          <div class="gauge-item">
            <svg class="gauge-svg" viewBox="0 0 110 66">
              <path d="M8,62 A50,50 0 0,1 102,62" fill="none" stroke="#e2e8f0" stroke-width="9" stroke-linecap="round"/>
              <path id="g-temp" d="M8,62 A50,50 0 0,1 102,62" fill="none" stroke="#f97316" stroke-width="9" stroke-linecap="round" stroke-dasharray="157" stroke-dashoffset="90" style="transition:stroke-dashoffset .5s"/>
              <text x="55" y="56" text-anchor="middle" fill="#334155" font-size="13" font-family="JetBrains Mono,monospace" font-weight="600" id="gv-temp">28.4</text>
            </svg>
            <div class="gauge-num" id="gn-temp">28.4°C</div><div class="gauge-lbl">Temp</div>
          </div>
          <div class="gauge-item">
            <svg class="gauge-svg" viewBox="0 0 110 66">
              <path d="M8,62 A50,50 0 0,1 102,62" fill="none" stroke="#e2e8f0" stroke-width="9" stroke-linecap="round"/>
              <path id="g-humi" d="M8,62 A50,50 0 0,1 102,62" fill="none" stroke="#3b82f6" stroke-width="9" stroke-linecap="round" stroke-dasharray="157" stroke-dashoffset="60" style="transition:stroke-dashoffset .5s"/>
              <text x="55" y="56" text-anchor="middle" fill="#334155" font-size="13" font-family="JetBrains Mono,monospace" font-weight="600" id="gv-humi">62</text>
            </svg>
            <div class="gauge-num" id="gn-humi">62%</div><div class="gauge-lbl">Humidity</div>
          </div>
          <div class="gauge-item">
            <svg class="gauge-svg" viewBox="0 0 110 66">
              <path d="M8,62 A50,50 0 0,1 102,62" fill="none" stroke="#e2e8f0" stroke-width="9" stroke-linecap="round"/>
              <path id="g-soil" d="M8,62 A50,50 0 0,1 102,62" fill="none" stroke="#22c55e" stroke-width="9" stroke-linecap="round" stroke-dasharray="157" stroke-dashoffset="87" style="transition:stroke-dashoffset .5s"/>
              <text x="55" y="56" text-anchor="middle" fill="#334155" font-size="13" font-family="JetBrains Mono,monospace" font-weight="600" id="gv-soil">45</text>
            </svg>
            <div class="gauge-num" id="gn-soil">44.7%</div><div class="gauge-lbl">Soil</div>
          </div>
          <div class="gauge-item">
            <svg class="gauge-svg" viewBox="0 0 110 66">
              <path d="M8,62 A50,50 0 0,1 102,62" fill="none" stroke="#e2e8f0" stroke-width="9" stroke-linecap="round"/>
              <path id="g-pm25" d="M8,62 A50,50 0 0,1 102,62" fill="none" stroke="#22c55e" stroke-width="9" stroke-linecap="round" stroke-dasharray="157" stroke-dashoffset="127" style="transition:stroke-dashoffset .5s"/>
              <text x="55" y="56" text-anchor="middle" fill="#334155" font-size="12" font-family="JetBrains Mono,monospace" font-weight="600" id="gv-pm25">12.4</text>
            </svg>
            <div class="gauge-num" id="gn-pm25">12.4 µg</div><div class="gauge-lbl">PM2.5</div>
          </div>
        </div>
      </div>
    </div>
    <div class="panel">
      <div class="panel-header"><div class="panel-dot pd-red"></div><div class="panel-title">Alerts</div></div>
      <div class="panel-body-sm">
        <div class="alert-item"><div class="alert-icon-wrap ai-yellow">⚠️</div><div class="alert-content"><div class="alert-msg">Node-03 แบตเตอรี่ต่ำ (18%)</div><div class="alert-time">10:00 · ESP32 Node-03</div></div></div>
        <div class="alert-item"><div class="alert-icon-wrap ai-red">🌡️</div><div class="alert-content"><div class="alert-msg">อุณหภูมิเกิน 35°C (Node-01)</div><div class="alert-time">09:00 · DHT22</div></div></div>
      </div>
    </div>
    <div class="panel">
      <div class="panel-header"><div class="panel-dot pd-blue"></div><div class="panel-title">Sync Log</div></div>
      <div class="panel-body-sm"><div class="timeline" id="syncLog"></div></div>
    </div>
  </div>

  <div class="panel" style="margin-bottom:16px">
    <div class="panel-header">
      <div class="panel-dot pd-blue"></div>
      <div class="panel-title">Node Deployment Map (Satellite)</div>
      <div class="panel-sub">📍 Kalasin · 5 Nodes Online</div>
    </div>
    <div class="panel-body" style="padding: 10px;">
      <div id="map"></div>
    </div>
  </div>

  <div class="section-header" style="margin-top: 20px;">
    <h2>🤖 AI Overview Assistant</h2>
    <div class="section-line"></div>
    <div class="section-meta">สรุปสถานการณ์ฟาร์มแบบ Real-time</div>
  </div>

  <div class="ai-insight-panel">
    <div class="ai-avatar-wrap">
      <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f916/512.gif" alt="Animated AI Robot" class="ai-avatar-img">
    </div>
    <div class="ai-speech-bubble">
      <div id="aiTextSource" style="display: none;"><?php echo $ai_index_text; ?></div>
      <div class="ai-text-container typing" id="aiTextTypedResult"></div>
    </div>
  </div>

<?php
/* ── 5. Footer + Shared JS ───────────────────────── */
require_once '_footer.php';
?>

<script>
window.addEventListener('DOMContentLoaded', () => {
  const sourceContainer = document.getElementById('aiTextSource');
  const targetContainer = document.getElementById('aiTextTypedResult');
  const delay = 35; // ความเร็วในการพิมพ์ (ms)
  const pauseEnd = 6000; // เวลาหยุดพัก 6 วินาที

  function typeTextNode(text, textNode, charIndex, callback) {
    if (charIndex < text.length) {
      textNode.nodeValue += text.charAt(charIndex);
      setTimeout(() => typeTextNode(text, textNode, charIndex + 1, callback), delay);
    } else {
      callback();
    }
  }

  function typeChildrenNodes(children, childIndex, target, callback) {
    if (childIndex < children.length) {
      const child = children[childIndex];
      const newNode = child.cloneNode(false);
      newNode.textContent = ""; 
      target.appendChild(newNode);

      if (child.nodeType === Node.TEXT_NODE) {
        typeTextNode(child.nodeValue, newNode, 0, () => {
          typeChildrenNodes(children, childIndex + 1, target, callback);
        });
      } else if (child.nodeType === Node.ELEMENT_NODE) {
        typeChildrenNodes(child.childNodes, 0, newNode, () => {
          typeChildrenNodes(children, childIndex + 1, target, callback);
        });
      } else {
        typeChildrenNodes(children, childIndex + 1, target, callback);
      }
    } else if (callback) {
      callback();
    }
  }

  function startTypingLoop() {
    targetContainer.innerHTML = ''; 
    targetContainer.classList.add('typing'); 
    
    typeChildrenNodes(sourceContainer.childNodes, 0, targetContainer, () => {
      targetContainer.classList.remove('typing'); 
      setTimeout(() => { startTypingLoop(); }, pauseEnd);
    });
  }

  setTimeout(startTypingLoop, 1000); 
});
</script>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// 1. SATELLITE MAP (LEAFLET)
const map = L.map('map').setView([16.432, 103.506], 15);
L.tileLayer('http://mt0.google.com/vt/lyrs=y&hl=en&x={x}&y={y}&z={z}', { maxZoom: 20, attribution: 'Map data &copy; Google' }).addTo(map);

const mapNodes = [
  { id: 'Node-01', lat: 16.4335, lng: 103.5042, status: 'online', val: 'PM2.5: 12.4 µg' },
  { id: 'Node-02', lat: 16.4310, lng: 103.5085, status: 'online', val: 'PM2.5: 24.1 µg' },
  { id: 'Node-03', lat: 16.4355, lng: 103.5070, status: 'warn',   val: 'Batt: 18%' },
  { id: 'Node-04', lat: 16.4295, lng: 103.5020, status: 'online', val: 'PM2.5: 18.9 µg' },
  { id: 'Node-05', lat: 16.4300, lng: 103.5100, status: 'online', val: 'PM2.5: 11.2 µg' }
];

mapNodes.forEach(n => {
  const colorClass = n.status === 'online' ? 'marker-online' : 'marker-warn';
  const icon = L.divIcon({ className: `leaflet-marker-icon ${colorClass}`, iconSize: [14, 14], iconAnchor: [7, 7], popupAnchor: [0, -10] });
  L.marker([n.lat, n.lng], {icon: icon}).addTo(map).bindPopup(`<div style="font-family: 'DM Sans', sans-serif;"><strong style="color:var(--blue-700)">${n.id}</strong><br><span style="font-size:0.75rem; color:var(--slate-500)">Status: ${n.status.toUpperCase()}</span><br><span style="font-size:0.75rem; font-weight:bold">${n.val}</span></div>`);
});

// 2. MOCKUP DATA COMPONENTS
const syncLogs = [{ok:true, msg:'Sync MySQL สำเร็จ · 5/5 nodes', time:'10:00'}, {ok:true, msg:'Sync MySQL สำเร็จ · 5/5 nodes', time:'09:00'}, {ok:false,msg:'Node-03 timeout · retry OK', time:'08:00'}, {ok:true, msg:'Sync MySQL สำเร็จ · 5/5 nodes', time:'07:00'}];
document.getElementById('syncLog').innerHTML = syncLogs.map((e,i) => `<div class="tl-item"><div class="tl-aside"><div class="tl-dot ${e.ok?'ok':'err'}"></div>${i < syncLogs.length-1 ? '<div class="tl-seg"></div>' : ''}</div><div style="padding-bottom:${i<syncLogs.length-1?'0':'0'}"><div class="tl-title">${e.msg}</div><div class="tl-time">${e.time}</div></div></div>`).join('');

const devices = [{id:'Node-01',loc:'แปลง A', status:'online', rssi:-48, batt:92}, {id:'Node-02',loc:'แปลง B', status:'online', rssi:-62, batt:78}, {id:'Node-03',loc:'แปลง C', status:'warn', rssi:-75, batt:18}, {id:'Node-04',loc:'แปลง D', status:'online', rssi:-55, batt:65}, {id:'Node-05',loc:'แปลง E', status:'online', rssi:-43, batt:88}];
function rssiSegs(r) { const n = r > -50 ? 4 : r > -65 ? 3 : r > -75 ? 2 : 1; return Array.from({length:4},(_,i)=>`<div class="rssi-b${i<n?' on':''}" style="height:${(i+1)*3+3}px"></div>`).join(''); }
document.getElementById('deviceBody').innerHTML = devices.map(d => { const battColor = d.batt < 25 ? '#ef4444' : d.batt < 50 ? '#eab308' : '#22c55e'; return `<tr><td><div class="node-name">${d.id}</div><div class="node-loc">${d.loc}</div></td><td><span class="badge-pill badge-${d.status==='online'?'online':d.status==='warn'?'warn':'offline'}"><span class="badge-dot"></span>${d.status==='online'?'Online':d.status==='warn'?'Warning':'Offline'}</span></td><td><div class="rssi-bars">${rssiSegs(d.rssi)}</div></td><td><div style="font-family:var(--font-mono);font-size:.72rem;color:${battColor};font-weight:600;margin-bottom:3px">${d.batt}%</div><div class="bar-batt"><div class="bar-batt-fill" style="width:${d.batt}%;background:${battColor}"></div></div></td></tr>`; }).join('');

const nodesPM25 = [{id:'Node-01', val:12.4}, {id:'Node-02', val:24.1}, {id:'Node-03', val:8.6}, {id:'Node-04', val:18.9}, {id:'Node-05', val:11.2}];
function renderPM25Bars() { const max = Math.max(...nodesPM25.map(n=>n.val), 40); document.getElementById('pm25NodeBars').innerHTML = nodesPM25.map(n => { let color = '#22c55e'; if(n.val>12) color = '#eab308'; if(n.val>35.4) color = '#f97316'; const pct = (n.val / max * 100).toFixed(1); return `<div style="display:flex;align-items:center;gap:10px"><div style="font-family:var(--font-mono);font-size:.7rem;color:var(--slate-500);width:58px;flex-shrink:0">${n.id}</div><div style="flex:1;height:8px;background:var(--slate-100);border-radius:4px;overflow:hidden"><div style="height:100%;width:${pct}%;background:${color};border-radius:4px;transition:width .5s"></div></div><div style="font-family:var(--font-mono);font-size:.72rem;font-weight:600;color:${color};width:46px;text-align:right">${n.val} µg</div></div>`; }).join(''); }
renderPM25Bars();

// 3. CHARTS.JS INITIALIZATION
const hours = Array.from({length:24},(_,i)=>`${String(i).padStart(2,'0')}:00`);
new Chart(document.getElementById('pm25Chart').getContext('2d'), { type:'line', data:{ labels: hours, datasets:[{ label:'PM2.5 (µg/m³)', data: [8.2,7.8,7.4,7.1,6.8,6.5,7.2,9.4,12.1,14.8,16.2,18.5,20.1,22.3,24.1,21.8,19.2,17.4,15.8,14.2,13.1,12.4,11.6,10.9], borderColor: '#3b82f6', backgroundColor: (ctx) => { const g = ctx.chart.ctx.createLinearGradient(0,0,0,ctx.chart.height); g.addColorStop(0,'rgba(59,130,246,.15)'); g.addColorStop(1,'rgba(59,130,246,.01)'); return g; }, fill:true, tension:.4, pointRadius:0, borderWidth:2.5 }] }, options:{ responsive:true, maintainAspectRatio:false, scales:{ x:{ grid:{color:'rgba(203,213,225,.3)'}, ticks:{font:{family:'JetBrains Mono',size:9},maxTicksLimit:12} }, y:{ grid:{color:'rgba(203,213,225,.3)'}, min:0, max:40 } } } });
new Chart(document.getElementById('trendChart').getContext('2d'), { type:'line', data:{ labels: hours, datasets:[ { label:'อุณหภูมิ (°C)', data:[26.2,25.8,25.4,25.1,24.9,24.7,25.2,26.8,28.1,29.4,30.6,31.2,31.8,32.1,31.5,30.8,29.9,29.2,28.4,27.8,27.3,26.9,26.5,26.2], borderColor:'#f97316', fill:false, tension:.4, pointRadius:0, borderWidth:2, yAxisID:'y' }, { label:'ความชื้น (%RH)', data:[68,70,72,74,75,76,73,68,63,59,56,54,52,51,53,56,59,62,65,67,68,69,70,69], borderColor:'#3b82f6', fill:false, tension:.4, pointRadius:0, borderWidth:2, yAxisID:'y1' } ] }, options:{ responsive:true, maintainAspectRatio:false, scales:{ x:{grid:{color:'rgba(203,213,225,.3)'},ticks:{font:{family:'JetBrains Mono',size:9},maxTicksLimit:12}}, y:{grid:{color:'rgba(203,213,225,.3)'},position:'left'}, y1:{grid:{drawOnChartArea:false},position:'right'} } } });

// 4. REAL-TIME SIMULATION
const PM25_LEVELS = [ { max:12, bg:'var(--green-100)', color:'var(--aqi-good)', cls:'good' }, { max:35.4, bg:'var(--yellow-100)', color:'var(--aqi-moderate)', cls:'moderate' }, { max:55.4, bg:'var(--orange-100)', color:'var(--aqi-sensitive)', cls:'sensitive' }, { max:150.4, bg:'var(--red-100)', color:'var(--aqi-unhealthy)', cls:'unhealthy' }, { max:999, bg:'var(--purple-100)', color:'var(--aqi-very)', cls:'very' } ];
function getAQILevel(v) { return PM25_LEVELS.find(l => v <= l.max) || PM25_LEVELS[PM25_LEVELS.length-1]; }
const state = {temp:28.4, humi:62.1, soil:44.7, light:3840, pm25:12.4};

function updateLive() {
  state.temp  = Math.max(20, Math.min(40, state.temp  + (Math.random()*.6-.3)));
  state.humi  = Math.max(30, Math.min(95, state.humi  + (Math.random()*1-.5)));
  state.soil  = Math.max(10, Math.min(80, state.soil  + (Math.random()*.8-.4)));
  state.light = Math.max(0,  Math.min(6000, state.light + (Math.random()*120-60)));
  state.pm25  = Math.max(3,  Math.min(55, state.pm25  + (Math.random()*.8-.4)));

  document.getElementById('v-temp').textContent  = state.temp.toFixed(1);
  document.getElementById('v-humi').textContent  = state.humi.toFixed(1);
  document.getElementById('v-soil').textContent  = state.soil.toFixed(1);
  document.getElementById('v-light').textContent = Math.round(state.light);
  
  const lv = getAQILevel(state.pm25);
  document.getElementById('sc-pm25-val').style.color = lv.color;
  document.getElementById('sc-pm25-icon').style.background = lv.bg;
  document.getElementById('sc-pm25-icon').style.color = lv.color;
  document.getElementById('v-pm25').textContent  = state.pm25.toFixed(1);
  
  document.getElementById('pm25BigVal').textContent = state.pm25.toFixed(1);
  document.getElementById('pm25BigVal').style.color = lv.color;
  document.getElementById('aqiBadge').style.background = lv.bg;
  document.getElementById('aqiBadge').style.color = lv.color;
  document.getElementById('pm25Banner').className = 'pm25-banner aqi-' + lv.cls;

  const offs = (v,mn,mx) => (157 - Math.min(1,(v-mn)/(mx-mn))*157).toFixed(1);
  document.getElementById('g-temp').setAttribute('stroke-dashoffset', offs(state.temp,15,45));
  document.getElementById('gv-temp').textContent = state.temp.toFixed(1);
  document.getElementById('gn-temp').textContent = state.temp.toFixed(1)+'°C';
  document.getElementById('g-humi').setAttribute('stroke-dashoffset', offs(state.humi,30,100));
  document.getElementById('gv-humi').textContent = Math.round(state.humi);
  document.getElementById('gn-humi').textContent = Math.round(state.humi)+'%';
  document.getElementById('g-soil').setAttribute('stroke-dashoffset', offs(state.soil,0,100));
  document.getElementById('gv-soil').textContent = Math.round(state.soil);
  document.getElementById('gn-soil').textContent = state.soil.toFixed(1)+'%';
  document.getElementById('g-pm25').style.stroke = lv.color;
  document.getElementById('g-pm25').setAttribute('stroke-dashoffset', offs(state.pm25,0,75));
  document.getElementById('gv-pm25').textContent = state.pm25.toFixed(1);
  document.getElementById('gn-pm25').textContent = state.pm25.toFixed(1) + ' µg';
}
setInterval(updateLive, 5000);
</script>

</main>
</body>
</html>