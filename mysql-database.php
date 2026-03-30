<?php
/* ── 1. Load global config ───────────────────────── */
require_once '_vars.php';

/* ── 2. Page-specific options ────────────────────── */
$page_title = "IoT Dashboard - Database Management";
$use_leaflet = false;

// CSS เฉพาะหน้าจัดการ Database
$extra_css = '
<style>
  /* ── DB Stat Cards ── */
  .db-stat-card {
    background: var(--bg-card); border: 1px solid var(--slate-200);
    border-radius: var(--radius-lg); padding: 18px;
    display: flex; align-items: center; gap: 16px;
    box-shadow: var(--shadow-sm);
  }
  .db-icon {
    width: 50px; height: 50px; border-radius: 12px;
    display: grid; place-items: center; font-size: 1.5rem; flex-shrink: 0;
  }
  .dbi-green  { background: var(--green-50); color: var(--green-600); }
  .dbi-blue   { background: var(--blue-50); color: var(--blue-600); }
  .dbi-purple { background: var(--purple-50); color: var(--purple-600); }
  .dbi-orange { background: var(--orange-50); color: var(--orange-600); }
  
  .db-info { flex: 1; }
  .db-lbl { font-size: .7rem; color: var(--slate-500); font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }
  .db-val { font-family: var(--font-mono); font-size: 1.6rem; font-weight: 700; color: var(--blue-900); line-height: 1.1; margin-top: 2px; }
  .db-sub { font-size: .7rem; font-family: var(--font-mono); color: var(--slate-400); margin-top: 4px; }

  /* ── Server Info Panel ── */
  .server-info-list { list-style: none; padding: 0; margin: 0; font-size: .8rem; }
  .server-info-list li { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px dashed var(--slate-200); }
  .server-info-list li:last-child { border-bottom: none; }
  .si-lbl { color: var(--slate-500); display: flex; align-items: center; gap: 6px; }
  .si-val { font-family: var(--font-mono); font-weight: 600; color: var(--slate-700); }

  /* ── Live SQL Terminal ── */
  .sql-terminal {
    background: #0f172a; border-radius: var(--radius-md); padding: 16px;
    height: 300px; overflow-y: auto; font-family: var(--font-mono); font-size: .75rem;
    color: #e2e8f0; box-shadow: inset 0 4px 10px rgba(0,0,0,.3);
  }
  .sql-line { margin-bottom: 8px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 6px; line-height: 1.5; }
  .sql-time { color: #64748b; margin-right: 8px; }
  .sql-kw   { color: #c678dd; font-weight: 600; } /* Keyword: INSERT, INTO, VALUES */
  .sql-str  { color: #98c379; } /* Strings */
  .sql-num  { color: #d19a66; } /* Numbers */
  .sql-func { color: #61afef; } /* Functions */
  .sql-tbl  { color: #e5c07b; } /* Table names */
  
  .sql-terminal::-webkit-scrollbar { width: 6px; }
  .sql-terminal::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }

  /* ── Action Buttons ── */
  .db-actions { display: flex; gap: 10px; margin-top: 14px; }
  .btn-db {
    flex: 1; padding: 8px 0; border: 1px solid var(--slate-300); background: white;
    border-radius: var(--radius-sm); font-size: .75rem; font-weight: 600; color: var(--slate-600);
    cursor: pointer; transition: all 0.2s; display: flex; justify-content: center; align-items: center; gap: 6px;
  }
  .btn-db:hover { background: var(--blue-50); border-color: var(--blue-300); color: var(--blue-600); }
  .btn-db.primary { background: var(--blue-600); color: white; border: none; }
  .btn-db.primary:hover { background: var(--blue-700); }
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
    <h2>🗄️ Database Management & Sync</h2>
    <div class="section-line"></div>
    <div class="section-meta">PDO Connection · Active</div>
  </div>

  <div class="grid-2" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 20px;">
    <div class="db-stat-card">
      <div class="db-icon dbi-green">🟢</div>
      <div class="db-info">
        <div class="db-lbl">Connection Status</div>
        <div class="db-val">Connected</div>
        <div class="db-sub" style="color:var(--green-500)">Ping: <span id="dbPing">12</span> ms</div>
      </div>
    </div>
    
    <div class="db-stat-card">
      <div class="db-icon dbi-blue">📊</div>
      <div class="db-info">
        <div class="db-lbl">Total Records</div>
        <div class="db-val" id="totalRecords">1.24M</div>
        <div class="db-sub">ตาราง dbo.sensor_log</div>
      </div>
    </div>

    <div class="db-stat-card">
      <div class="db-icon dbi-purple">💾</div>
      <div class="db-info">
        <div class="db-lbl">Database Size</div>
        <div class="db-val" id="dbSize">142.5</div>
        <div class="db-sub">หน่วย: Megabytes (MB)</div>
      </div>
    </div>

    <div class="db-stat-card">
      <div class="db-icon dbi-orange">⚡</div>
      <div class="db-info">
        <div class="db-lbl">Insert Rate</div>
        <div class="db-val" id="insertRate">12 <span style="font-size:1rem">/min</span></div>
        <div class="db-sub">Transactions per minute</div>
      </div>
    </div>
  </div>

  <div class="grid-2-1">
    <div class="panel">
      <div class="panel-header">
        <div class="panel-dot pd-blue"></div>
        <div class="panel-title">Database Configuration</div>
      </div>
      <div class="panel-body">
        <ul class="server-info-list">
          <li>
            <span class="si-lbl">🔌 Driver / Engine</span>
            <span class="si-val" style="color:var(--blue-600)">PDO_SQLSRV (MS SQL 2019)</span>
          </li>
          <li>
            <span class="si-lbl">🌐 Host Address</span>
            <span class="si-val">127.0.0.1, 1433</span>
          </li>
          <li>
            <span class="si-lbl">🗂️ Database Name</span>
            <span class="si-val">Pornsiri_Farm_IoT</span>
          </li>
          <li>
            <span class="si-lbl">👤 User Role</span>
            <span class="si-val">db_owner</span>
          </li>
          <li>
            <span class="si-lbl">🕒 Server Uptime</span>
            <span class="si-val" style="color:var(--green-600)">45 Days, 12 Hrs</span>
          </li>
          <li>
            <span class="si-lbl">📦 Collation</span>
            <span class="si-val">Thai_CI_AS</span>
          </li>
        </ul>
        <div class="db-actions">
          <button class="btn-db" onclick="alert('✅ Connection Test Passed! \nLatency: 12ms')">🔄 Test Conn</button>
          <button class="btn-db" onclick="alert('🧹 Optimizing Indexes for dbo.sensor_log...')">⚙️ Optimize</button>
          <button class="btn-db primary" onclick="alert('💾 Generating Backup (.bak)...\nPlease wait.')">📥 Backup DB</button>
        </div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-header">
        <div class="panel-dot pd-purple"></div>
        <div class="panel-title">Database Growth Trend</div>
        <div class="panel-sub">Records (Millions) - Last 6 Months</div>
      </div>
      <div class="panel-body">
        <div style="height:250px; position:relative;"><canvas id="dbGrowthChart"></canvas></div>
      </div>
    </div>
  </div>

  <div class="panel" style="margin-top: 16px;">
    <div class="panel-header">
      <div class="panel-dot pd-green"></div>
      <div class="panel-title">Live Transaction Monitor (Raw SQL)</div>
      <div class="panel-sub">Listening to ESP32 MQTT -> PHP -> PDO</div>
    </div>
    <div class="panel-body" style="padding: 10px;">
      <div class="sql-terminal" id="sqlTerminal">
        <div class="sql-line"><span class="sql-time">[System]</span> Connected to MS SQL Server successfully.</div>
        <div class="sql-line"><span class="sql-time">[System]</span> Awaiting incoming data from MQTT Broker...</div>
      </div>
    </div>
  </div>

<?php
/* ── 5. Footer + Shared JS ───────────────────────── */
require_once '_footer.php';
?>

<script>
// ════════════════════════════════════
// 1. INIT CHART (DB GROWTH)
// ════════════════════════════════════
const months = ['Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'];
const recordsData = [0.45, 0.62, 0.81, 0.98, 1.15, 1.24];

new Chart(document.getElementById('dbGrowthChart').getContext('2d'), {
  type: 'line',
  data: {
    labels: months,
    datasets: [{
      label: 'Total Records (Millions)',
      data: recordsData,
      borderColor: '#a855f7',
      backgroundColor: (context) => {
        const g = context.chart.ctx.createLinearGradient(0, 0, 0, context.chart.height);
        g.addColorStop(0, 'rgba(168,85,247,0.2)');
        g.addColorStop(1, 'rgba(168,85,247,0)');
        return g;
      },
      borderWidth: 3, pointRadius: 4, pointBackgroundColor: '#fff',
      pointBorderColor: '#a855f7', pointBorderWidth: 2, fill: true, tension: 0.3
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { display: false }, ticks: { font: {family: 'JetBrains Mono'} } },
      y: { grid: { color: 'rgba(203,213,225,.3)' }, ticks: { font: {family: 'JetBrains Mono'} }, min: 0, max: 1.5 }
    }
  }
});

// ════════════════════════════════════
// 2. LIVE SQL TERMINAL SIMULATION
// ════════════════════════════════════
const terminal = document.getElementById('sqlTerminal');
const nodes = ['Node-01', 'Node-02', 'Node-03', 'Node-04', 'Node-05'];

let totalRecs = 1240500;
let dbSizeMB = 142.5;

function rand(min, max) { return (Math.random() * (max - min) + min).toFixed(2); }

function simulateSQLInsert() {
  const now = new Date();
  const timeStr = now.toLocaleTimeString('en-GB', { hour12: false }) + '.' + String(now.getMilliseconds()).padStart(3, '0');
  const n = nodes[Math.floor(Math.random() * nodes.length)];
  
  // สุ่มค่า Data
  const t = rand(24, 35);
  const h = rand(40, 80);
  const p = rand(5, 45);

  // สร้าง SQL String แบบมี Syntax Highlighting (ใช้ Format ของ MS SQL)
  const sql = `
    <span class="sql-kw">INSERT INTO</span> <span class="sql-tbl">dbo.sensor_log</span> 
    (node_id, temp, humi, pm25, created_at) 
    <span class="sql-kw">VALUES</span> 
    (<span class="sql-str">'${n}'</span>, <span class="sql-num">${t}</span>, <span class="sql-num">${h}</span>, <span class="sql-num">${p}</span>, <span class="sql-func">CURRENT_TIMESTAMP</span>);
  `;

  const div = document.createElement('div');
  div.className = 'sql-line';
  div.innerHTML = `<span class="sql-time">[${timeStr}]</span> ${sql}`;
  
  terminal.appendChild(div);
  
  // ลบข้อความเก่าถ้าเกิน 30 บรรทัด (กันกระตุก)
  if(terminal.childElementCount > 30) { terminal.removeChild(terminal.firstChild); }
  
  // เลื่อนจอลงล่างสุด
  terminal.scrollTop = terminal.scrollHeight;

  // อัปเดตตัวเลขสถิติด้านบน
  totalRecs++;
  dbSizeMB += 0.0001; // ค่อยๆ โต
  
  document.getElementById('totalRecords').innerText = (totalRecs / 1000000).toFixed(3) + 'M';
  document.getElementById('dbSize').innerText = dbSizeMB.toFixed(2);
  document.getElementById('dbPing').innerText = Math.floor(Math.random() * 5 + 8); // สุ่มปิง 8-12ms
}

// ยิงคำสั่ง INSERT จำลองทุกๆ 3-5 วินาที
setInterval(simulateSQLInsert, 4000);

</script>

</main>
</body>
</html>