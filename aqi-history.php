<?php
/* ── 1. Load global config ───────────────────────── */
require_once '_vars.php';

/* ── 2. Page-specific options ────────────────────── */
$page_title = "IoT Dashboard - AQI History";
$use_leaflet = false;

// CSS เฉพาะหน้า AQI History
$extra_css = '
<style>
  /* ── Controls Bar ── */
  .history-controls {
    display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; align-items: center;
    background: var(--bg-card); padding: 14px 20px; border-radius: var(--radius-lg);
    border: 1px solid var(--slate-200); box-shadow: var(--shadow-sm);
  }
  .hc-group { display: flex; align-items: center; gap: 8px; }
  .hc-label { font-size: .75rem; font-weight: 600; color: var(--slate-500); text-transform: uppercase; }
  .hc-select, .hc-input {
    padding: 8px 12px; border: 1px solid var(--slate-300); border-radius: var(--radius-sm);
    font-family: var(--font-ui); font-size: .85rem; color: var(--slate-700); background: var(--slate-50);
    outline: none; transition: border 0.2s;
  }
  .hc-select:focus, .hc-input:focus { border-color: var(--blue-500); background: white; }
  
  .btn-primary {
    background: var(--blue-600); color: white; border: none; padding: 8px 16px;
    border-radius: var(--radius-sm); font-weight: 500; cursor: pointer; transition: background 0.2s;
  }
  .btn-primary:hover { background: var(--blue-700); }
  .btn-export { margin-left: auto; background: var(--green-500); color: white; border: none; padding: 8px 16px; border-radius: var(--radius-sm); font-weight: 500; cursor: pointer; box-shadow: 0 2px 6px rgba(34,197,94,.3); }
  .btn-export:hover { background: var(--green-600); transform: translateY(-1px); }

  /* ── Stat Cards ── */
  .aqi-stat-card {
    background: var(--bg-card); border: 1px solid var(--slate-200);
    border-radius: var(--radius-lg); padding: 18px; box-shadow: var(--shadow-sm);
    display: flex; flex-direction: column; gap: 6px; position: relative; overflow: hidden;
  }
  .aqi-stat-card::before {
    content: ""; position: absolute; left: 0; top: 0; bottom: 0; width: 4px;
    background: var(--card-color, var(--blue-500));
  }
  .asc-label { font-size: .75rem; color: var(--slate-500); font-weight: 600; text-transform: uppercase; }
  .asc-val { font-family: var(--font-mono); font-size: 1.8rem; font-weight: 700; color: var(--slate-800); }
  .asc-sub { font-size: .7rem; color: var(--slate-400); }

  /* ── Calendar Heatmap ── */
  .heatmap-grid {
    display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; margin-top: 10px;
  }
  .hm-day-lbl { text-align: center; font-size: .65rem; color: var(--slate-400); font-weight: 600; text-transform: uppercase; margin-bottom: 4px; }
  .hm-cell {
    aspect-ratio: 1; border-radius: 6px; display: flex; flex-direction: column; align-items: center; justify-content: center;
    color: white; font-family: var(--font-mono); font-size: .8rem; font-weight: 600; cursor: pointer; transition: transform 0.1s;
    box-shadow: inset 0 0 0 1px rgba(0,0,0,0.05);
  }
  .hm-cell:hover { transform: scale(1.05); box-shadow: 0 4px 10px rgba(0,0,0,0.15); z-index: 2; position: relative; }
  .hm-empty { background: transparent; box-shadow: none; pointer-events: none; }
  .hm-date { font-size: .55rem; opacity: 0.8; margin-bottom: 2px; }

  /* Heatmap Colors */
  .hm-good { background: var(--aqi-good); }
  .hm-mod  { background: var(--aqi-moderate); }
  .hm-sen  { background: var(--aqi-sensitive); }
  .hm-unh  { background: var(--aqi-unhealthy); }
  .hm-haz  { background: var(--aqi-very); }
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
    <h2>🌬️ Historical AQI & PM2.5 Data</h2>
    <div class="section-line"></div>
    <div class="section-meta">วิเคราะห์สถิติฝุ่นละอองระยะยาว</div>
  </div>

  <div class="history-controls">
    <div class="hc-group">
      <span class="hc-label">Node:</span>
      <select class="hc-select" id="nodeSelect">
        <option value="all">All Nodes (Average)</option>
        <option value="1">Node-01 (แปลง A)</option>
        <option value="2">Node-02 (แปลง B)</option>
        <option value="3">Node-03 (แปลง C)</option>
      </select>
    </div>
    <div class="hc-group">
      <span class="hc-label">Month:</span>
      <input type="month" class="hc-input" id="monthSelect" value="2026-03">
    </div>
    <button class="btn-primary" onclick="generateMockHistory()">🔍 Apply Filter</button>
    <button class="btn-export">📥 Export Monthly Data</button>
  </div>

  <div class="grid-2" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 20px;">
    <div class="aqi-stat-card" style="--card-color: var(--blue-500)">
      <div class="asc-label">Monthly Average</div>
      <div class="asc-val" id="statAvg">22.4 <span style="font-size: 1rem; color: var(--slate-400)">µg</span></div>
      <div class="asc-sub">ระดับ: ปานกลาง (Moderate)</div>
    </div>
    <div class="aqi-stat-card" style="--card-color: var(--orange-500)">
      <div class="asc-label">Monthly Peak</div>
      <div class="asc-val" id="statPeak">58.2 <span style="font-size: 1rem; color: var(--slate-400)">µg</span></div>
      <div class="asc-sub">วันที่ 14 มี.ค. 2026</div>
    </div>
    <div class="aqi-stat-card" style="--card-color: var(--red-500)">
      <div class="asc-label">Days Exceeded WHO</div>
      <div class="asc-val" id="statExc">12 <span style="font-size: 1rem; color: var(--slate-400)">Days</span></div>
      <div class="asc-sub">เกินค่ามาตรฐาน (>15 µg/m³)</div>
    </div>
    <div class="aqi-stat-card" style="--card-color: var(--green-500)">
      <div class="asc-label">Data Coverage</div>
      <div class="asc-val">99.8 <span style="font-size: 1rem; color: var(--slate-400)">%</span></div>
      <div class="asc-sub">ข้อมูลสมบูรณ์เกือบ 100%</div>
    </div>
  </div>

  <div class="grid-2-1">
    <div class="panel">
      <div class="panel-header">
        <div class="panel-dot pd-blue"></div>
        <div class="panel-title">Daily AQI Heatmap</div>
        <div class="panel-sub" id="heatmapMonthLbl">March 2026</div>
      </div>
      <div class="panel-body">
        <div class="heatmap-grid" id="heatmapDaysLbl">
          <div class="hm-day-lbl">Sun</div><div class="hm-day-lbl">Mon</div><div class="hm-day-lbl">Tue</div>
          <div class="hm-day-lbl">Wed</div><div class="hm-day-lbl">Thu</div><div class="hm-day-lbl">Fri</div><div class="hm-day-lbl">Sat</div>
        </div>
        <div class="heatmap-grid" id="heatmapContainer">
          </div>
        <div style="display:flex; justify-content:center; gap:12px; margin-top:16px; font-size:.65rem; color:var(--slate-500);">
          <div style="display:flex;align-items:center;gap:4px;"><div style="width:10px;height:10px;background:var(--aqi-good);border-radius:2px;"></div> ดี</div>
          <div style="display:flex;align-items:center;gap:4px;"><div style="width:10px;height:10px;background:var(--aqi-moderate);border-radius:2px;"></div> ปานกลาง</div>
          <div style="display:flex;align-items:center;gap:4px;"><div style="width:10px;height:10px;background:var(--aqi-sensitive);border-radius:2px;"></div> เริ่มมีผลกระทบ</div>
          <div style="display:flex;align-items:center;gap:4px;"><div style="width:10px;height:10px;background:var(--aqi-unhealthy);border-radius:2px;"></div> มีผลกระทบ</div>
        </div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-header">
        <div class="panel-dot pd-purple"></div>
        <div class="panel-title">Air Quality Days</div>
      </div>
      <div class="panel-body" style="display:flex; flex-direction:column; align-items:center; justify-content:center; height: 100%;">
        <div style="height:200px; width:100%; position:relative;"><canvas id="aqiPieChart"></canvas></div>
      </div>
    </div>
  </div>

  <div class="panel" style="margin-bottom: 20px;">
    <div class="panel-header">
      <div class="panel-dot pd-orange"></div>
      <div class="panel-title">30-Day PM2.5 Trend</div>
      <div class="panel-sub">Daily Average & Max (µg/m³)</div>
    </div>
    <div class="panel-body">
      <div style="height:300px; position:relative;"><canvas id="historyChart"></canvas></div>
    </div>
  </div>

