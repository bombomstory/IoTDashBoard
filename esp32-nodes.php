<?php
/* ── 1. Load global config ───────────────────────── */
require_once '_vars.php';

/* ── 2. Page-specific options ────────────────────── */
$page_title = "IoT Dashboard - ESP32 Nodes";
$use_leaflet = false;

// CSS เฉพาะหน้าจัดการ ESP32 Nodes
$extra_css = '
<style>
  /* ── Stats Overview ── */
  .node-stat-card {
    background: var(--bg-card); border: 1px solid var(--slate-200);
    border-radius: var(--radius-lg); padding: 18px 24px;
    display: flex; align-items: center; gap: 16px;
    box-shadow: var(--shadow-sm);
  }
  .nsc-icon {
    width: 48px; height: 48px; border-radius: 50%;
    display: grid; place-items: center; font-size: 1.4rem; flex-shrink: 0;
  }
  .nsc-blue { background: var(--blue-50); color: var(--blue-600); }
  .nsc-green { background: var(--green-100); color: var(--green-600); }
  .nsc-red { background: var(--red-100); color: var(--red-600); }
  .nsc-yellow { background: var(--yellow-100); color: var(--yellow-600); }
  
  .nsc-info { flex: 1; }
  .nsc-val { font-family: var(--font-mono); font-size: 1.8rem; font-weight: 700; color: var(--blue-900); line-height: 1.1; }
  .nsc-lbl { font-size: .75rem; color: var(--slate-500); font-weight: 600; text-transform: uppercase; }

  /* ── Node Grid & Cards ── */
  .node-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px; margin-top: 20px;
  }
  .device-card {
    background: var(--bg-card); border: 1px solid var(--slate-200);
    border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);
    overflow: hidden; transition: transform 0.2s, box-shadow 0.2s;
    position: relative;
  }
  .device-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
  
  /* Status Bar ด้านซ้ายของ Card */
  .dc-status-bar { position: absolute; left: 0; top: 0; bottom: 0; width: 4px; }
  .sb-online { background: var(--green-500); }
  .sb-warn { background: var(--yellow-500); }
  .sb-offline { background: var(--red-500); }

  .dc-header {
    padding: 16px 20px; border-bottom: 1px dashed var(--slate-200);
    display: flex; align-items: center; justify-content: space-between;
  }
  .dc-title-wrap { display: flex; align-items: center; gap: 12px; }
  .dc-icon {
    width: 38px; height: 38px; background: var(--slate-100); border-radius: 8px;
    display: grid; place-items: center; font-size: 1.2rem;
  }
  .dc-name { font-family: var(--font-head); font-weight: 700; font-size: 1rem; color: var(--blue-900); }
  .dc-loc { font-size: .7rem; color: var(--slate-500); font-family: var(--font-mono); }
  
  .dc-body { padding: 16px 20px; font-size: .8rem; }
  .dc-info-row { display: flex; justify-content: space-between; margin-bottom: 8px; align-items: center; }
  .dc-info-lbl { color: var(--slate-500); display: flex; align-items: center; gap: 6px; }
  .dc-info-val { font-family: var(--font-mono); font-weight: 600; color: var(--slate-700); }

  /* Battery & Signal Visuals */
  .batt-container { display: flex; align-items: center; gap: 8px; }
  .batt-bar-bg { width: 50px; height: 8px; background: var(--slate-200); border-radius: 4px; overflow: hidden; position: relative; }
  .batt-bar-fill { height: 100%; border-radius: 4px; transition: width 0.3s; }
  
  .rssi-bars-wrap { display: flex; gap: 3px; align-items: flex-end; height: 14px; }
  .rssi-bar-seg { width: 4px; background: var(--slate-200); border-radius: 1px; }
  .rssi-bar-seg.active { background: var(--blue-500); }

  /* Actions */
  .dc-footer {
    padding: 12px 20px; background: var(--slate-50); border-top: 1px solid var(--slate-100);
    display: flex; gap: 10px;
  }
  .btn-action {
    flex: 1; padding: 6px 0; border: 1px solid var(--slate-300); background: white;
    border-radius: var(--radius-sm); font-size: .75rem; font-weight: 600; color: var(--slate-600);
    cursor: pointer; transition: all 0.2s; display: flex; justify-content: center; align-items: center; gap: 6px;
  }
  .btn-action:hover { background: var(--blue-50); border-color: var(--blue-300); color: var(--blue-600); }
  .btn-danger:hover { background: var(--red-50); border-color: var(--red-300); color: var(--red-600); }
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
    <h2>🔲 ESP32 Nodes Management</h2>
    <div class="section-line"></div>
    <div class="section-meta">Mesh Network Status</div>
  </div>

  <div class="grid-2" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 24px;">
    <div class="node-stat-card">
      <div class="nsc-icon nsc-blue">🌐</div>
      <div class="nsc-info">
        <div class="nsc-lbl">Total Nodes</div>
        <div class="nsc-val" id="statTotal">5</div>
      </div>
    </div>
    <div class="node-stat-card">
      <div class="nsc-icon nsc-green">✅</div>
      <div class="nsc-info">
        <div class="nsc-lbl">Online</div>
        <div class="nsc-val" id="statOnline">4</div>
      </div>
    </div>
    <div class="node-stat-card">
      <div class="nsc-icon nsc-red">❌</div>
      <div class="nsc-info">
        <div class="nsc-lbl">Offline</div>
        <div class="nsc-val" id="statOffline">0</div>
      </div>
    </div>
    <div class="node-stat-card">
      <div class="nsc-icon nsc-yellow">🔋</div>
      <div class="nsc-info">
        <div class="nsc-lbl">Low Battery</div>
        <div class="nsc-val" id="statLowBatt">1</div>
      </div>
    </div>
  </div>

  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
    <div style="font-weight: 600; color: var(--blue-900);">Active Devices</div>
    <button style="background: var(--blue-600); color: white; border: none; padding: 8px 16px; border-radius: var(--radius-sm); font-weight: 500; cursor: pointer; box-shadow: var(--shadow-sm);">➕ Add New Node</button>
  </div>

  <div class="node-grid" id="nodeGridContainer">
    </div>

