<?php
/* ── 1. Load global config ───────────────────────── */
require_once '_vars.php';

/* ── 2. Page-specific options ────────────────────── */
$page_title = "IoT Dashboard - PM2.5 Monitor";
$use_leaflet = false; // ไม่ใช้แผนที่

// เพิ่ม CSS เฉพาะหน้านี้ (ตกแต่งการ์ดคำแนะนำสุขภาพ และหน้าปัดเสริม)
$extra_css = '
<style>
  .health-card {
    background: var(--blue-50);
    border: 1px solid var(--blue-100);
    border-radius: var(--radius-lg);
    padding: 20px;
    display: flex; gap: 16px;
  }
  .health-icon {
    font-size: 2.5rem; flex-shrink: 0;
    width: 60px; height: 60px; background: white; border-radius: 50%;
    display: grid; place-items: center; box-shadow: var(--shadow-sm);
  }
  .health-content h4 { font-family: var(--font-head); color: var(--blue-900); font-size: .95rem; margin-bottom: 6px; }
  .health-content p { font-size: .8rem; color: var(--slate-600); line-height: 1.6; }
  
  .stat-box {
    background: var(--bg-card); border: 1px solid var(--slate-200);
    padding: 16px; border-radius: var(--radius-md); text-align: center;
  }
  .stat-box-val { font-family: var(--font-mono); font-size: 1.8rem; font-weight: 700; color: var(--slate-700); }
  .stat-box-lbl { font-size: .7rem; color: var(--slate-500); text-transform: uppercase; font-weight: 600; letter-spacing: .05em; margin-top: 4px; }
  
  /* ขยายความสูงกราฟ Bar ของ Node ให้ดูง่ายขึ้น */
  .node-bar-wrap { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
  .node-bar-name { font-family: var(--font-mono); font-size: .75rem; color: var(--slate-600); width: 65px; flex-shrink: 0; font-weight: 600; }
  .node-bar-track { flex: 1; height: 12px; background: var(--slate-100); border-radius: 6px; overflow: hidden; }
  .node-bar-fill { height: 100%; border-radius: 6px; transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1); }
  .node-bar-val { font-family: var(--font-mono); font-size: .8rem; font-weight: 700; width: 55px; text-align: right; }
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
    <h2>🌫️ Air Quality Index (AQI) Monitor</h2>
    <div class="section-line"></div>
    <div class="section-meta">อัปเดตข้อมูลเซ็นเซอร์ PMS5003 แบบ Real-time</div>
  </div>

  <div class="pm25-banner" id="pm25Banner" style="margin-bottom: 24px;">
    <div class="pm25-main-val">
      <div class="pm25-big" id="pm25BigVal">--.-</div>
      <div class="pm25-unit-lbl">µg/m³  ·  PM2.5</div>
    </div>
    <div class="pm25-info">
      <div class="aqi-badge" id="aqiBadge"><span class="aqi-dot"></span><span id="aqiLabel">กำลังโหลด...</span></div>
      <div class="pm25-desc" id="pm25Desc">กำลังวิเคราะห์คุณภาพอากาศ...</div>
      <div class="pm25-recommendation">
        <span id="pm25RecIcon">💚</span>
        <span id="pm25Rec">รอรับข้อมูลจากเซ็นเซอร์</span>
      </div>
    </div>
    <div class="pm25-scale">
      <div style="font-size:.65rem;font-weight:700;color:var(--slate-400);letter-spacing:.1em;text-transform:uppercase;margin-bottom:4px;">AQI SCALE (WHO)</div>
      <div class="aqi-row" id="aqiRow0"><div class="aqi-pip" style="background:var(--aqi-good)"></div><span>ดี</span><span class="aqi-range">0–12</span></div>
      <div class="aqi-row" id="aqiRow1"><div class="aqi-pip" style="background:var(--aqi-moderate)"></div><span>ปานกลาง</span><span class="aqi-range">12–35</span></div>
      <div class="aqi-row" id="aqiRow2"><div class="aqi-pip" style="background:var(--aqi-sensitive)"></div><span>กลุ่มเสี่ยง</span><span class="aqi-range">35–55</span></div>
      <div class="aqi-row" id="aqiRow3"><div class="aqi-pip" style="background:var(--aqi-unhealthy)"></div><span>ไม่ดี</span><span class="aqi-range">55–150</span></div>
      <div class="aqi-row" id="aqiRow4"><div class="aqi-pip" style="background:var(--aqi-very)"></div><span>อันตราย</span><span class="aqi-range">150+</span></div>
    </div>
  </div>

  <div class="grid-3" style="margin-bottom: 24px;">
    <div class="stat-box">
      <div class="stat-box-val" id="statMin" style="color: var(--green-500)">8.2</div>
      <div class="stat-box-lbl">ต่ำสุด (24 ชม.)</div>
    </div>
    <div class="stat-box">
      <div class="stat-box-val" id="statMax" style="color: var(--orange-500)">42.1</div>
      <div class="stat-box-lbl">สูงสุด (24 ชม.)</div>
    </div>
    <div class="health-card">
      <div class="health-icon" id="healthIcon">😷</div>
      <div class="health-content">
        <h4>คำแนะนำด้านสุขภาพ</h4>
        <p id="healthText">กลุ่มเสี่ยง (เด็ก ผู้สูงอายุ ผู้ป่วยโรคทางเดินหายใจ) ควรลดระยะเวลาการทำกิจกรรมกลางแจ้ง และสวมหน้ากากป้องกันฝุ่น</p>
      </div>
    </div>
  </div>

  <div class="grid-2-1">
    <div class="panel">
      <div class="panel-header">
        <div class="panel-dot pd-blue"></div>
        <div class="panel-title">PM2.5 Hourly Trend (24h)</div>
        <div class="panel-sub">ค่าเฉลี่ยทุกพื้นที่</div>
      </div>
      <div class="panel-body">
        <div style="height:280px;position:relative"><canvas id="pm25TrendChart"></canvas></div>
        <div style="display:flex;gap:16px;margin-top:14px;justify-content:center;border-top:1px solid var(--slate-100);padding-top:14px;">
          <div style="display:flex;align-items:center;gap:6px;font-size:.7rem;color:var(--slate-500)">
            <div style="width:20px;height:2px;border-top:2px dashed var(--green-500)"></div> ดี (≤12)
          </div>
          <div style="display:flex;align-items:center;gap:6px;font-size:.7rem;color:var(--slate-500)">
            <div style="width:20px;height:2px;border-top:2px dashed var(--yellow-500)"></div> ปานกลาง (≤35)
          </div>
          <div style="display:flex;align-items:center;gap:6px;font-size:.7rem;color:var(--slate-500)">
            <div style="width:20px;height:2px;border-top:2px dashed var(--red-500)"></div> เริ่มมีผลกระทบ (≤55)
          </div>
        </div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-header">
        <div class="panel-dot pd-purple"></div>
        <div class="panel-title">Current PM2.5 by Node</div>
      </div>
      <div class="panel-body" id="nodeComparisonList">
        </div>
    </div>
  </div>

