<?php
/* ── 1. Load global config ───────────────────────── */
require_once '_vars.php';

/* ── 2. Page-specific options ────────────────────── */
$page_title = "IoT Dashboard - Light & UV";
$use_leaflet = false;

// CSS เฉพาะหน้า Light & UV
$extra_css = '
<style>
  /* ── Special Stat Cards ── */
  .lu-stat-card {
    background: var(--bg-card); border: 1px solid var(--slate-200);
    border-radius: var(--radius-lg); padding: 20px;
    display: flex; align-items: center; justify-content: space-between;
    box-shadow: var(--shadow-sm); transition: transform 0.2s;
    position: relative; overflow: hidden;
  }
  .lu-stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
  .lu-stat-card::after {
    content: ""; position: absolute; bottom: 0; left: 0; right: 0; height: 4px;
    background: var(--card-grad, var(--slate-200));
  }
  .lsc-lux  { --card-grad: linear-gradient(90deg, #eab308, #f97316); }
  .lsc-uv   { --card-grad: linear-gradient(90deg, #a855f7, #ec4899); }
  .lsc-dli  { --card-grad: linear-gradient(90deg, #22c55e, #10b981); }
  .lsc-lamp { --card-grad: linear-gradient(90deg, #f59e0b, #ef4444); }

  .lu-info { display: flex; flex-direction: column; gap: 4px; }
  .lu-lbl { font-size: .75rem; color: var(--slate-500); font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }
  .lu-val { font-family: var(--font-mono); font-size: 2rem; font-weight: 700; color: var(--slate-800); line-height: 1; }
  .lu-unit { font-size: 1rem; color: var(--slate-400); font-weight: 500; }
  .lu-sub { font-size: .7rem; font-family: var(--font-mono); font-weight: 600; margin-top: 4px; }

  .lu-icon-bg {
    width: 60px; height: 60px; border-radius: 50%;
    display: grid; place-items: center; font-size: 1.8rem;
    background: var(--slate-50); box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
  }

  /* ── UV & Lamp Badges ── */
  .uv-badge {
    display: inline-block; padding: 4px 10px; border-radius: 6px;
    font-size: .7rem; font-weight: 700; font-family: var(--font-ui); color: white;
  }
  .uv-low { background: var(--green-500); }
  .uv-mod { background: var(--yellow-500); }
  .uv-high { background: var(--orange-500); }
  .uv-very { background: var(--red-500); }
  .uv-ext { background: var(--purple-500); }

  .lamp-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 12px; border-radius: 20px; font-size: .75rem; font-weight: 700;
    font-family: var(--font-ui); transition: all 0.3s;
  }
  .lamp-on { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; box-shadow: 0 0 12px rgba(245,158,11,.4); }
  .lamp-off { background: var(--slate-100); color: var(--slate-500); border: 1px solid var(--slate-200); }
  .lamp-icon { font-size: .9rem; }
  .lamp-on .lamp-icon { animation: light-flicker 2s infinite alternate; text-shadow: 0 0 8px #f59e0b; }
  @keyframes light-flicker { 0% {opacity: 0.8;} 100% {opacity: 1;} }

  /* ── Custom Progress (Lux) ── */
  .lux-wrap { display: flex; align-items: center; gap: 10px; }
  .lux-bar { flex: 1; height: 8px; background: var(--slate-100); border-radius: 4px; overflow: hidden; min-width: 80px; position: relative; }
  .lux-fill { height: 100%; border-radius: 4px; transition: width 0.5s, background-color 0.5s; }
  .lux-val { font-family: var(--font-mono); font-size: .85rem; font-weight: 700; width: 50px; text-align: right; color: var(--slate-700); }
  
  .lux-marker { position: absolute; left: 10%; top: 0; bottom: 0; width: 2px; background: rgba(59,130,246,0.6); z-index: 2; } /* ขีด 1000 Lux */
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
    <h2>☀️ Light Intensity & UV Index</h2>
    <div class="section-line"></div>
    <div class="section-meta">Smart Farm System · พรศิริฟาร์มสุข</div>
  </div>

  <div class="grid-2" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 24px;">
    <div class="lu-stat-card lsc-lux">
      <div class="lu-info">
        <div class="lu-lbl">Avg Light (Lux)</div>
        <div class="lu-val" id="avgLux">-- <span class="lu-unit">lx</span></div>
        <div class="lu-sub" style="color: var(--orange-500)">Max: <span id="maxLux">--</span> lx</div>
      </div>
      <div class="lu-icon-bg" style="color: var(--yellow-500)">☀️</div>
    </div>
    
    <div class="lu-stat-card lsc-uv">
      <div class="lu-info">
        <div class="lu-lbl">Max UV Index</div>
        <div class="lu-val" id="maxUV">--.- <span class="lu-unit">UVI</span></div>
        <div class="lu-sub" id="uvStatusTxt" style="color: var(--purple-500)">ระดับรังสี UV</div>
      </div>
      <div class="lu-icon-bg" style="color: var(--purple-500)">😎</div>
    </div>

    <div class="lu-stat-card lsc-dli">
      <div class="lu-info">
        <div class="lu-lbl">Est. DLI</div>
        <div class="lu-val">14.2 <span class="lu-unit">mol</span></div>
        <div class="lu-sub" style="color: var(--green-600)">Daily Light Integral</div>
      </div>
      <div class="lu-icon-bg" style="color: var(--green-500)">🌱</div>
    </div>

    <div class="lu-stat-card lsc-lamp">
      <div class="lu-info">
        <div class="lu-lbl">Grow Lights ON</div>
        <div class="lu-val" id="activeLamps">0 <span class="lu-unit">/ 5</span></div>
        <div class="lu-sub" style="color: var(--red-500)">เปิดไฟเสริมแสงอัตโนมัติ</div>
      </div>
      <div class="lu-icon-bg" style="color: var(--orange-400)">💡</div>
    </div>
  </div>

  <div class="grid-2-1">
    <div class="panel">
      <div class="panel-header">
        <div class="panel-dot pd-yellow"></div>
        <div class="panel-title">Light & UV Trend (24h)</div>
        <div class="panel-sub">Dual Axis</div>
      </div>
      <div class="panel-body">
        <div style="height:280px; position:relative;"><canvas id="luTrendChart"></canvas></div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-header">
        <div class="panel-dot pd-purple"></div>
        <div class="panel-title">UV Index Distribution</div>
      </div>
      <div class="panel-body">
        <div style="height:280px; position:relative;"><canvas id="uvPolarChart"></canvas></div>
      </div>
    </div>
  </div>

  <div class="panel" style="margin-top: 16px;">
    <div class="panel-header">
      <div class="panel-dot pd-orange"></div>
      <div class="panel-title">Live Light & Grow Lamp Status</div>
      <div class="panel-sub">Auto-lamp threshold: < 1,000 Lux</div>
    </div>
    <div class="panel-body-sm" style="overflow-x: auto;">
      <table class="device-table" style="min-width: 650px;">
        <thead>
          <tr>
            <th>Node / Location</th>
            <th style="width: 35%">Light Intensity (Lux)</th>
            <th>UV Index</th>
            <th>Grow Light Status</th>
            <th>Manual Control</th>
          </tr>
        </thead>
        <tbody id="luTableBody">
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
const luNodes = [
  { id: 'Node-01', loc: 'แปลง A (โรงเรือน 1)', lux: 4500, uv: 4.2, isLampOn: false },
  { id: 'Node-02', loc: 'แปลง B (กลางแจ้ง)',   lux: 8500, uv: 7.5, isLampOn: false },
  { id: 'Node-03', loc: 'แปลง C (มุมอับ)',     lux: 850,  uv: 1.2, isLampOn: true }, // แสงน้อย ให้ไฟเปิด
  { id: 'Node-04', loc: 'แปลง D (โรงเรือน 2)', lux: 3200, uv: 3.5, isLampOn: false },
  { id: 'Node-05', loc: 'แปลง E (ทางเข้า)',    lux: 6200, uv: 5.8, isLampOn: false }
];

function getUVStatus(val) {
  if (val >= 11) return { txt: 'Extreme', cls: 'uv-ext' };
  if (val >= 8)  return { txt: 'Very High', cls: 'uv-very' };
  if (val >= 6)  return { txt: 'High', cls: 'uv-high' };
  if (val >= 3)  return { txt: 'Moderate', cls: 'uv-mod' };
  return { txt: 'Low', cls: 'uv-low' };
}

// ฟังก์ชัน Toggle ไฟปลูกพืชด้วยปุ่มแบบ Manual (จำลอง)
function toggleLamp(index) {
  luNodes[index].isLampOn = !luNodes[index].isLampOn;
  updateLUUI();
}

// ════════════════════════════════════
// 2. RENDER COMPONENTS
// ════════════════════════════════════
let uvPolarChartInstance = null;

function updateLUUI() {
  let sumLux = 0, maxLux = 0, maxUV = 0, activeCount = 0;
  let polarLabels = [], polarData = [], polarColors = [];

  const tableHtml = luNodes.map((n, i) => {
    sumLux += n.lux;
    if (n.lux > maxLux) maxLux = n.lux;
    if (n.uv > maxUV) maxUV = n.uv;
    if (n.isLampOn) activeCount++;

    const uvStat = getUVStatus(n.uv);
    
    // ตั้ง Max Lux ที่ 10000 สำหรับสเกล Progress Bar
    const lPct = Math.min(100, (n.lux / 10000) * 100);
    const lColor = n.lux < 1000 ? '#94a3b8' : (n.lux < 5000 ? '#eab308' : '#f97316');

    // เก็บข้อมูลลงกราฟ Polar
    polarLabels.push(n.id);
    polarData.push(n.uv.toFixed(1));
    
    let pColor = '#22c55e'; // Green
    if(n.uv >= 8) pColor = '#ef4444';
    else if(n.uv >= 6) pColor = '#f97316';
    else if(n.uv >= 3) pColor = '#eab308';
    polarColors.push(pColor + '99'); // เติม Alpha 60%

    return `
      <tr>
        <td>
          <div style="font-family:var(--font-mono); font-weight:700; color:var(--blue-700)">${n.id}</div>
          <div style="font-size:.7rem; color:var(--slate-500)">${n.loc}</div>
        </td>
        <td>
          <div class="lux-wrap">
            <div class="lux-bar">
              <div class="lux-marker" title="1,000 Lux Threshold"></div>
              <div class="lux-fill" style="width:${lPct}%; background:${lColor}"></div>
            </div>
            <div class="lux-val">${Math.round(n.lux).toLocaleString()}</div>
          </div>
        </td>
        <td>
          <span class="uv-badge ${uvStat.cls}">${n.uv.toFixed(1)} ${uvStat.txt}</span>
        </td>
        <td>
          <div class="lamp-badge ${n.isLampOn ? 'lamp-on' : 'lamp-off'}">
            <span class="lamp-icon">${n.isLampOn ? '💡' : '🌑'}</span>
            ${n.isLampOn ? 'LAMP ON' : 'LAMP OFF'}
          </div>
        </td>
        <td>
          <button style="background:var(--bg-card); border:1px solid var(--slate-300); padding:4px 10px; border-radius:4px; font-size:.7rem; font-family:var(--font-ui); cursor:pointer; color:var(--slate-600);" onclick="toggleLamp(${i})">
            ${n.isLampOn ? 'Turn Off' : 'Turn On'}
          </button>
        </td>
      </tr>
    `;
  }).join('');

  document.getElementById('luTableBody').innerHTML = tableHtml;

  // Update Summary Cards
  const avgL = sumLux / luNodes.length;
  document.getElementById('avgLux').innerHTML = `${Math.round(avgL).toLocaleString()} <span class="lu-unit">lx</span>`;
  document.getElementById('maxLux').innerText = Math.round(maxLux).toLocaleString();
  
  document.getElementById('maxUV').innerHTML = `${maxUV.toFixed(1)} <span class="lu-unit">UVI</span>`;
  const maxUVStat = getUVStatus(maxUV);
  document.getElementById('uvStatusTxt').innerText = maxUVStat.txt;
  document.getElementById('uvStatusTxt').className = 'lu-sub ' + maxUVStat.cls.replace('uv-', 'text-'); // ปรับสี Text คร่าวๆ
  
  document.getElementById('activeLamps').innerHTML = `${activeCount} <span class="lu-unit">/ ${luNodes.length}</span>`;

  // Update Polar Chart
  if(uvPolarChartInstance) {
    uvPolarChartInstance.data.datasets[0].data = polarData;
    uvPolarChartInstance.data.datasets[0].backgroundColor = polarColors;
    uvPolarChartInstance.update();
  } else {
    uvPolarChartInstance = new Chart(document.getElementById('uvPolarChart').getContext('2d'), {
      type: 'polarArea',
      data: {
        labels: polarLabels,
        datasets: [{
          label: 'UV Index', data: polarData,
          backgroundColor: polarColors,
          borderWidth: 1
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        scales: { r: { ticks: { display: false } } },
        plugins: { legend: { position: 'right', labels: {font: {family: 'DM Sans', size: 10}} } }
      }
    });
  }
}

// ════════════════════════════════════
// 3. INIT TREND CHART (MOCK 24H)
// ════════════════════════════════════
const hours = Array.from({length:24}, (_,i) => `${String(i).padStart(2,'0')}:00`);
// จำลองแสงพระอาทิตย์ขึ้น-ตก
const histLux = [0,0,0,0,0,0,800,2500,4800,7200,8800,9500,9800,9200,7800,5500,3200,900,0,0,0,0,0,0];
const histUV  = [0,0,0,0,0,0,0.5, 1.2, 3.5, 5.8, 7.2, 8.5, 8.8, 8.0, 6.5, 4.2, 1.8, 0.2,0,0,0,0,0,0];

new Chart(document.getElementById('luTrendChart').getContext('2d'), {
  type: 'line',
  data: {
    labels: hours,
    datasets: [
      { label: 'Avg Lux', data: histLux, borderColor: '#eab308', backgroundColor: 'rgba(234,179,8,0.1)', fill: true, tension: 0.4, yAxisID: 'y', borderWidth: 2, pointRadius: 0, pointHoverRadius: 5 },
      { label: 'Avg UV Index', data: histUV, borderColor: '#a855f7', backgroundColor: 'rgba(168,85,247,0.1)', fill: true, tension: 0.4, yAxisID: 'y1', borderWidth: 2, pointRadius: 0, pointHoverRadius: 5, borderDash: [5,5] }
    ]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: { legend: { position: 'top', labels: { font: {family: 'DM Sans'} } } },
    scales: {
      x: { grid: { color: 'rgba(203,213,225,.3)' }, ticks: { font: {family: 'JetBrains Mono'}, maxTicksLimit: 12 } },
      y: { type: 'linear', position: 'left', min: 0, max: 12000, grid: { color: 'rgba(203,213,225,.3)' }, ticks: { color: '#eab308', font: {family: 'JetBrains Mono'} } },
      y1: { type: 'linear', position: 'right', min: 0, max: 12, grid: { drawOnChartArea: false }, ticks: { color: '#a855f7', font: {family: 'JetBrains Mono'} } }
    }
  }
});

// ════════════════════════════════════
// 4. REAL-TIME AUTO-GROW LIGHT SIMULATION
// ════════════════════════════════════
function simulateLightRealtime() {
  luNodes.forEach(n => {
    // ถ้านอกอาคาร แสงจะแกว่งเยอะกว่าในอาคาร
    const swing = n.loc.includes('กลางแจ้ง') ? 400 : 150;
    
    // แกว่งค่าแสง Lux และ UV เล็กน้อย
    n.lux = Math.max(100, Math.min(10000, n.lux + (Math.random() * swing - (swing/2))));
    n.uv = Math.max(0, Math.min(11, n.uv + (Math.random() * 0.4 - 0.2)));

    // 💡 AUTO GROW LIGHT LOGIC 💡
    // ถ้าแสงธรรมชาติต่ำกว่า 1000 Lux ให้เปิดไฟปลูกพืชช่วย
    if (n.lux < 1000 && !n.isLampOn) {
      n.isLampOn = true;
    } 
    // ถ้าแสงธรรมชาติกลับมาแรงเกิน 2000 Lux ให้ปิดไฟเพื่อประหยัดพลังงาน
    else if (n.lux > 2000 && n.isLampOn) {
      n.isLampOn = false;
    }

    // ถ้าเปิดไฟปลูกพืชอยู่ ค่าความสว่าง Lux รวมจะเพิ่มขึ้นมาชดเชย (+ประมาณ 2500 Lux)
    if(n.isLampOn && n.lux < 3000) {
      n.lux += (Math.random() * 100 + 2400); 
      // สังเกตว่าระบบจะไม่เปิดไฟซ้ำซ้อน เพราะเช็ก Flag isLampOn แล้ว
    }
  });
  
  updateLUUI();
}

// โหลดครั้งแรก และตั้งเวลาอัปเดตทุก 2.5 วินาที
updateLUUI();
setInterval(simulateLightRealtime, 2500);

</script>

</main>
</body>
</html>