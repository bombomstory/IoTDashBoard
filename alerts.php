<?php
/* ── 1. Load global config ───────────────────────── */
require_once '_vars.php';

/* ── 2. Page-specific options ────────────────────── */
$page_title = "IoT Dashboard - Alerts & Notifications";
$use_leaflet = false;

// CSS เฉพาะหน้า Alerts
$extra_css = '
<style>
  /* ── Alert Stat Cards ── */
  .alt-stat-card {
    background: var(--bg-card); border: 1px solid var(--slate-200);
    border-radius: var(--radius-lg); padding: 20px;
    display: flex; align-items: center; gap: 16px;
    box-shadow: var(--shadow-sm); transition: transform 0.2s;
  }
  .alt-stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
  
  .asc-icon {
    width: 54px; height: 54px; border-radius: 12px;
    display: grid; place-items: center; font-size: 1.8rem; flex-shrink: 0;
  }
  .asc-red { background: var(--red-100); color: var(--red-600); }
  .asc-orange { background: var(--orange-100); color: var(--orange-600); }
  .asc-blue { background: var(--blue-100); color: var(--blue-600); }
  .asc-green { background: var(--green-100); color: var(--green-600); }
  
  .asc-info { flex: 1; }
  .asc-lbl { font-size: .75rem; color: var(--slate-500); font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }
  .asc-val { font-family: var(--font-mono); font-size: 2rem; font-weight: 700; color: var(--slate-800); line-height: 1; margin-top: 4px; }

  /* ── Filter Tabs ── */
  .alert-tabs { display: flex; gap: 10px; margin-bottom: 16px; border-bottom: 2px solid var(--slate-100); padding-bottom: 12px; }
  .tab-btn {
    padding: 8px 16px; border: none; background: transparent; font-family: var(--font-ui);
    font-size: .85rem; font-weight: 600; color: var(--slate-500); cursor: pointer;
    border-radius: var(--radius-sm); transition: all 0.2s;
  }
  .tab-btn:hover { background: var(--slate-100); color: var(--slate-700); }
  .tab-btn.active { background: var(--blue-50); color: var(--blue-600); }

  /* ── Alert List Items ── */
  .alert-list { display: flex; flex-direction: column; gap: 12px; }
  .alert-row {
    background: var(--bg-card); border: 1px solid var(--slate-200); border-left: 4px solid var(--slate-300);
    border-radius: var(--radius-md); padding: 16px; display: flex; gap: 16px; align-items: center;
    box-shadow: var(--shadow-sm); transition: all 0.3s;
  }
  .alert-row.critical { border-left-color: var(--red-500); background: #fffcfc; }
  .alert-row.warning { border-left-color: var(--orange-500); background: #fffdfa; }
  .alert-row.info { border-left-color: var(--blue-500); }
  .alert-row.resolved { border-left-color: var(--green-500); opacity: 0.7; }
  
  .ar-icon { font-size: 1.5rem; width: 40px; text-align: center; flex-shrink: 0; }
  .ar-content { flex: 1; }
  .ar-title { font-family: var(--font-head); font-weight: 600; font-size: .95rem; color: var(--blue-900); margin-bottom: 4px; display: flex; align-items: center; gap: 8px; }
  .ar-desc { font-size: .8rem; color: var(--slate-600); line-height: 1.5; }
  .ar-meta { display: flex; align-items: center; gap: 16px; margin-top: 8px; font-size: .7rem; font-family: var(--font-mono); color: var(--slate-400); }
  
  /* ── Badges & Buttons ── */
  .ar-badge { padding: 2px 8px; border-radius: 12px; font-size: .65rem; font-weight: 700; font-family: var(--font-ui); letter-spacing: .05em; text-transform: uppercase; }
  .ar-badge.cri { background: var(--red-100); color: var(--red-600); animation: blink 1.5s infinite; }
  .ar-badge.war { background: var(--orange-100); color: var(--orange-600); }
  .ar-badge.inf { background: var(--blue-100); color: var(--blue-600); }
  @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }

  .btn-ack {
    background: white; border: 1px solid var(--slate-300); padding: 8px 16px; border-radius: var(--radius-sm);
    font-size: .75rem; font-weight: 600; color: var(--slate-600); cursor: pointer; transition: all 0.2s;
  }
  .btn-ack:hover { background: var(--green-50); border-color: var(--green-400); color: var(--green-600); }
  .alert-row.resolved .btn-ack { display: none; }
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
    <h2>🔔 System Alerts & Notifications</h2>
    <div class="section-line"></div>
    <div class="section-meta">ศูนย์รวมการแจ้งเตือนสถานะอุปกรณ์และสภาพแวดล้อม</div>
  </div>

  <div class="grid-2" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 24px;">
    <div class="alt-stat-card">
      <div class="asc-icon asc-red">🚨</div>
      <div class="asc-info">
        <div class="asc-lbl">Critical Alerts</div>
        <div class="asc-val" id="statCritical">0</div>
      </div>
    </div>
    <div class="alt-stat-card">
      <div class="asc-icon asc-orange">⚠️</div>
      <div class="asc-info">
        <div class="asc-lbl">Warnings</div>
        <div class="asc-val" id="statWarning">0</div>
      </div>
    </div>
    <div class="alt-stat-card">
      <div class="asc-icon asc-blue">ℹ️</div>
      <div class="asc-info">
        <div class="asc-lbl">Active Info</div>
        <div class="asc-val" id="statInfo">0</div>
      </div>
    </div>
    <div class="alt-stat-card">
      <div class="asc-icon asc-green">✅</div>
      <div class="asc-info">
        <div class="asc-lbl">Resolved Today</div>
        <div class="asc-val" id="statResolved">0</div>
      </div>
    </div>
  </div>

  <div class="panel">
    <div class="panel-header">
      <div class="panel-dot pd-red"></div>
      <div class="panel-title">Recent Alerts</div>
      <button style="margin-left:auto; background:transparent; border:1px solid var(--slate-300); padding:4px 10px; border-radius:4px; font-size:.7rem; cursor:pointer;" onclick="acknowledgeAll()">✓ Mark All as Read</button>
    </div>
    <div class="panel-body">
      
      <div class="alert-tabs">
        <button class="tab-btn active" onclick="filterAlerts('all')">All Alerts</button>
        <button class="tab-btn" onclick="filterAlerts('critical')">Critical</button>
        <button class="tab-btn" onclick="filterAlerts('warning')">Warnings</button>
        <button class="tab-btn" onclick="filterAlerts('resolved')">Resolved</button>
      </div>

      <div class="alert-list" id="alertContainer">
        </div>

    </div>
  </div>

<?php
/* ── 5. Footer + Shared JS ───────────────────────── */
require_once '_footer.php';
?>

<script>
// ════════════════════════════════════
// 1. DATA MODEL
// ════════════════════════════════════
let alertsData = [
  { id: 1, type: 'critical', node: 'Node-04', loc: 'โรงเรือน 2', title: 'PM2.5 ทะลุขีดจำกัด (Hazardous)', desc: 'ค่าฝุ่น PM2.5 พุ่งสูงถึง 158 µg/m³ เกินค่าความปลอดภัยที่ตั้งไว้ (55 µg/m³)', time: '10 mins ago', isResolved: false, icon: '🌫️' },
  { id: 2, type: 'warning', node: 'Node-03', loc: 'แปลง C', title: 'Battery Low Alert', desc: 'ระดับแบตเตอรี่ของ Node-03 ลดลงเหลือ 18% กรุณาตรวจสอบแผงโซลาร์เซลล์', time: '1 hour ago', isResolved: false, icon: '🔋' },
  { id: 3, type: 'critical', node: 'Node-01', loc: 'โรงเรือน 1', title: 'High Temperature Detected', desc: 'อุณหภูมิในโรงเรือนสูงถึง 36.5 °C เสี่ยงต่อความเสียหายของพืช', time: '2 hours ago', isResolved: false, icon: '🌡️' },
  { id: 4, type: 'info', node: 'System', loc: 'Server', title: 'Database Backup Completed', MS: 'สำรองข้อมูลไปยังเซิร์ฟเวอร์เรียบร้อยแล้ว (Size: 142.5 MB)', time: 'Yesterday 23:00', isResolved: true, icon: '💾' },
  { id: 5, type: 'warning', node: 'Node-02', loc: 'แปลง B', title: 'Soil Moisture Dropped', desc: 'ความชื้นในดินลดลงเหลือ 28% ระบบรดน้ำอัตโนมัติทำงานแล้ว', time: 'Yesterday 14:20', isResolved: true, icon: '🌱' }
];

let currentFilter = 'all';
let resolvedToday = 2; // ยอดสะสม

// ════════════════════════════════════
// 2. UI RENDERER
// ════════════════════════════════════
function renderAlerts() {
  const container = document.getElementById('alertContainer');
  let criticalCount = 0, warningCount = 0, infoCount = 0;

  // Filter Logic
  const filteredAlerts = alertsData.filter(a => {
    // นำไปนับ Stats เฉพาะที่ยังไม่ Resolved
    if (!a.isResolved) {
      if (a.type === 'critical') criticalCount++;
      if (a.type === 'warning') warningCount++;
      if (a.type === 'info') infoCount++;
    }

    if (currentFilter === 'all') return true;
    if (currentFilter === 'resolved') return a.isResolved;
    return a.type === currentFilter && !a.isResolved;
  });

  // สร้าง HTML ของแต่ละ Alert
  container.innerHTML = filteredAlerts.map(a => {
    const badgeClass = a.type === 'critical' ? 'cri' : (a.type === 'warning' ? 'war' : 'inf');
    const rowClass = a.isResolved ? 'resolved' : a.type;
    
    return `
      <div class="alert-row ${rowClass}" id="alert-${a.id}">
        <div class="ar-icon">${a.icon}</div>
        <div class="ar-content">
          <div class="ar-title">
            ${a.title}
            ${!a.isResolved ? `<span class="ar-badge ${badgeClass}">${a.type}</span>` : '<span class="ar-badge" style="background:var(--green-100);color:var(--green-600)">Resolved</span>'}
          </div>
          <div class="ar-desc">${a.desc}</div>
          <div class="ar-meta">
            <span>🕒 ${a.time}</span>
            <span>📍 ${a.node} (${a.loc})</span>
            ${a.isResolved ? `<span style="color:var(--green-500)">✓ Acknowledged</span>` : ''}
          </div>
        </div>
        <button class="btn-ack" onclick="ackAlert(${a.id})">✓ Acknowledge</button>
      </div>
    `;
  }).join('');

  if (filteredAlerts.length === 0) {
    container.innerHTML = `<div style="text-align:center; padding: 30px; color:var(--slate-400);">🎉 ไม่มีรายการแจ้งเตือนในหมวดหมู่นี้</div>`;
  }

  // อัปเดตตัวเลข Stats ด้านบน
  document.getElementById('statCritical').innerText = criticalCount;
  document.getElementById('statWarning').innerText = warningCount;
  document.getElementById('statInfo').innerText = infoCount;
  document.getElementById('statResolved').innerText = resolvedToday;
}

// ════════════════════════════════════
// 3. INTERACTIONS
// ════════════════════════════════════
function filterAlerts(type) {
  currentFilter = type;
  
  // อัปเดต CSS ของปุ่ม Tabs
  document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
  event.target.classList.add('active');
  
  renderAlerts();
}

function ackAlert(id) {
  const alertIndex = alertsData.findIndex(a => a.id === id);
  if (alertIndex > -1) {
    alertsData[alertIndex].isResolved = true;
    resolvedToday++;
    renderAlerts();
  }
}

function acknowledgeAll() {
  if(confirm('ยืนยันรับทราบการแจ้งเตือนทั้งหมด?')) {
    alertsData.forEach(a => {
      if(!a.isResolved) {
        a.isResolved = true;
        resolvedToday++;
      }
    });
    renderAlerts();
  }
}

// ════════════════════════════════════
// 4. SIMULATE NEW ALERT
// ════════════════════════════════════
let alertCounter = 10;
function simulateIncomingAlert() {
  // สุ่มสร้างแจ้งเตือนใหม่ทุกๆ 20 วินาที
  const newAlert = {
    id: alertCounter++,
    type: 'warning',
    node: 'Node-05',
    loc: 'แปลง E',
    title: 'High Humidity Detected',
    desc: 'ความชื้นในอากาศสูงเกิน 85% เสี่ยงต่อการเกิดเชื้อราที่ใบพืช',
    time: 'Just now',
    isResolved: false,
    icon: '💦'
  };
  
  alertsData.unshift(newAlert); // แทรกด้านบนสุด
  renderAlerts();

  // เด้งแจ้งเตือนบนหน้าต่างเบราว์เซอร์
  // alert('🔔 New Alert: ' + newAlert.title); 
}

// โหลดครั้งแรก
renderAlerts();

// จำลองการมีแจ้งเตือนใหม่วิ่งเข้ามา (20 วิ)
setTimeout(simulateIncomingAlert, 20000);

</script>

</main>
</body>
</html>