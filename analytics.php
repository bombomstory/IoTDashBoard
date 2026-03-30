<?php
/* ── 1. Load global config ───────────────────────── */
require_once '_vars.php';

/* ── 2. Page-specific options ────────────────────── */
$page_title = "IoT Dashboard - Analytics";
$use_leaflet = false;

// 💡 กำหนดคำบรรยายของ AI
$avg_temp = 29.4;
$avg_humi = 65.2;
$peak_pm = 42.1;
$total_alerts = 14;

$ai_analysis_text = "สวัสดีครับพี่ทูล! วันนี้ผมวิเคราะห์ข้อมูลย้อนหลัง 7 วันให้แล้วนะครับ<br><br>";
$ai_analysis_text .= "ภาพรวมสัปดาห์นี้ อุณหภูมิเฉลี่ยอยู่ที่ <strong>{$avg_temp}°C</strong> ถือว่าเหมาะสมดีครับสำหรับพืช ส่วนความชื้นเฉลี่ย <strong>{$avg_humi}%</strong> กำลังพอดีเลยครับ<br><br>";
$ai_analysis_text .= "จุดที่ต้องเฝ้าระวังคือ <strong>Node-02 (แปลง B)</strong> ครับ เพราะวัดค่าฝุ่น PM2.5 สูงสุดได้ถึง <strong>{$peak_pm} µg/m³</strong> ซึ่งจัดอยู่ในเกณฑ์กลุ่มเสี่ยง สอดคล้องกับกราฟแท่งที่มีสีส้มขึ้นในช่วงกลางสัปดาห์ครับ<br><br>";
$ai_analysis_text .= "สำหรับยอดแจ้งเตือน <strong>{$total_alerts} ครั้ง</strong> ส่วนใหญ่เกิดจากอุณหภูมิสูง แนะนำให้ตรวจสอบระบบพ่นหมอกในโรงเรือนช่วงบ่ายนะครับ สู้ๆ ครับพี่ทูล!";