<?php
/* ── 5. Footer + Shared JS ───────────────────────── */
require_once '_footer.php';
?>

<script>
// ════════════════════════════════════
// 1. PM2.5 AQI LOGIC & STATE
// ════════════════════════════════════
const PM25_LEVELS = [
  { max:12,   label:'ดี (Good)', color:'var(--aqi-good)', bg:'var(--green-100)', icon:'😊', recIcon:'💚', rec:'สามารถทำกิจกรรมกลางแจ้งได้ตามปกติ ไม่จำเป็นต้องสวมหน้ากากอนามัย', desc:'คุณภาพอากาศอยู่ในระดับดี ฝุ่นละออง PM2.5 อยู่ในเกณฑ์มาตรฐาน WHO', cls:'good' },
  { max:35.4, label:'ปานกลาง (Moderate)', color:'var(--aqi-moderate)', bg:'var(--yellow-100)', icon:'😐', recIcon:'💛', rec:'กลุ่มเสี่ยง (โรคหอบหืด โรคหัวใจ) ควรระมัดระวังเป็นพิเศษ', desc:'คุณภาพอากาศอยู่ในระดับพอรับได้ แต่ผู้มีความเสี่ยงสูงควรสังเกตอาการ', cls:'moderate' },
  { max:55.4, label:'กลุ่มเสี่ยง (USG)', color:'var(--aqi-sensitive)', bg:'var(--orange-100)', icon:'😷', recIcon:'🧡', rec:'กลุ่มเสี่ยงควรงดกิจกรรมกลางแจ้ง พิจารณาสวมหน้ากาก N95', desc:'อาจส่งผลต่อสุขภาพของกลุ่มเสี่ยง เช่น เด็ก ผู้สูงอายุ และผู้ป่วย', cls:'sensitive' },
  { max:150.4,label:'ไม่ดีต่อสุขภาพ (Unhealthy)', color:'var(--aqi-unhealthy)', bg:'var(--red-100)', icon:'🚨', recIcon:'❤️', rec:'ทุกคนควรสวมหน้ากาก N95 และหลีกเลี่ยงกิจกรรมกลางแจ้งเด็ดขาด', desc:'คุณภาพอากาศอยู่ในระดับที่เป็นอันตรายต่อสุขภาพทุกคนในพื้นที่', cls:'unhealthy' },
  { max:999,  label:'อันตราย (Hazardous)', color:'var(--aqi-very)', bg:'var(--purple-100)', icon:'☠️', recIcon:'💜', rec:'ห้ามออกนอกอาคาร ปิดประตูหน้าต่าง สวมหน้ากาก N95 ตลอดเวลา', desc:'ระดับอันตราย กระทบต่อสุขภาพอย่างรุนแรง ทุกคนได้รับผลกระทบ', cls:'very' },
];

