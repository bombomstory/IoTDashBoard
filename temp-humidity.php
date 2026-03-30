<?php
/* ── 1. Load global config ───────────────────────── */
require_once '_vars.php';

/* ── 2. Page-specific options ────────────────────── */
$page_title = "IoT Dashboard - Temp & Humidity";
$use_leaflet = false;

// CSS เฉพาะหน้า Temp & Humidity
$extra_css = '
<style>
  /* ── Special Stat Cards ── */
  .th-stat-card {
    background: var(--bg-card); border: 1px solid var(--slate-200);
    border-radius: var(--radius-lg); padding: 20px;
    display: flex; align-items: center; justify-content: space-between;
    box-shadow: var(--shadow-sm); transition: transform 0.2s;
    position: relative; overflow: hidden;
  }
  .th-stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
  .th-stat-card::after {
    content: ""; position: absolute; bottom: 0; left: 0; right: 0; height: 4px;
    background: var(--card-grad, var(--slate-200));
  }
  .tsc-temp { --card-grad: linear-gradient(90deg, #f97316, #ef4444); }
  .tsc-humi { --card-grad: linear-gradient(90deg, #3b82f6, #06b6d4); }
  .tsc-heat { --card-grad: linear-gradient(90deg, #eab308, #ef4444); }
  .tsc-dew  { --card-grad: linear-gradient(90deg, #06b6d4, #10b981); }

  .th-info { display: flex; flex-direction: column; gap: 4px; }
  .th-lbl { font-size: .75rem; color: var(--slate-500); font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }
  .th-val { font-family: var(--font-mono); font-size: 2rem; font-weight: 700; color: var(--slate-800); line-height: 1; }
  .th-unit { font-size: 1rem; color: var(--slate-400); font-weight: 500; }
  .th-sub { font-size: .7rem; font-family: var(--font-mono); font-weight: 600; margin-top: 4px; }

  .th-icon-bg {
    width: 60px; height: 60px; border-radius: 50%;
    display: grid; place-items: center; font-size: 1.8rem;
    background: var(--slate-50); box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
  }

  /* ── Custom Table Data ── */
  .status-tag {
    display: inline-block; padding: 4px 10px; border-radius: 6px;
    font-size: .7rem; font-weight: 700; font-family: var(--font-ui);
  }
  .tag-optimal { background: var(--green-100); color: var(--green-600); }
  .tag-warning { background: var(--yellow-100); color: var(--yellow-700); }
  .tag-danger  { background: var(--red-100); color: var(--red-600); }

  .progress-wrap { display: flex; align-items: center; gap: 8px; }
  .progress-bar { flex: 1; height: 6px; background: var(--slate-100); border-radius: 3px; overflow: hidden; min-width: 60px; }
  .progress-fill { height: 100%; border-radius: 3px; transition: width 0.5s; }
  .progress-val { font-family: var(--font-mono); font-size: .75rem; font-weight: 600; width: 35px; text-align: right; }
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
    <h2>🌡️ Temperature & Humidity Details</h2>
    <div class="section-line"></div>
    <div class="section-meta">พรศิริฟาร์มสุข Sensor Network (DHT22)</div>
  </div>

  <div class="grid-2" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 24px;">
    <div class="th-stat-card tsc-temp">
      <div class="th-info">
        <div class="th-lbl">Avg Temperature</div>
        <div class="th-val" id="avgTemp">--.- <span class="th-unit">°C</span></div>
        <div class="th-sub" style="color: var(--orange-500)">Max: <span id="maxTemp">--.-</span> °C</div>
      </div>
      <div class="th-icon-bg" style="color: var(--orange-500)">🌡️</div>
    </div>
    
    <div class="th-stat-card tsc-humi">
      <div class="th-info">
        <div class="th-lbl">Avg Humidity</div>
        <div class="th-val" id="avgHumi">--.- <span class="th-unit">%</span></div>
        <div class="th-sub" style="color: var(--blue-500)">Min: <span id="minHumi">--.-</span> %</div>
      </div>
      <div class="th-icon-bg" style="color: var(--blue-500)">💧</div>
    </div>

    <div class="th-stat-card tsc-heat">
      <div class="th-info">
        <div class="th-lbl">Max Heat Index</div>
        <div class="th-val" id="maxHeatIndex">--.- <span class="th-unit">°C</span></div>
        <div class="th-sub" style="color: var(--red-500)">ความรู้สึกจริง (Feels Like)</div>
      </div>
      <div class="th-icon-bg" style="color: var(--red-500)">🔥</div>
    </div>

    <div class="th-stat-card tsc-dew">
      <div class="th-info">
        <div class="th-lbl">Dew Point (Avg)</div>
        <div class="th-val" id="avgDew">--.- <span class="th-unit">°C</span></div>
        <div class="th-sub" style="color: var(--green-600)">จุดน้ำค้าง (โอกาสเกิดเชื้อรา)</div>
      </div>
      <div class="th-icon-bg" style="color: var(--green-500)">🌿</div>
    </div>
  </div>

  <div class="grid-2-1">
    <div class="panel">
      <div class="panel-header">
        <div class="panel-dot pd-orange"></div>
        <div class="panel-title">24-Hour Microclimate Trend</div>
        <div class="panel-sub">อุณหภูมิ vs ความชื้น</div>
      </div>
      <div class="panel-body">
        <div style="height:280px; position:relative;"><canvas id="thTrendChart"></canvas></div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-header">
        <div class="panel-dot pd-blue"></div>
        <div class="panel-title">Current Node Comparison</div>
      </div>
      <div class="panel-body">
        <div style="height:280px; position:relative;"><canvas id="thBarChart"></canvas></div>
      </div>
    </div>
  </div>

  <div class="panel" style="margin-top: 16px;">
    <div class="panel-header">
      <div class="panel-dot pd-green"></div>
      <div class="panel-title">Real-time Node Status</div>
      <div class="panel-sub">อัปเดตทุก 3 วินาที</div>
    </div>
    <div class="panel-body-sm" style="overflow-x: auto;">
      <table class="device-table" style="min-width: 600px;">
        <thead>
          <tr>
            <th>Node / Location</th>
            <th style="width: 25%">Temperature (°C)</th>
            <th style="width: 25%">Humidity (%)</th>
            <th>Heat Index (°C)</th>
            <th>Environment Status</th>
          </tr>
        </thead>
        <tbody id="thTableBody">
          </tbody>
      </table>
    </div>
  </div>

<?php
/* ── 5. Footer + Shared JS ───────────────────────── */
require_once '_footer.php';
?>

<script>
// ════════════════════════════════════
// 1. DATA & HELPER FUNCTIONS
// ════════════════════════════════════
const thNodes = [
  { id: 'Node-01', loc: 'โรงเรือน 1', temp: 28.4, humi: 62 },
  { id: 'Node-02', loc: 'แปลงเปิด B', temp: 31.2, humi: 55 },
  { id: 'Node-03', loc: 'โซนเก็บเกี่ยว', temp: 29.5, humi: 68 },
  { id: 'Node-04', loc: 'โรงเรือน 2', temp: 27.8, humi: 65 },
  { id: 'Node-05', loc: 'โซนเพาะกล้า', temp: 34.5, humi: 45 } // ตั้งใจให้ค่อนข้างร้อนเพื่อโชว์ Warning
];

// สูตรคำนวณ Heat Index แบบง่าย (อ้างอิงจาก NOAA)
function calculateHeatIndex(t, h) {
  // แปลง C เป็น F
  let tf = (t * 9/5) + 32;
  let hi = tf;
  if (tf >= 80) {
    hi = -42.379 + 2.04901523*tf + 10.14333127*h - 0.22475541*tf*h - 0.00683783*tf*tf - 0.05481717*h*h + 0.00122874*tf*tf*h + 0.00085282*tf*h*h - 0.00000199*tf*tf*h*h;
  }
  // แปลง F กลับเป็น C
  return ((hi - 32) * 5/9);
}

// สูตรคำนวณ Dew Point แบบง่าย (Magnus formula)
function calculateDewPoint(t, h) {
  const a = 17.27; const b = 237.7;
  const alpha = ((a * t) / (b + t)) + Math.log(h / 100.0);
  return (b * alpha) / (a - alpha);
}

function getEnvStatus(temp, humi) {
  if (temp > 33 || temp < 15 || humi > 85 || humi < 40) return { cls: 'tag-danger', txt: 'อันตราย (Danger)' };
  if (temp > 30 || temp < 20 || humi > 75 || humi < 50) return { cls: 'tag-warning', txt: 'เฝ้าระวัง (Warning)' };
  return { cls: 'tag-optimal', txt: 'เหมาะสม (Optimal)' };
}

// ════════════════════════════════════
// 2. RENDER COMPONENTS
// ════════════════════════════════════
let thBarChartInstance = null;

function updateUI() {
  let sumTemp = 0, sumHumi = 0, maxT = -99, minH = 999, maxHI = -99, sumDew = 0;
  let labels = [], tempData = [], humiData = [];

  const tableHtml = thNodes.map((n, i) => {
    sumTemp += n.temp; sumHumi += n.humi;
    if (n.temp > maxT) maxT = n.temp;
    if (n.humi < minH) minH = n.humi;

    const hi = calculateHeatIndex(n.temp, n.humi);
    const dp = calculateDewPoint(n.temp, n.humi);
    if (hi > maxHI) maxHI = hi;
    sumDew += dp;

    labels.push(n.id);
    tempData.push(n.temp.toFixed(1));
    humiData.push(n.humi);

    const status = getEnvStatus(n.temp, n.humi);
    
    // คำนวณสีของ Progress Bar
    const tColor = n.temp > 32 ? '#ef4444' : (n.temp > 28 ? '#f97316' : '#22c55e');
    const hColor = n.humi > 80 ? '#06b6d4' : (n.humi < 50 ? '#eab308' : '#3b82f6');
    
    const tPct = Math.min(100, (n.temp / 50) * 100);
    const hPct = n.humi;

    return `
      <tr>
        <td>
          <div style="font-family:var(--font-mono); font-weight:700; color:var(--blue-700)">${n.id}</div>
          <div style="font-size:.7rem; color:var(--slate-500)">${n.loc}</div>
        </td>
        <td>
          <div class="progress-wrap">
            <div class="progress-bar"><div class="progress-fill" style="width:${tPct}%; background:${tColor}"></div></div>
            <div class="progress-val" style="color:${tColor}">${n.temp.toFixed(1)}</div>
          </div>
        </td>
        <td>
          <div class="progress-wrap">
            <div class="progress-bar"><div class="progress-fill" style="width:${hPct}%; background:${hColor}"></div></div>
            <div class="progress-val" style="color:${hColor}">${n.humi.toFixed(0)}</div>
          </div>
        </td>
        <td style="font-family:var(--font-mono); font-weight:600; color:${hi > 35 ? 'var(--red-500)' : 'var(--slate-700)'}">
          ${hi.toFixed(1)} °C
        </td>
        <td><span class="status-tag ${status.cls}">${status.txt}</span></td>
      </tr>
    `;
  }).join('');

  document.getElementById('thTableBody').innerHTML = tableHtml;

  // Update Summary Cards
  const avgT = sumTemp / thNodes.length;
  const avgH = sumHumi / thNodes.length;
  const avgD = sumDew / thNodes.length;
  
  document.getElementById('avgTemp').innerHTML = `${avgT.toFixed(1)} <span class="th-unit">°C</span>`;
  document.getElementById('maxTemp').innerText = maxT.toFixed(1);
  
  document.getElementById('avgHumi').innerHTML = `${avgH.toFixed(1)} <span class="th-unit">%</span>`;
  document.getElementById('minHumi').innerText = minH.toFixed(0);
  
  document.getElementById('maxHeatIndex').innerHTML = `${maxHI.toFixed(1)} <span class="th-unit">°C</span>`;
  document.getElementById('avgDew').innerHTML = `${avgD.toFixed(1)} <span class="th-unit">°C</span>`;

  // Update Bar Chart
  if(thBarChartInstance) {
    thBarChartInstance.data.datasets[0].data = tempData;
    thBarChartInstance.data.datasets[1].data = humiData;
    thBarChartInstance.update();
  } else {
    thBarChartInstance = new Chart(document.getElementById('thBarChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [
          { label: 'Temp (°C)', data: tempData, backgroundColor: 'rgba(249,115,22,0.8)', borderRadius: 4, yAxisID: 'y' },
          { label: 'Humi (%)', data: humiData, backgroundColor: 'rgba(59,130,246,0.8)', borderRadius: 4, yAxisID: 'y1' }
        ]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'top', labels: { font: {family: 'DM Sans'} } } },
        scales: {
          x: { ticks: { font: {family: 'JetBrains Mono'} }, grid: {display: false} },
          y: { type: 'linear', position: 'left', min: 0, max: 50, ticks: {color: '#f97316', font: {family: 'JetBrains Mono'}} },
          y1: { type: 'linear', position: 'right', min: 0, max: 100, grid: {drawOnChartArea: false}, ticks: {color: '#3b82f6', font: {family: 'JetBrains Mono'}} }
        }
      }
    });
  }
}

// ════════════════════════════════════
// 3. INIT TREND CHART (MOCK 24H)
// ════════════════════════════════════
const hours = Array.from({length:24}, (_,i) => `${String(i).padStart(2,'0')}:00`);
const histTemp = [26.2,25.8,25.4,25.1,24.9,24.7,25.2,26.8,28.1,29.4,30.6,31.2,31.8,32.1,31.5,30.8,29.9,29.2,28.4,27.8,27.3,26.9,26.5,26.2];
const histHumi = [68,70,72,74,75,76,73,68,63,59,56,54,52,51,53,56,59,62,65,67,68,69,70,69];

new Chart(document.getElementById('thTrendChart').getContext('2d'), {
  type: 'line',
  data: {
    labels: hours,
    datasets: [
      { label: 'Avg Temp (°C)', data: histTemp, borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,0.1)', fill: true, tension: 0.4, yAxisID: 'y', borderWidth: 2, pointRadius: 0, pointHoverRadius: 5 },
      { label: 'Avg Humi (%)', data: histHumi, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', fill: true, tension: 0.4, yAxisID: 'y1', borderWidth: 2, pointRadius: 0, pointHoverRadius: 5 }
    ]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: { legend: { position: 'top', labels: { font: {family: 'DM Sans'} } } },
    scales: {
      x: { grid: { color: 'rgba(203,213,225,.3)' }, ticks: { font: {family: 'JetBrains Mono'}, maxTicksLimit: 12 } },
      y: { type: 'linear', position: 'left', grid: { color: 'rgba(203,213,225,.3)' }, ticks: { color: '#f97316', font: {family: 'JetBrains Mono'} } },
      y1: { type: 'linear', position: 'right', grid: { drawOnChartArea: false }, ticks: { color: '#3b82f6', font: {family: 'JetBrains Mono'} } }
    }
  }
});

// ════════════════════════════════════
// 4. REAL-TIME SIMULATION
// ════════════════════════════════════
function simulateTHRealtime() {
  thNodes.forEach(n => {
    n.temp = Math.max(15, Math.min(45, n.temp + (Math.random() * 0.6 - 0.3)));
    n.humi = Math.max(30, Math.min(90, n.humi + (Math.random() * 1.5 - 0.75)));
  });
  updateUI();
}

// โหลดครั้งแรก และตั้งเวลาอัปเดตทุก 3 วินาที
updateUI();
setInterval(simulateTHRealtime, 3000);

</script>

</main>
</body>
</html>