// CSS เฉพาะหน้า Analytics
$extra_css = '
<style>
  .filter-bar { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
  .btn-filter {
    background: var(--bg-card); border: 1px solid var(--slate-200);
    padding: 8px 16px; border-radius: var(--radius-sm);
    font-size: .8rem; font-weight: 500; color: var(--slate-600);
    cursor: pointer; transition: all 0.2s;
    font-family: var(--font-ui);
  }
  .btn-filter.active { background: var(--blue-50); border-color: var(--blue-500); color: var(--blue-700); font-weight: 600; }
  .btn-filter:hover:not(.active) { background: var(--slate-50); }
  
  .btn-export { 
    margin-left: auto; background: var(--green-500); color: white; border: none; 
    box-shadow: 0 2px 6px rgba(34,197,94,.3);
  }
  .btn-export:hover { background: var(--green-600); transform: translateY(-1px); }

  .stat-card { 
    background: var(--bg-card); border: 1px solid var(--slate-200); 
    border-radius: var(--radius-lg); padding: 18px; 
    display: flex; align-items: center; gap: 16px; 
    box-shadow: var(--shadow-sm); transition: transform 0.2s;
  }
  .stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
  
  .stat-icon { width: 52px; height: 52px; border-radius: 12px; display: grid; place-items: center; font-size: 1.6rem; flex-shrink: 0; }
  .si-orange { background: var(--orange-50); color: var(--orange-500); }
  .si-blue { background: var(--blue-50); color: var(--blue-500); }
  .si-purple { background: var(--purple-50); color: var(--purple-500); }
  .si-red { background: var(--red-50); color: var(--red-500); }
  
  .stat-info { flex: 1; }
  .stat-label { font-size: .7rem; color: var(--slate-400); text-transform: uppercase; letter-spacing: .05em; font-weight: 600; margin-bottom: 4px; }
  .stat-value { font-family: var(--font-mono); font-size: 1.6rem; font-weight: 700; color: var(--blue-900); line-height: 1; }
  .stat-trend { font-size: .7rem; font-family: var(--font-mono); font-weight: 600; margin-top: 4px; }
  .trend-up { color: var(--green-500); }
  .trend-down { color: var(--red-500); }

  /* 🤖 AI Insight Section Styles */
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
    content: "|";
    position: absolute;
    margin-left: 2px;
    color: var(--blue-500);
    animation: blinkCursor 0.8s infinite;
  }
  @keyframes blinkCursor {
    0%, 100% { opacity: 1; }
    50% { opacity: 0; }
  }
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
    <h2>📊 Data Analytics & Reports</h2>
    <div class="section-line"></div>
    <div class="section-meta">Historical Data Analysis</div>
  </div>

  <div class="filter-bar fade-up">
    <button class="btn-filter">Today</button>
    <button class="btn-filter active">Last 7 Days</button>
    <button class="btn-filter">Last 30 Days</button>
    <button class="btn-filter">This Month</button>
    <button class="btn-filter btn-export">📥 Export CSV</button>
  </div>

  <div class="grid-2-1 fade-up-1" style="grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px;">
    <div class="stat-card"><div class="stat-icon si-orange">🌡️</div><div class="stat-info"><div class="stat-label">Avg Temperature</div><div class="stat-value">29.4 <span style="font-size: 1rem; color: var(--slate-400);">°C</span></div><div class="stat-trend trend-up">▲ +0.5°C</div></div></div>
    <div class="stat-card"><div class="stat-icon si-blue">💧</div><div class="stat-info"><div class="stat-label">Avg Humidity</div><div class="stat-value">65.2 <span style="font-size: 1rem; color: var(--slate-400);">%</span></div><div class="stat-trend trend-down">▼ -2.1%</div></div></div>
    <div class="stat-card"><div class="stat-icon si-purple">🌫️</div><div class="stat-info"><div class="stat-label">PM2.5 Peak</div><div class="stat-value">42.1 <span style="font-size: 1rem; color: var(--slate-400);">µg</span></div><div class="stat-trend trend-up">▲ +5.2 µg</div></div></div>
    <div class="stat-card"><div class="stat-icon si-red">🚨</div><div class="stat-info"><div class="stat-label">Total Alerts</div><div class="stat-value">14 <span style="font-size: 1rem; color: var(--slate-400);">times</span></div><div class="stat-trend trend-down">▼ -3 times</div></div></div>
  </div>

  <div class="grid-2-1 fade-up-2">
    <div class="panel">
      <div class="panel-header"><div class="panel-dot pd-blue"></div><div class="panel-title">Weekly Environmental Trend</div></div>
      <div class="panel-body"><div style="height:250px;position:relative"><canvas id="weeklyTrendChart"></canvas></div></div>
    </div>
    <div class="panel">
      <div class="panel-header"><div class="panel-dot pd-purple"></div><div class="panel-title">PM2.5 Daily Average</div></div>
      <div class="panel-body"><div style="height:250px;position:relative"><canvas id="pm25BarChart"></canvas></div></div>
    </div>
  </div>

  <div class="grid-2-1 fade-up-3">
    <div class="panel">
      <div class="panel-header"><div class="panel-dot pd-green"></div><div class="panel-title">Node Performance Summary</div></div>
      <div class="panel-body-sm" style="overflow-x: auto;">
        <table class="device-table" style="min-width: 500px;">
          <thead><tr><th>Node ID</th><th>Location</th><th>Avg Temp</th><th>Avg Humi</th><th>Max PM2.5</th><th>Uptime</th></tr></thead>
          <tbody id="analyticsTableBody"></tbody>
        </table>
      </div>
    </div>
    <div class="panel">
      <div class="panel-header"><div class="panel-dot pd-red"></div><div class="panel-title">Alert Distribution</div></div>
      <div class="panel-body" style="display:flex; flex-direction:column; align-items:center; justify-content:center;">
        <div style="height:180px; width:100%; position:relative"><canvas id="alertDonutChart"></canvas></div>
        <div style="display:flex; gap:12px; margin-top:16px; flex-wrap:wrap; justify-content:center; font-size:.75rem; color:var(--slate-600);">
          <div style="display:flex; align-items:center; gap:4px;"><div style="width:10px;height:10px;border-radius:3px;background:var(--red-500)"></div> Temp High</div>
          <div style="display:flex; align-items:center; gap:4px;"><div style="width:10px;height:10px;border-radius:3px;background:var(--orange-500)"></div> PM2.5 High</div>
          <div style="display:flex; align-items:center; gap:4px;"><div style="width:10px;height:10px;border-radius:3px;background:var(--yellow-500)"></div> Batt Low</div>
          <div style="display:flex; align-items:center; gap:4px;"><div style="width:10px;height:10px;border-radius:3px;background:var(--slate-400)"></div> Offline</div>
        </div>
      </div>
    </div>
  </div>

  <div class="section-header fade-up" style="margin-top: 20px;">
    <h2>🤖 AI Insight Assistant</h2>
    <div class="section-line"></div>
    <div class="section-meta">บทวิเคราะห์จากปัญญาประดิษฐ์ (Real-time Report)</div>
  </div>

  <div class="ai-insight-panel fade-up-4">
    <div class="ai-avatar-wrap">
      <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f916/512.gif" alt="Animated AI Robot" class="ai-avatar-img">
    </div>
    <div class="ai-speech-bubble">
      <div id="aiTextSource" style="display: none;">
        <?php echo $ai_analysis_text; ?>
      </div>
      <div class="ai-text-container typing" id="aiTextTypedResult"></div>
    </div>
  </div>