<?php
/* ── 5. Footer + Shared JS ───────────────────────── */
require_once '_footer.php';
?>

<script>
// ════════════════════════════════════
// 1. DATA MOCKUP
// ════════════════════════════════════
const espNodes = [
  { id: 'Node-01', loc: 'แปลง A (โรงเรือน 1)', status: 'online', ip: '192.168.1.101', mac: '24:0A:C4:00:01:1A', fw: 'v2.5.1', uptime: '14d 05h 12m', batt: 92, rssi: -48, lastSync: 'Just now' },
  { id: 'Node-02', loc: 'แปลง B (กลางแจ้ง)', status: 'online', ip: '192.168.1.102', mac: '24:0A:C4:00:02:2B', fw: 'v2.5.1', uptime: '14d 02h 45m', batt: 78, rssi: -62, lastSync: '2m ago' },
  { id: 'Node-03', loc: 'แปลง C (มุมอับ)', status: 'warn', ip: '192.168.1.103', mac: '24:0A:C4:00:03:3C', fw: 'v2.5.0', uptime: '05d 12h 30m', batt: 18, rssi: -75, lastSync: '10m ago' },
  { id: 'Node-04', loc: 'แปลง D (โรงเรือน 2)', status: 'online', ip: '192.168.1.104', mac: '24:0A:C4:00:04:4D', fw: 'v2.5.1', uptime: '22d 08h 15m', batt: 65, rssi: -55, lastSync: '1m ago' },
  { id: 'Node-05', loc: 'แปลง E (ทางเข้า)', status: 'online', ip: '192.168.1.105', mac: '24:0A:C4:00:05:5E', fw: 'v2.5.1', uptime: '30d 10h 22m', batt: 88, rssi: -43, lastSync: 'Just now' },
];

// ════════════════════════════════════
// 2. HELPER FUNCTIONS
// ════════════════════════════════════
function getStatusBadge(status) {
  if(status === 'online') return `<span class="badge-pill badge-online"><span class="badge-dot"></span>Online</span>`;
  if(status === 'warn') return `<span class="badge-pill badge-warn"><span class="badge-dot"></span>Warning</span>`;
  return `<span class="badge-pill badge-offline"><span class="badge-dot"></span>Offline</span>`;
}

function getBatteryColor(level) {
  if (level > 50) return 'var(--green-500)';
  if (level > 20) return 'var(--yellow-500)';
  return 'var(--red-500)';
}

function renderRssiBars(rssi) {
  // สัญญาณยิ่งลบมากยิ่งแย่ (เช่น -40 ดีมาก, -80 แย่)
  let strength = 1;
  if (rssi > -50) strength = 4;
  else if (rssi > -65) strength = 3;
  else if (rssi > -75) strength = 2;
  
  let barsHtml = '';
  for (let i = 1; i <= 4; i++) {
    const activeClass = i <= strength ? 'active' : '';
    barsHtml += `<div class="rssi-bar-seg ${activeClass}" style="height: ${i * 3 + 2}px;"></div>`;
  }
  return `<div class="rssi-bars-wrap">${barsHtml}</div>`;
}