<?php
/* ── 5. Footer + Shared JS ───────────────────────── */
require_once '_footer.php';
?>

<script>
// ════════════════════════════════════
// AQI HELPER
// ════════════════════════════════════
function getAQIClass(val) {
  if (val <= 12) return 'hm-good';
  if (val <= 35.4) return 'hm-mod';
  if (val <= 55.4) return 'hm-sen';
  if (val <= 150.4) return 'hm-unh';
  return 'hm-haz';
}
function getAQIColor(val) {
  if (val <= 12) return '#22c55e';
  if (val <= 35.4) return '#eab308';
  if (val <= 55.4) return '#f97316';
  if (val <= 150.4) return '#ef4444';
  return '#a855f7';
}

// ════════════════════════════════════
// MOCKUP DATA GENERATOR
// ════════════════════════════════════
let historyChartInstance = null;
let pieChartInstance = null;

function generateMockHistory() {
  const monthInput = document.getElementById('monthSelect').value; // e.g., "2026-03"
  const [year, month] = monthInput.split('-');
  const daysInMonth = new Date(year, month, 0).getDate();
  const firstDay = new Date(year, month - 1, 1).getDay(); // 0 = Sun, 1 = Mon ...
  
  // สร้างข้อมูลจำลองของเดือนนั้นๆ
  let mockData = [];
  let totalPM25 = 0;
  let maxPM25 = 0;
  let exceedDays = 0;
  let counts = { good: 0, mod: 0, sen: 0, unh: 0, haz: 0 };

  // Base PM2.5 value เพื่อให้กราฟดูมี Trend ต่อเนื่อง
  let baseVal = 15; 
  
  for (let i = 1; i <= daysInMonth; i++) {
    // สุ่มแบบมี Pattern (ให้คล้ายของจริงที่มีขึ้นมีลงตามฤดู)
    baseVal += (Math.random() * 10 - 5);
    if(baseVal < 5) baseVal = 5;
    if(baseVal > 80) baseVal = 80;
    
    let avg = baseVal;
    let max = avg + (Math.random() * 15);
    
    mockData.push({ day: i, avg: avg, max: max });
    
    totalPM25 += avg;
    if (max > maxPM25) maxPM25 = max;
    if (avg > 15) exceedDays++; // WHO guideline (annual 5, 24h 15)
    
    // นับจำนวนวันในแต่ละระดับ (อิงตามค่าเฉลี่ย)
    if(avg <= 12) counts.good++;
    else if(avg <= 35.4) counts.mod++;
    else if(avg <= 55.4) counts.sen++;
    else if(avg <= 150.4) counts.unh++;
    else counts.haz++;
  }

  // อัปเดต Stat Cards
  const avgMonthly = totalPM25 / daysInMonth;
  document.getElementById('statAvg').innerHTML = `${avgMonthly.toFixed(1)} <span style="font-size: 1rem; color: var(--slate-400)">µg</span>`;
  document.getElementById('statPeak').innerHTML = `${maxPM25.toFixed(1)} <span style="font-size: 1rem; color: var(--slate-400)">µg</span>`;
  document.getElementById('statExc').innerHTML = `${exceedDays} <span style="font-size: 1rem; color: var(--slate-400)">Days</span>`;
  
  const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
  document.getElementById('heatmapMonthLbl').innerText = `${monthNames[parseInt(month)-1]} ${year}`;

  // 1. วาด ปฏิทิน Heatmap
  const hmContainer = document.getElementById('heatmapContainer');
  hmContainer.innerHTML = '';
  
  // กล่องว่าง (วันก่อนเริ่มเดือน)
  for (let i = 0; i < firstDay; i++) {
    hmContainer.innerHTML += `<div class="hm-cell hm-empty"></div>`;
  }
  // กล่องวันที่
  mockData.forEach(d => {
    let cls = getAQIClass(d.avg);
    hmContainer.innerHTML += `
      <div class="hm-cell ${cls}" title="Date: ${d.day} ${monthNames[parseInt(month)-1]}\nAvg: ${d.avg.toFixed(1)} µg/m³\nMax: ${d.max.toFixed(1)} µg/m³">
        <span class="hm-date">${d.day}</span>
        <span>${Math.round(d.avg)}</span>
      </div>
    `;
  });

  // 2. วาด กราฟแท่ง 30 วัน (Line + Bar)
  const labels = mockData.map(d => d.day);
  const avgData = mockData.map(d => d.avg.toFixed(1));
  const maxData = mockData.map(d => d.max.toFixed(1));
  const barColors = mockData.map(d => getAQIColor(d.avg) + 'B3'); // B3 = 70% opacity
  const barBorders = mockData.map(d => getAQIColor(d.avg));

  if (historyChartInstance) historyChartInstance.destroy();
  historyChartInstance = new Chart(document.getElementById('historyChart').getContext('2d'), {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [
        {
          type: 'line', label: 'Max PM2.5 (Peak)', data: maxData,
          borderColor: '#94a3b8', borderDash: [5, 5], borderWidth: 2, pointRadius: 3, fill: false, tension: 0.3
        },
        {
          type: 'bar', label: 'Avg PM2.5', data: avgData,
          backgroundColor: barColors, borderColor: barBorders, borderWidth: 1, borderRadius: 4
        }
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: { legend: { position: 'top', labels: {font: {family: 'DM Sans'}} } },
      scales: {
        x: { grid: { display: false }, ticks: {font: {family: 'JetBrains Mono'}} },
        y: { grid: { color: 'rgba(203,213,225,.4)' }, ticks: {font: {family: 'JetBrains Mono'}} }
      }
    }
  });

  // 3. วาด กราฟโดนัท (AQI Days)
  if (pieChartInstance) pieChartInstance.destroy();
  pieChartInstance = new Chart(document.getElementById('aqiPieChart').getContext('2d'), {
    type: 'doughnut',
    data: {
      labels: ['ดี (Good)', 'ปานกลาง (Mod)', 'กลุ่มเสี่ยง (USG)', 'ไม่ดี (Unh)', 'อันตราย (Haz)'],
      datasets: [{
        data: [counts.good, counts.mod, counts.sen, counts.unh, counts.haz],
        backgroundColor: ['#22c55e', '#eab308', '#f97316', '#ef4444', '#a855f7'],
        borderWidth: 2, hoverOffset: 5
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false, cutout: '65%',
      plugins: { 
        legend: { position: 'right', labels: { boxWidth: 12, padding: 10, font: {family: 'DM Sans', size: 11} } } 
      }
    }
  });
}

// โหลดข้อมูลครั้งแรกเมื่อเปิดหน้าเว็บ
generateMockHistory();
</script>

</main>
</body>
</html>