<?php
/* ── 5. Footer + Shared JS ───────────────────────── */
require_once '_footer.php';
?>

<script>
window.onload = function() {
  const sourceContainer = document.getElementById('aiTextSource');
  const targetContainer = document.getElementById('aiTextTypedResult');
  const delay = 35; // ความเร็วในการพิมพ์ (ms) ต่อตัวอักษร
  const pauseEnd = 6000; // เวลาหยุดพักหลังพิมพ์จบประโยคสุดท้าย (6 วินาที) ก่อนจะลบแล้วเริ่มใหม่

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

  // ฟังก์ชันควบคุมการวนลูป
  function startTypingLoop() {
    targetContainer.innerHTML = ''; // ล้างข้อความเก่าทิ้งทั้งหมด
    targetContainer.classList.add('typing'); // แสดงเคอร์เซอร์กะพริบ
    
    typeChildrenNodes(sourceContainer.childNodes, 0, targetContainer, () => {
      // เมื่อพิมพ์จนจบข้อความทั้งหมดแล้ว
      targetContainer.classList.remove('typing'); // ซ่อนเคอร์เซอร์ชั่วคราว
      
      // หน่วงเวลาให้คนอ่านจบ แล้วเรียกฟังก์ชันตัวเองซ้ำเพื่อลบแล้วพิมพ์ใหม่
      setTimeout(() => {
        startTypingLoop();
      }, pauseEnd);
    });
  }

  // ดีเลย์นิดนึงก่อนเริ่มพิมพ์ครั้งแรก
  setTimeout(startTypingLoop, 1000); 
};
</script>

<script>
const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
new Chart(document.getElementById('weeklyTrendChart').getContext('2d'), { type: 'line', data: { labels: days, datasets: [{ label: 'Avg Temp (°C)', data: [28.5, 29.1, 30.2, 29.8, 28.7, 27.9, 28.4], borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,0.1)', fill: true, tension: 0.4, yAxisID: 'y'}, { label: 'Avg Humidity (%)', data: [65, 62, 58, 60, 66, 70, 68], borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', fill: true, tension: 0.4, yAxisID: 'y1' }] }, options: { responsive: true, maintainAspectRatio: false } });
new Chart(document.getElementById('pm25BarChart').getContext('2d'), { type: 'bar', data: { labels: days, datasets: [{ label: 'PM2.5', data: [12.4, 15.2, 28.5, 42.1, 18.6, 14.2, 11.5], backgroundColor: '#eab308' }] }, options: { responsive: true, maintainAspectRatio: false } });
new Chart(document.getElementById('alertDonutChart').getContext('2d'), { type: 'doughnut', data: { labels: ['Temp', 'PM2.5', 'Batt', 'Offline'], datasets: [{ data: [5, 4, 3, 2], backgroundColor: ['#ef4444', '#f97316', '#eab308', '#94a3b8'] }] }, options: { responsive: true, maintainAspectRatio: false, cutout: '70%' } });

const analyticsData = [
  { id: 'Node-01', loc: 'แปลง A', temp: 28.5, humi: 65, pm25: 18.2, uptime: '99.9%' },
  { id: 'Node-02', loc: 'แปลง B', temp: 30.1, humi: 58, pm25: 42.1, uptime: '99.5%' },
  { id: 'Node-03', loc: 'แปลง C', temp: 29.2, humi: 62, pm25: 15.6, uptime: '85.2%' },
  { id: 'Node-04', loc: 'แปลง D', temp: 27.8, humi: 68, pm25: 22.4, uptime: '99.8%' },
  { id: 'Node-05', loc: 'แปลง E', temp: 29.9, humi: 59, pm25: 14.1, uptime: '98.0%' }
];
document.getElementById('analyticsTableBody').innerHTML = analyticsData.map(d => `<tr><td><div class="node-name" style="color:var(--blue-600)">${d.id}</div></td><td style="color:var(--slate-500); font-size:.75rem;">${d.loc}</td><td style="font-family:var(--font-mono); font-weight:600;">${d.temp.toFixed(1)}°C</td><td style="font-family:var(--font-mono); font-weight:600;">${d.humi}%</td><td style="font-family:var(--font-mono); font-weight:600; color:${d.pm25>35?'var(--orange-500)':'var(--green-500)'}">${d.pm25.toFixed(1)}</td><td><span class="badge-pill" style="background:${parseFloat(d.uptime)>95?'var(--green-100)':'var(--red-100)'}; color:${parseFloat(d.uptime)>95?'var(--green-600)':'var(--red-600)'}">${d.uptime}</span></td></tr>`).join('');
</script>

</main>
</body>
</html>