function handleAction(action, nodeId) {
  if(action === 'ping') {
    alert(`📡 Pinging ${nodeId}... \nReply from ${nodeId}: bytes=32 time=12ms TTL=64`);
  } else if(action === 'reboot') {
    if(confirm(`⚠️ Are you sure you want to restart ${nodeId}?`)) {
      alert(`🔄 ${nodeId} is restarting... Please wait 15-30 seconds.`);
    }
  }
}

// ════════════════════════════════════
// 3. RENDER CARDS & UPDATE STATS
// ════════════════════════════════════
function renderNodes() {
  const container = document.getElementById('nodeGridContainer');
  let onlineCount = 0;
  let offlineCount = 0;
  let lowBattCount = 0;

  container.innerHTML = espNodes.map(n => {
    // คำนวณ Stats
    if (n.status === 'online' || n.status === 'warn') onlineCount++;
    if (n.status === 'offline') offlineCount++;
    if (n.batt <= 20) lowBattCount++;

    const sbClass = n.status === 'online' ? 'sb-online' : (n.status === 'warn' ? 'sb-warn' : 'sb-offline');
    const battColor = getBatteryColor(n.batt);

    return `
      <div class="device-card">
        <div class="dc-status-bar ${sbClass}"></div>
        
        <div class="dc-header">
          <div class="dc-title-wrap">
            <div class="dc-icon">🔲</div>
            <div>
              <div class="dc-name">${n.id}</div>
              <div class="dc-loc">${n.loc}</div>
            </div>
          </div>
          ${getStatusBadge(n.status)}
        </div>

        <div class="dc-body">
          <div class="dc-info-row">
            <span class="dc-info-lbl">🌐 IP Address</span>
            <span class="dc-info-val">${n.ip}</span>
          </div>
          <div class="dc-info-row">
            <span class="dc-info-lbl">🏷️ MAC</span>
            <span class="dc-info-val" style="font-size:.75rem;">${n.mac}</span>
          </div>
          <div class="dc-info-row">
            <span class="dc-info-lbl">⚙️ Firmware</span>
            <span class="dc-info-val">${n.fw}</span>
          </div>
          <div class="dc-info-row">
            <span class="dc-info-lbl">⏱️ Uptime</span>
            <span class="dc-info-val" style="font-size:.75rem; color:var(--slate-500)">${n.uptime}</span>
          </div>

          <div style="height:1px; background:var(--slate-100); margin: 12px 0;"></div>

          <div class="dc-info-row">
            <span class="dc-info-lbl">🔋 Battery</span>
            <div class="batt-container">
              <span class="dc-info-val" style="color: ${battColor};">${n.batt}%</span>
              <div class="batt-bar-bg"><div class="batt-bar-fill" style="width: ${n.batt}%; background: ${battColor};"></div></div>
            </div>
          </div>
          <div class="dc-info-row">
            <span class="dc-info-lbl">📶 WiFi Signal</span>
            <div class="batt-container">
              <span class="dc-info-val">${n.rssi} dBm</span>
              ${renderRssiBars(n.rssi)}
            </div>
          </div>
          <div class="dc-info-row" style="margin-top:8px;">
            <span class="dc-info-lbl">🔄 Last Sync</span>
            <span class="dc-info-val" style="font-size:.7rem; color:${n.status==='warn'?'var(--orange-500)':'var(--green-500)'}">${n.lastSync}</span>
          </div>
        </div>

        <div class="dc-footer">
          <button class="btn-action" onclick="handleAction('ping', '${n.id}')">📡 Ping</button>
          <button class="btn-action" onclick="alert('⚙️ Opening configuration for ${n.id}')">⚙️ Config</button>
          <button class="btn-action btn-danger" onclick="handleAction('reboot', '${n.id}')">🔄 Restart</button>
        </div>
      </div>
    `;
  }).join('');

  // Update Top Stats
  document.getElementById('statTotal').innerText = espNodes.length;
  document.getElementById('statOnline').innerText = onlineCount;
  document.getElementById('statOffline').innerText = offlineCount;
  document.getElementById('statLowBatt').innerText = lowBattCount;
}

// 4. Initial Render
renderNodes();

</script>

</main>
</body>
</html>