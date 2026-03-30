<?php
/* ── 1. Load global config ───────────────────────── */
require_once '_vars.php';

/* ── 2. Page-specific options ────────────────────── */
$page_title = "IoT Dashboard - Soil & Water";
$use_leaflet = false;

// CSS เฉพาะหน้า Soil & Water
$extra_css = '
<style>
  /* ── Special Stat Cards ── */
  .sw-stat-card {
    background: var(--bg-card); border: 1px solid var(--slate-200);
    border-radius: var(--radius-lg); padding: 20px;
    display: flex; align-items: center; justify-content: space-between;
    box-shadow: var(--shadow-sm); transition: transform 0.2s;
    position: relative; overflow: hidden;
  }
  .sw-stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
  .sw-stat-card::after {
    content: ""; position: absolute; bottom: 0; left: 0; right: 0; height: 4px;
    background: var(--card-grad, var(--slate-200));
  }
  .ssc-soil { --card-grad: linear-gradient(90deg, #22c55e, #16a34a); }
  .ssc-dry  { --card-grad: linear-gradient(90deg, #f97316, #ef4444); }
  .ssc-pump { --card-grad: linear-gradient(90deg, #3b82f6, #0ea5e9); }
  .ssc-drop { --card-grad: linear-gradient(90deg, #0ea5e9, #8b5cf6); }

  .sw-info { display: flex; flex-direction: column; gap: 4px; }
  .sw-lbl { font-size: .75rem; color: var(--slate-500); font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }
  .sw-val { font-family: var(--font-mono); font-size: 2rem; font-weight: 700; color: var(--slate-800); line-height: 1; }
  .sw-unit { font-size: 1rem; color: var(--slate-400); font-weight: 500; }
  .sw-sub { font-size: .7rem; font-family: var(--font-mono); font-weight: 600; margin-top: 4px; }

  .sw-icon-bg {
    width: 60px; height: 60px; border-radius: 50%;
    display: grid; place-items: center; font-size: 1.8rem;
    background: var(--slate-50); box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
  }

  /* ── Pump Status Badges ── */
  .pump-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 12px; border-radius: 20px; font-size: .75rem; font-weight: 700;
    font-family: var(--font-ui); transition: all 0.3s;
  }
  .pump-on { background: var(--blue-100); color: var(--blue-600); box-shadow: 0 0 10px rgba(59,130,246,.4); }
  .pump-off { background: var(--slate-100); color: var(--slate-500); }
  .pump-dot { width: 8px; height: 8px; border-radius: 50%; background: currentColor; }
  .pump-on .pump-dot { animation: pump-pulse 1s infinite alternate; }
  @keyframes pump-pulse { 0% {transform: scale(0.8); opacity: 0.5;} 100% {transform: scale(1.2); opacity: 1;} }

  /* ── Custom Progress (Soil Moisture) ── */
  .moisture-wrap { display: flex; align-items: center; gap: 10px; }
  .moisture-bar { flex: 1; height: 8px; background: var(--slate-100); border-radius: 4px; overflow: hidden; min-width: 80px; position: relative; }
  .moisture-fill { height: 100%; border-radius: 4px; transition: width 0.5s, background-color 0.5s; }
  .moisture-val { font-family: var(--font-mono); font-size: .85rem; font-weight: 700; width: 40px; text-align: right; }
  
  /* ขีดเตือนระดับน้ำต่ำ (30%) บน Bar */
  .moisture-marker { position: absolute; left: 30%; top: 0; bottom: 0; width: 2px; background: rgba(239,68,68,0.5); z-index: 2; }
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
    <h2>🌱 Soil Moisture & Irrigation Control</h2>
    <div class="section-line"></div>
    <div class="section-meta">Smart Farm System · พรศิริฟาร์มสุข</div>
  </div>

  <div class="grid-2" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 24px;">
    <div class="sw-stat-card ssc-soil">
      <div class="sw-info">
        <div class="sw-lbl">Avg Soil Moisture</div>
        <div class="sw-val" id="avgSoil">--.- <span class="sw-unit">%</span></div>
        <div class="sw-sub" style="color: var(--green-600)">ความชื้นเฉลี่ยรวม</div>
      </div>
      <div class="sw-icon-bg" style="color: var(--green-500)">🌱</div>
    </div>
    
    <div class="sw-stat-card ssc-dry">
      <div class="sw-info">
        <div class="sw-lbl">Min Moisture (Dry)</div>
        <div class="sw-val" id="minSoil">--.- <span class="sw-unit">%</span></div>
        <div class="sw-sub" style="color: var(--orange-500)">จุดที่แห้งที่สุดในแปลง</div>
      </div>
      <div class="sw-icon-bg" style="color: var(--orange-500)">🏜️</div>
    </div>

    <div class="sw-stat-card ssc-pump">
      <div class="sw-info">
        <div class="sw-lbl">Active Pumps</div>
        <div class="sw-val" id="activePumps">0 <span class="sw-unit">/ 5</span></div>
        <div class="sw-sub" style="color: var(--blue-500)">วาล์วน้ำที่กำลังทำงาน</div>
      </div>
      <div class="sw-icon-bg" style="color: var(--blue-500)">🚰</div>
    </div>

    <div class="sw-stat-card ssc-drop">
      <div class="sw-info">
        <div class="sw-lbl">Est. Water Usage</div>
        <div class="sw-val" id="waterUsage">1,240 <span class="sw-unit">L</span></div>
        <div class="sw-sub" style="color: var(--purple-500)">ปริมาณน้ำที่ใช้ (วันนี้)</div>
      </div>
      <div class="sw-icon-bg" style="color: var(--purple-500)">💧</div>
    </div>
  </div>

  <div class="grid-2-1">
    <div class="panel">
      <div class="panel-header">
        <div class="panel-dot pd-green"></div>
        <div class="panel-title">Soil Moisture Trend (24h)</div>
        <div class="panel-sub">Avg Moisture (%)</div>
      </div>
      <div class="panel-body">
        <div style="height:280px; position:relative;"><canvas id="soilTrendChart"></canvas></div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-header">
        <div class="panel-dot pd-blue"></div>
        <div class="panel-title">Watering Duration</div>
        <div class="panel-sub">ระยะเวลาเปิดปั๊ม (นาที)</div>
      </div>
      <div class="panel-body">
        <div style="height:280px; position:relative;"><canvas id="pumpBarChart"></canvas></div>
      </div>
    </div>
  </div>

  <div class="panel" style="margin-top: 16px;">
    <div class="panel-header">
      <div class="panel-dot pd-blue"></div>
      <div class="panel-title">Live Soil & Valve Status</div>
      <div class="panel-sub">Auto-irrigation threshold: 30%</div>
    </div>
    <div class="panel-body-sm" style="overflow-x: auto;">
      <table class="device-table" style="min-width: 600px;">
        <thead>
          <tr>
            <th>Node / Location</th>
            <th style="width: 40%">Soil Moisture Level (%)</th>
            <th>Status</th>
            <th>Valve / Pump</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="swTableBody">
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
// เพิ่มตัวแปร isPumping เพื่อบอกสถานะเปิดน้ำ
const swNodes = [
  { id: 'Node-01', loc: 'แปลง A (โรงเรือน 1)', soil: 45.2, isPumping: false },
  { id: 'Node-02', loc: 'แปลง B (กลางแจ้ง)',   soil: 32.5, isPumping: false },
  { id: 'Node-03', loc: 'แปลง C (มุมอับ)',     soil: 55.0, isPumping: false },
  { id: 'Node-04', loc: 'แปลง D (โรงเรือน 2)', soil: 48.7, isPumping: false },
  { id: 'Node-05', loc: 'แปลง E (ทางเข้า)',    soil: 28.1, isPumping: true } // ให้มีตัวนึงเริ่มมาแล้วน้ำแห้ง
];

function getMoistureStatus(val) {
  if (val < 30) return { color: '#ef4444', txt: 'แห้งเกินไป (Dry)', cls: 'tag-danger' };
  if (val > 75) return { color: '#3b82f6', txt: 'แฉะเกินไป (Wet)', cls: 'tag-warning' };
  if (val > 60) return { color: '#0ea5e9', txt: 'ชุ่มชื้น (Moist)', cls: 'tag-optimal' };
  return { color: '#22c55e', txt: 'เหมาะสม (Good)', cls: 'tag-optimal' };
}

// ฟังก์ชัน Toggle ปั๊มน้ำด้วยปุ่มแบบ Manual (จำลอง)
function togglePump(index) {
  swNodes[index].isPumping = !swNodes[index].isPumping;
  updateSWUI();
}

// ════════════════════════════════════
// 2. RENDER COMPONENTS
// ════════════════════════════════════
function updateSWUI() {
  let sumSoil = 0, minSoil = 999, activeCount = 0;

  const tableHtml = swNodes.map((n, i) => {
    sumSoil += n.soil;
    if (n.soil < minSoil) minSoil = n.soil;
    if (n.isPumping) activeCount++;

    const status = getMoistureStatus(n.soil);
    const mPct = Math.min(100, Math.max(0, n.soil));

    return `
      <tr>
        <td>
          <div style="font-family:var(--font-mono); font-weight:700; color:var(--blue-700)">${n.id}</div>
          <div style="font-size:.7rem; color:var(--slate-500)">${n.loc}</div>
        </td>
        <td>
          <div class="moisture-wrap">
            <div class="moisture-bar">
              <div class="moisture-marker"></div>
              <div class="moisture-fill" style="width:${mPct}%; background:${status.color}"></div>
            </div>
            <div class="moisture-val" style="color:${status.color}">${n.soil.toFixed(1)}</div>
          </div>
        </td>
        <td>
          <span style="font-size:.75rem; font-weight:600; color:${status.color}">${status.txt}</span>
        </td>
        <td>
          <div class="pump-badge ${n.isPumping ? 'pump-on' : 'pump-off'}">
            <div class="pump-dot"></div>
            ${n.isPumping ? 'VALVE OPEN' : 'VALVE CLOSED'}
          </div>
        </td>
        <td>
          <button style="background:var(--bg-card); border:1px solid var(--slate-300); padding:4px 10px; border-radius:4px; font-size:.7rem; font-family:var(--font-ui); cursor:pointer; color:var(--slate-600);" onclick="togglePump(${i})">
            ${n.isPumping ? 'Stop' : 'Start'}
          </button>
        </td>
      </tr>
    `;
  }).join('');

  document.getElementById('swTableBody').innerHTML = tableHtml;

  // Update Summary Cards
  const avgS = sumSoil / swNodes.length;
  document.getElementById('avgSoil').innerHTML = `${avgS.toFixed(1)} <span class="sw-unit">%</span>`;
  document.getElementById('minSoil').innerHTML = `${minSoil.toFixed(1)} <span class="sw-unit">%</span>`;
  document.getElementById('activePumps').innerHTML = `${activeCount} <span class="sw-unit">/ ${swNodes.length}</span>`;
  
  // เพิ่มตัวเลขจำลองปริมาณน้ำตามจำนวนปั๊มที่เปิด
  let currentUsage = parseInt(document.getElementById('waterUsage').innerText.replace(/,/g, ''));
  if (activeCount > 0) currentUsage += (activeCount * 2); 
  document.getElementById('waterUsage').innerHTML = `${currentUsage.toLocaleString()} <span class="sw-unit">L</span>`;
}

// ════════════════════════════════════
// 3. INIT CHARTS (MOCKUP)
// ════════════════════════════════════
const hours = Array.from({length:24}, (_,i) => `${String(i).padStart(2,'0')}:00`);
const histSoil = [52.1, 50.4, 48.2, 46.5, 45.1, 42.8, 41.2, 38.5, 36.4, 34.2, 31.5, 29.8, 65.4 /* รดน้ำ */, 62.1, 60.5, 58.2, 55.4, 53.1, 51.5, 49.8, 47.5, 46.1, 44.8, 43.5];

// Line Chart
new Chart(document.getElementById('soilTrendChart').getContext('2d'), {
  type: 'line',
  data: {
    labels: hours,
    datasets: [{
      label: 'Avg Soil Moisture (%)', data: histSoil,
      borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,0.15)',
      fill: true, tension: 0.3, borderWidth: 2.5, pointRadius: 0, pointHoverRadius: 5
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { color: 'rgba(203,213,225,.3)' }, ticks: { font: {family: 'JetBrains Mono'}, maxTicksLimit: 12 } },
      y: { min: 0, max: 100, grid: { color: 'rgba(203,213,225,.3)' }, ticks: { color: '#22c55e', font: {family: 'JetBrains Mono'} } }
    },
    plugins: [{
      id: 'threshLine',
      beforeDraw(chart) {
        const { ctx, chartArea: { left, right }, scales: { y } } = chart;
        const yPos = y.getPixelForValue(30);
        if(yPos > y.bottom || yPos < y.top) return;
        ctx.save(); ctx.beginPath(); ctx.moveTo(left, yPos); ctx.lineTo(right, yPos);
        ctx.lineWidth = 1.5; ctx.strokeStyle = 'rgba(239,68,68,0.5)'; ctx.setLineDash([4, 4]); ctx.stroke(); ctx.restore();
      }
    }]
  }
});

// Bar Chart (Watering Duration)
new Chart(document.getElementById('pumpBarChart').getContext('2d'), {
  type: 'bar',
  data: {
    labels: swNodes.map(n => n.id),
    datasets: [{
      label: 'Pump Duration (Mins)',
      data: [45, 60, 20, 35, 80],
      backgroundColor: 'rgba(59,130,246,0.7)', borderColor: '#3b82f6',
      borderWidth: 1, borderRadius: 4
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { display: false }, ticks: { font: {family: 'JetBrains Mono'} } },
      y: { grid: { color: 'rgba(203,213,225,.3)' }, ticks: { color: '#3b82f6', font: {family: 'JetBrains Mono'} } }
    }
  }
});

// ════════════════════════════════════
// 4. REAL-TIME AUTO-IRRIGATION SIMULATION
// ════════════════════════════════════
function simulateAutoWatering() {
  swNodes.forEach(n => {
    if (n.isPumping) {
      // ถ้าน้ำเปิดอยู่ ความชื้นจะเพิ่มขึ้นอย่างรวดเร็ว
      n.soil = Math.min(80, n.soil + (Math.random() * 2 + 1));
      // ถ้าชื้นพอแล้ว (เกิน 70%) ให้ปิดปั๊มเองอัตโนมัติ
      if (n.soil >= 70) n.isPumping = false;
    } else {
      // ถ้าน้ำปิดอยู่ ความชื้นจะค่อยๆ ลดลง
      n.soil = Math.max(10, n.soil - (Math.random() * 0.8 + 0.1));
      // ถ้าน้ำแห้งกว่า 30% ให้ระบบสั่งเปิดปั๊มเองอัตโนมัติ!
      if (n.soil <= 30) n.isPumping = true;
    }
  });
  updateSWUI();
}

// โหลดครั้งแรก และตั้งเวลาอัปเดตทุก 2 วินาที (ให้เห็นความชื้นขยับไวๆ สนุกๆ ค่ะ)
updateSWUI();
setInterval(simulateAutoWatering, 2000);

</script>

</main>
</body>
</html>