function getAQILevel(val) { return PM25_LEVELS.find(l => val <= l.max) || PM25_LEVELS[PM25_LEVELS.length-1]; }

function updateBigBanner(val) {
  const lv = getAQILevel(val);
  
  // อัปเดตตัวเลข
  document.getElementById('pm25BigVal').textContent = val.toFixed(1);
  document.getElementById('pm25BigVal').style.color = lv.color;
  
  // อัปเดต Badge และข้อความ
  const badge = document.getElementById('aqiBadge');
  badge.style.background = lv.bg; badge.style.color = lv.color;
  document.getElementById('aqiLabel').textContent = lv.label;
  document.getElementById('pm25Desc').textContent = lv.desc;
  
  document.getElementById('pm25RecIcon').textContent = lv.recIcon;
  document.getElementById('pm25Rec').textContent = lv.rec;
  
  // อัปเดต Health Card ด้านล่าง
  document.getElementById('healthIcon').textContent = lv.icon;
  document.getElementById('healthText').textContent = lv.rec;
  
  // อัปเดตขอบสีของ Banner
  const banner = document.getElementById('pm25Banner');
  banner.className = 'pm25-banner aqi-' + lv.cls;
  banner.style.setProperty('--before-bg', lv.color);

  // ไฮไลท์ Scale
  ['aqiRow0','aqiRow1','aqiRow2','aqiRow3','aqiRow4'].forEach((id, i) => {
    const row = document.getElementById(id);
    const isActive = (lv === PM25_LEVELS[i]);
    row.style.background = isActive ? PM25_LEVELS[i].bg : 'transparent';
    row.style.borderColor = isActive ? PM25_LEVELS[i].color+'33' : 'transparent';
    row.style.fontWeight = isActive ? '700' : '400';
  });
}

// ════════════════════════════════════
// 2. NODE BARS RENDERER
// ════════════════════════════════════
const nodes = [
  { id: 'Node-01', val: 12.4 }, { id: 'Node-02', val: 24.1 },
  { id: 'Node-03', val: 8.6 },  { id: 'Node-04', val: 42.5 },
  { id: 'Node-05', val: 18.2 }
];

function renderNodeBars() {
  const maxVal = 75; // ตั้ง Max scale เพื่อให้กราฟดูสมส่วน
  const container = document.getElementById('nodeComparisonList');
  
  container.innerHTML = nodes.map((n, i) => {
    const lv = getAQILevel(n.val);
    const pct = Math.min(100, (n.val / maxVal) * 100);
    return `
      <div class="node-bar-wrap">
        <div class="node-bar-name">${n.id}</div>
        <div class="node-bar-track">
          <div class="node-bar-fill" id="nfill-${i}" style="width: ${pct}%; background: ${lv.color};"></div>
        </div>
        <div class="node-bar-val" id="nval-${i}" style="color: ${lv.color};">${n.val.toFixed(1)}</div>
      </div>
    `;
  }).join('');
}
renderNodeBars();

