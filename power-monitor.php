<?php
/* ── 1. Load global config ───────────────────────── */
require_once '_vars.php';

/* ── 2. Page-specific options ────────────────────── */
$page_title = "IoT Dashboard - Power Monitor";
$use_leaflet = false;

// CSS เฉพาะหน้า Power Monitor
$extra_css = '
<style>
  /* ── Power Stat Cards ── */
  .pwr-stat-card {
    background: var(--bg-card); border: 1px solid var(--slate-200);
    border-radius: var(--radius-lg); padding: 20px;
    display: flex; align-items: center; justify-content: space-between;
    box-shadow: var(--shadow-sm); transition: transform 0.2s;
    position: relative; overflow: hidden;
  }
  .pwr-stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
  .pwr-stat-card::before {
    content: ""; position: absolute; top: 0; left: 0; width: 4px; bottom: 0;
    background: var(--card-color, var(--slate-200));
  }
  .psc-watt { --card-color: var(--yellow-500); }
  .psc-kwh  { --card-color: var(--blue-500); }
  .psc-cost { --card-color: var(--green-500); }
  .psc-grid { --card-color: var(--purple-500); }

  .pwr-info { display: flex; flex-direction: column; gap: 4px; }
  .pwr-lbl { font-size: .75rem; color: var(--slate-500); font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }
  .pwr-val { font-family: var(--font-mono); font-size: 2rem; font-weight: 700; color: var(--slate-800); line-height: 1; }
  .pwr-val.glow { color: #d97706; text-shadow: 0 0 10px rgba(245,158,11,0.3); } /* เอฟเฟกต์ไฟเรืองแสง */
  .pwr-unit { font-size: 1rem; color: var(--slate-400); font-weight: 500; }
  .pwr-sub { font-size: .7rem; font-family: var(--font-mono); font-weight: 600; margin-top: 4px; }

  .pwr-icon-bg {
    width: 60px; height: 60px; border-radius: 12px;
    display: grid; place-items: center; font-size: 1.8rem;
    background: var(--slate-50); box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
    transform: rotate(-5deg);
  }

  /* ── Custom Data Visuals ── */
  .volt-badge {
    display: inline-block; padding: 4px 10px; border-radius: 6px;
    font-size: .75rem; font-weight: 700; font-family: var(--font-mono);
  }
  .volt-normal { background: var(--green-100); color: var(--green-600); }
  .volt-warn   { background: var(--yellow-100); color: var(--yellow-700); }
  .volt-danger { background: var(--red-100); color: var(--red-600); }

  .amp-bar-wrap { display: flex; align-items: center; gap: 8px; }
  .amp-bar { flex: 1; height: 8px; background: var(--slate-100); border-radius: 4px; overflow: hidden; min-width: 70px; }
  .amp-fill { height: 100%; border-radius: 4px; transition: width 0.3s ease-out; background: linear-gradient(90deg, var(--blue-400), var(--purple-500)); }
  .amp-val { font-family: var(--font-mono); font-size: .85rem; font-weight: 700; width: 45px; text-align: right; color: var(--slate-700); }
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
    <h2>⚡ Power & Energy Monitor</h2>
    <div class="section-line"></div>
    <div class="section-meta">PZEM-004T AC Energy Meter · พรศิริฟาร์มสุข</div>
  </div>

  <div class="grid-2" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 24px;">
    <div class="pwr-stat-card psc-watt">
      <div class="pwr-info">
        <div class="pwr-lbl">Live Power Usage</div>
        <div class="pwr-val glow" id="totalWatt">0 <span class="pwr-unit">W</span></div>
        <div class="pwr-sub" style="color: var(--yellow-600)">กำลังไฟฟ้ารวมขณะนี้</div>
      </div>
      <div class="pwr-icon-bg" style="color: var(--yellow-500)">⚡</div>
    </div>
    
    <div class="pwr-stat-card psc-kwh">
      <div class="pwr-info">
        <div class="pwr-lbl">Energy Consumed</div>
        <div class="pwr-val" id="totalKwh">142.5 <span class="pwr-unit">kWh</span></div>
        <div class="pwr-sub" style="color: var(--blue-500)">หน่วยก้านไฟฟ้าสะสม (เดือนนี้)</div>
      </div>
      <div class="pwr-icon-bg" style="color: var(--blue-500)">🔋</div>
    </div>

    <div class="pwr-stat-card psc-cost">
      <div class="pwr-info">
        <div class="pwr-lbl">Est. Monthly Cost</div>
        <div class="pwr-val" id="estCost">641.25 <span class="pwr-unit">฿</span></div>
        <div class="pwr-sub" style="color: var(--green-600)">คำนวณที่เรท 4.50 ฿/หน่วย</div>
      </div>
      <div class="pwr-icon-bg" style="color: var(--green-500)">💸</div>
    </div>

    <div class="pwr-stat-card psc-grid">
      <div class="pwr-info">
        <div class="pwr-lbl">Grid Voltage (Main)</div>
        <div class="pwr-val" id="mainVolt">220.5 <span class="pwr-unit">V</span></div>
        <div class="pwr-sub" id="voltStatus" style="color: var(--purple-500)">สถานะแรงดันปกติ (AC)</div>
      </div>
      <div class="pwr-icon-bg" style="color: var(--purple-500)">🔌</div>
    </div>
  </div>

  <div class="grid-2-1">
    <div class="panel">
      <div class="panel-header">
        <div class="panel-dot pd-yellow"></div>
        <div class="panel-title">Real-time Power Load (Watts)</div>
        <div class="panel-sub">อัปเดตทุกวินาที</div>
      </div>
      <div class="panel-body">
        <div style="height:280px; position:relative;"><canvas id="powerLiveChart"></canvas></div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-header">
        <div class="panel-dot pd-blue"></div>
        <div class="panel-title">Energy Breakdown</div>
        <div class="panel-sub">By Equipment</div>
      </div>
      <div class="panel-body" style="display:flex; justify-content:center; align-items:center;">
        <div style="height:240px; width:100%; position:relative;"><canvas id="powerPieChart"></canvas></div>
      </div>
    </div>
  </div>

  <div class="panel" style="margin-top: 16px;">
    <div class="panel-header">
      <div class="panel-dot pd-red"></div>
      <div class="panel-title">Equipment Load Monitor</div>
      <div class="panel-sub">PZEM-004T Sensor Data</div>
    </div>
    <div class="panel-body-sm" style="overflow-x: auto;">
      <table class="device-table" style="min-width: 700px;">
        <thead>
          <tr>
            <th>Zone / Equipment</th>
            <th>Voltage (V)</th>
            <th style="width: 25%">Current (A)</th>
            <th>Active Power (W)</th>
            <th>Power Factor</th>
            <th>Energy (kWh)</th>
          </tr>
        </thead>
        <tbody id="pwrTableBody">
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
// สมมติอุปกรณ์ไฟฟ้าหลักๆ ในฟาร์ม
const eqNodes = [
  { id: 'Water Pump (แปลง A-B)', loc: 'ปั๊มน้ำหอยโข่ง 1.5HP', v: 220.0, i: 5.2, pf: 0.85, kwh: 45.2, isActive: true },
  { id: 'Grow Lights (โรงเรือน)', loc: 'สปอร์ตไลท์ LED 100W x 10', v: 220.0, i: 4.5, pf: 0.95, kwh: 78.5, isActive: false },
  { id: 'Ventilation Fans', loc: 'พัดลมระบายอากาศ 24 นิ้ว x 2', v: 220.0, i: 3.8, pf: 0.90, kwh: 12.1, isActive: true },
  { id: 'IoT Control Cabinet', loc: 'ตู้คอนโทรล + เซิร์ฟเวอร์', v: 220.0, i: 0.8, pf: 0.98, kwh: 6.7, isActive: true }
];

function getVoltBadge(v) {
  if (v > 240) return { cls: 'volt-danger', txt: v.toFixed(1) + ' V (Over)' };
  if (v < 200) return { cls: 'volt-warn', txt: v.toFixed(1) + ' V (Drop)' };
  return { cls: 'volt-normal', txt: v.toFixed(1) + ' V' };
}

// ════════════════════════════════════
// 2. RENDER COMPONENTS
// ════════════════════════════════════
let pieChartInstance = null;

function updatePowerUI() {
  let totalW = 0;
  let pieLabels = [];
  let pieData = [];

  const tableHtml = eqNodes.map((n, index) => {
    // คำนวณ P = V * I * PF (สำหรับ AC)
    // ถ้าอุปกรณ์ปิดอยู่ ให้จำลองว่ากินกระแสต่ำมาก (Standby)
    if (!n.isActive) { n.i = Math.max(0, n.i - 0.5); if(n.i < 0.1) n.i = 0; }
    
    const powerW = n.v * n.i * n.pf;
    totalW += powerW;

    pieLabels.push(n.id);
    pieData.push(powerW > 0 ? powerW.toFixed(0) : 0);

    const vBadge = getVoltBadge(n.v);
    
    // คำนวณขีด Amp (สมมติ Max 10A)
    const ampPct = Math.min(100, (n.i / 10) * 100);

    return `
      <tr>
        <td>
          <div style="font-family:var(--font-head); font-weight:700; color:var(--blue-900); font-size:.85rem;">${n.id}</div>
          <div style="font-size:.7rem; color:var(--slate-500)">${n.loc}</div>
        </td>
        <td><span class="volt-badge ${vBadge.cls}">${vBadge.txt}</span></td>
        <td>
          <div class="amp-bar-wrap">
            <div class="amp-bar"><div class="amp-fill" style="width:${ampPct}%"></div></div>
            <div class="amp-val">${n.i.toFixed(2)} A</div>
          </div>
        </td>
        <td style="font-family:var(--font-mono); font-weight:700; color:${powerW > 1000 ? 'var(--orange-500)' : 'var(--blue-600)'}; font-size:.95rem;">
          ${powerW.toFixed(0)} <span style="font-size:.7rem; color:var(--slate-400)">W</span>
        </td>
        <td style="font-family:var(--font-mono); font-size:.8rem; color:var(--slate-600)">${n.pf.toFixed(2)}</td>
        <td style="font-family:var(--font-mono); font-weight:600; color:var(--slate-700)">${n.kwh.toFixed(1)}</td>
      </tr>
    `;
  }).join('');

  document.getElementById('pwrTableBody').innerHTML = tableHtml;

  // Update Top Stats
  document.getElementById('totalWatt').innerHTML = `${totalW.toFixed(0).toLocaleString()} <span class="pwr-unit">W</span>`;
  
  // อัปเดตแรงดันรวม (อิงจากตู้คอนโทรล)
  const mainV = eqNodes[3].v;
  document.getElementById('mainVolt').innerHTML = `${mainV.toFixed(1)} <span class="pwr-unit">V</span>`;
  if(mainV > 240) document.getElementById('voltStatus').innerHTML = '<span style="color:var(--red-500)">Over Voltage ⚠️</span>';
  else if(mainV < 200) document.getElementById('voltStatus').innerHTML = '<span style="color:var(--orange-500)">Voltage Drop ⚠️</span>';
  else document.getElementById('voltStatus').innerHTML = '<span style="color:var(--green-600)">แรงดันไฟฟ้าปกติ</span>';

  // Update Pie Chart
  if(pieChartInstance) {
    pieChartInstance.data.datasets[0].data = pieData;
    pieChartInstance.update();
  } else {
    pieChartInstance = new Chart(document.getElementById('powerPieChart').getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: pieLabels,
        datasets: [{
          data: pieData,
          backgroundColor: ['#3b82f6', '#f59e0b', '#10b981', '#8b5cf6'],
          borderWidth: 2, hoverOffset: 6
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false, cutout: '60%',
        plugins: { legend: { position: 'right', labels: {font: {family: 'DM Sans', size: 11}} } }
      }
    });
  }

  return totalW;
}

// ════════════════════════════════════
// 3. LIVE LINE CHART (REAL-TIME STREAM)
// ════════════════════════════════════
const ctxLive = document.getElementById('powerLiveChart').getContext('2d');
let liveLabels = Array.from({length: 30}, (_, i) => ''); // 30 วินาที
let liveDataW = Array.from({length: 30}, () => null);

const liveChart = new Chart(ctxLive, {
  type: 'line',
  data: {
    labels: liveLabels,
    datasets: [{
      label: 'Total Power (W)',
      data: liveDataW,
      borderColor: '#eab308',
      backgroundColor: (context) => {
        const g = context.chart.ctx.createLinearGradient(0, 0, 0, context.chart.height);
        g.addColorStop(0, 'rgba(234,179,8,0.2)');
        g.addColorStop(1, 'rgba(234,179,8,0)');
        return g;
      },
      borderWidth: 2.5, pointRadius: 0, fill: true, tension: 0.3
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false, animation: { duration: 0 }, // ปิดอนิเมชันเพื่อความไหลลื่นตอน Real-time
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { display: false }, ticks: { display: false } }, // ซ่อนแกน x ให้ดูเป็นเส้นไหลไปเรื่อยๆ
      y: { min: 0, max: 5000, grid: { color: 'rgba(203,213,225,.3)' }, ticks: { color: '#ca8a04', font: {family: 'JetBrains Mono'} } }
    }
  }
});

// ════════════════════════════════════
// 4. REAL-TIME SIMULATION ENGINE
// ════════════════════════════════════
function simulatePower() {
  // จำลองแรงดันไฟตก/ไฟเกิน (Grid Fluctuation) แกว่งระหว่าง 215V - 225V
  const gridV = 220 + (Math.random() * 6 - 3);
  
  eqNodes.forEach(n => {
    n.v = gridV;
    // ถ้าระบบเปิดอยู่ กระแสจะแกว่งนิดหน่อยตามการทำงานมอเตอร์
    if (n.isActive) {
      // สุ่มจำลองโหลดมอเตอร์ปั๊มน้ำ
      if (n.id.includes('Pump')) n.i = 5.2 + (Math.random() * 0.4 - 0.2);
      if (n.id.includes('Fans')) n.i = 3.8 + (Math.random() * 0.2 - 0.1);
      if (n.id.includes('IoT'))  n.i = 0.8 + (Math.random() * 0.05 - 0.02);
    }
  });

  // อัปเดตตารางและคำนวณวัตต์รวม
  const currentTotalW = updatePowerUI();

  // ดันข้อมูลเก่าออก ใส่ข้อมูลใหม่เข้ากราฟ (Real-time stream)
  liveDataW.push(currentTotalW);
  liveDataW.shift();
  liveChart.update();
}

// 💡 จำลองการเปิด/ปิดไฟปลูกพืช (โชว์กราฟกระชาก)
setInterval(() => {
  const growLights = eqNodes[1];
  growLights.isActive = !growLights.isActive;
  if(growLights.isActive) growLights.i = 4.5; // เปิดไฟดึงกระแส 4.5A
}, 15000); // สลับทุกๆ 15 วินาทีให้เห็นกราฟวัตต์พุ่งขึ้นลงชัดๆ

// โหลดครั้งแรก
updatePowerUI();

// อัปเดตข้อมูลทุกๆ 1 วินาที (เพื่อให้หน้าปัดตัวเลขวิ่งแบบเรียลไทม์)
setInterval(simulatePower, 1000);

</script>

</main>
</body>
</html>