// ════════════════════════════════════
// 3. CHART.JS (TREND CHART)
// ════════════════════════════════════
const hours = Array.from({length:24}, (_,i) => `${String(i).padStart(2,'0')}:00`);
const pm25TrendData = [18.2, 17.5, 16.4, 15.1, 14.8, 14.2, 16.5, 22.4, 28.1, 35.8, 42.1, 38.5, 34.2, 31.0, 28.5, 25.4, 22.1, 19.8, 18.4, 16.2, 15.0, 14.1, 13.5, 12.8];

const ctx = document.getElementById('pm25TrendChart').getContext('2d');
new Chart(ctx, {
  type: 'line',
  data: {
    labels: hours,
    datasets: [{
      label: 'Avg PM2.5 (µg/m³)',
      data: pm25TrendData,
      borderColor: '#3b82f6',
      backgroundColor: (context) => {
        const gradient = context.chart.ctx.createLinearGradient(0, 0, 0, context.chart.height);
        gradient.addColorStop(0, 'rgba(59,130,246,0.2)');
        gradient.addColorStop(1, 'rgba(59,130,246,0)');
        return gradient;
      },
      borderWidth: 2.5, pointRadius: 0, pointHoverRadius: 6, fill: true, tension: 0.4
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { color: 'rgba(203,213,225,.3)' }, ticks: { font: {family: 'JetBrains Mono', size: 10}, maxTicksLimit: 12 } },
      y: { grid: { color: 'rgba(203,213,225,.3)' }, ticks: { font: {family: 'JetBrains Mono', size: 10} }, min: 0, max: 60 }
    }
  },
  // วาดเส้นแบ่งระดับ AQI (Reference lines)
  plugins: [{
    id: 'horizontalLines',
    beforeDraw(chart) {
      const { ctx, chartArea: { left, right }, scales: { y } } = chart;
      const drawLine = (val, color) => {
        const yPos = y.getPixelForValue(val);
        if(yPos > y.bottom || yPos < y.top) return;
        ctx.save();
        ctx.beginPath();
        ctx.moveTo(left, yPos);
        ctx.lineTo(right, yPos);
        ctx.lineWidth = 1.5;
        ctx.strokeStyle = color;
        ctx.setLineDash([4, 4]);
        ctx.stroke();
        ctx.restore();
      };
      drawLine(12, 'rgba(34,197,94,0.6)'); // เขียว
      drawLine(35, 'rgba(234,179,8,0.6)'); // เหลือง
      drawLine(55, 'rgba(239,68,68,0.6)'); // แดง
    }
  }]
});

// ════════════════════════════════════
// 4. REAL-TIME SIMULATION
// ════════════════════════════════════
function simulateRealtime() {
  // สุ่มอัปเดตค่าของแต่ละ Node
  let total = 0;
  nodes.forEach((n, i) => {
    n.val = Math.max(5, Math.min(65, n.val + (Math.random() * 4 - 2)));
    total += n.val;
    
    // อัปเดต Bar UI
    const lv = getAQILevel(n.val);
    const pct = Math.min(100, (n.val / 75) * 100);
    const fillEl = document.getElementById(`nfill-${i}`);
    const valEl = document.getElementById(`nval-${i}`);
    
    if(fillEl) {
      fillEl.style.width = `${pct}%`;
      fillEl.style.background = lv.color;
    }
    if(valEl) {
      valEl.textContent = n.val.toFixed(1);
      valEl.style.color = lv.color;
    }
  });

  // อัปเดตค่าเฉลี่ยไปที่ Banner ใหญ่
  const avg = total / nodes.length;
  updateBigBanner(avg);
}

// โหลดครั้งแรก และตั้งเวลาอัปเดตทุก 4 วินาที
updateBigBanner(18.2);
setInterval(simulateRealtime, 4000);

</script>

</main>
</body>
</html>