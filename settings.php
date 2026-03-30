<?php
/* ── 1. Load global config ───────────────────────── */
require_once '_vars.php';

/* ── 2. Page-specific options ────────────────────── */
$page_title = "IoT Dashboard - Settings";
$use_leaflet = false;

// CSS เฉพาะหน้า Settings
$extra_css = '
<style>
  /* ── Form Elements ── */
  .form-section {
    background: var(--bg-card); border: 1px solid var(--slate-200);
    border-radius: var(--radius-lg); padding: 24px; margin-bottom: 20px;
    box-shadow: var(--shadow-sm);
  }
  .form-header {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--slate-100);
  }
  .form-header h3 { font-family: var(--font-head); font-weight: 700; color: var(--blue-900); font-size: 1.1rem; }
  .form-icon { font-size: 1.5rem; }

  .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
  @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } }

  .form-group { display: flex; flex-direction: column; gap: 6px; }
  .form-label { font-size: .8rem; font-weight: 600; color: var(--slate-600); }
  .form-label span { color: var(--slate-400); font-weight: 400; font-size: .7rem; margin-left: 6px; }
  
  .form-input, .form-select {
    padding: 10px 14px; border: 1px solid var(--slate-300); border-radius: var(--radius-sm);
    font-family: var(--font-ui); font-size: .85rem; color: var(--slate-700); background: var(--slate-50);
    transition: all 0.2s; outline: none; width: 100%;
  }
  .form-input:focus, .form-select:focus { border-color: var(--blue-500); background: white; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
  .form-input.mono { font-family: var(--font-mono); font-weight: 500; }

  /* ── Custom Toggle Switch ── */
  .toggle-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: var(--slate-50); border: 1px solid var(--slate-200); border-radius: var(--radius-sm); }
  .toggle-info { display: flex; flex-direction: column; gap: 2px; }
  .toggle-title { font-weight: 600; font-size: .85rem; color: var(--slate-700); }
  .toggle-desc { font-size: .7rem; color: var(--slate-500); }

  .toggle-switch { position: relative; display: inline-block; width: 44px; height: 24px; flex-shrink: 0; }
  .toggle-switch input { opacity: 0; width: 0; height: 0; }
  .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: var(--slate-300); transition: .3s; border-radius: 24px; }
  .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .3s; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
  input:checked + .slider { background-color: var(--green-500); }
  input:checked + .slider:before { transform: translateX(20px); }

  /* ── Save Action Bar ── */
  .save-bar {
    position: sticky; bottom: 20px; background: rgba(255,255,255,0.9); backdrop-filter: blur(8px);
    border: 1px solid var(--slate-200); border-radius: var(--radius-lg); padding: 16px 24px;
    display: flex; justify-content: space-between; align-items: center;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1); z-index: 50; margin-top: 30px;
  }
  .save-info { font-size: .8rem; color: var(--slate-500); }
  .btn-wrap { display: flex; gap: 12px; }
  .btn-cancel { padding: 10px 20px; background: white; border: 1px solid var(--slate-300); color: var(--slate-600); border-radius: var(--radius-sm); font-weight: 600; cursor: pointer; transition: all 0.2s; }
  .btn-cancel:hover { background: var(--slate-100); color: var(--slate-800); }
  .btn-save { padding: 10px 24px; background: var(--blue-600); border: none; color: white; border-radius: var(--radius-sm); font-weight: 600; cursor: pointer; box-shadow: 0 4px 10px rgba(37,99,235,0.3); transition: all 0.2s; }
  .btn-save:hover { background: var(--blue-700); transform: translateY(-1px); }
  
  /* Toast Notification */
  #toast { visibility: hidden; min-width: 250px; background-color: var(--green-600); color: #fff; text-align: center; border-radius: var(--radius-sm); padding: 12px; position: fixed; z-index: 1000; left: 50%; bottom: 80px; transform: translateX(-50%); font-weight: 600; box-shadow: 0 4px 12px rgba(0,0,0,0.2); opacity: 0; transition: opacity 0.3s, bottom 0.3s; }
  #toast.show { visibility: visible; opacity: 1; bottom: 90px; }
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
    <h2>⚙️ System Configuration</h2>
    <div class="section-line"></div>
    <div class="section-meta">ตั้งค่าระบบ ควบคุมอัตโนมัติ และฐานข้อมูล</div>
  </div>

  <div class="form-section">
    <div class="form-header">
      <div class="form-icon">🏢</div>
      <h3>General Profile</h3>
    </div>
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Project / Farm Name</label>
        <input type="text" class="form-input" value="พรศิริฟาร์มสุข (Pornsiri Farm Sook)">
      </div>
      <div class="form-group">
        <label class="form-label">Location / Site</label>
        <input type="text" class="form-input" value="Kalasin, Thailand">
      </div>
      <div class="form-group">
        <label class="form-label">Timezone</label>
        <select class="form-select">
          <option value="Asia/Bangkok" selected>Asia/Bangkok (UTC+07:00)</option>
          <option value="UTC">UTC Standard Time</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Administrator Email</label>
        <input type="email" class="form-input" value="admin@pornsirifarm.com">
      </div>
    </div>
  </div>

  <div class="form-section">
    <div class="form-header">
      <div class="form-icon">🤖</div>
      <h3>Smart Farm Automation Rules</h3>
    </div>
    
    <div style="display:flex; flex-direction:column; gap:12px; margin-bottom:20px;">
      <div class="toggle-row">
        <div class="toggle-info">
          <div class="toggle-title">💧 Auto Irrigation (ระบบรดน้ำอัตโนมัติ)</div>
          <div class="toggle-desc">เปิดวาล์วน้ำเมื่อความชื้นดินต่ำกว่าเกณฑ์ และปิดเมื่อความชื้นเพียงพอ</div>
        </div>
        <label class="toggle-switch"><input type="checkbox" checked><span class="slider"></span></label>
      </div>
      <div class="toggle-row">
        <div class="toggle-info">
          <div class="toggle-title">💡 Auto Grow Light (ไฟปลูกพืชเสริมแสง)</div>
          <div class="toggle-desc">เปิดสปอร์ตไลท์ LED อัตโนมัติเมื่อแสงธรรมชาติน้อยกว่าเกณฑ์ที่กำหนด</div>
        </div>
        <label class="toggle-switch"><input type="checkbox" checked><span class="slider"></span></label>
      </div>
    </div>

    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Soil Moisture Threshold (Min) <span>%</span></label>
        <input type="number" class="form-input mono" value="30">
      </div>
      <div class="form-group">
        <label class="form-label">Light Intensity Threshold (Min) <span>Lux</span></label>
        <input type="number" class="form-input mono" value="1000">
      </div>
      <div class="form-group">
        <label class="form-label">Temperature Alert (Max) <span>°C</span></label>
        <input type="number" class="form-input mono" value="35.0" step="0.1">
      </div>
      <div class="form-group">
        <label class="form-label">PM2.5 Alert (Max) <span>µg/m³</span></label>
        <input type="number" class="form-input mono" value="55.0" step="0.1">
      </div>
    </div>
  </div>

  <div class="form-section">
    <div class="form-header">
      <div class="form-icon">🔌</div>
      <h3>Connections (MQTT & Database)</h3>
    </div>
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">MQTT Broker IP / Domain</label>
        <input type="text" class="form-input mono" value="broker.emqx.io">
      </div>
      <div class="form-group">
        <label class="form-label">MQTT Port</label>
        <input type="number" class="form-input mono" value="1883">
      </div>
      <div class="form-group">
        <label class="form-label">Base Topic</label>
        <input type="text" class="form-input mono" value="esp32/sensor/#">
      </div>
      <div class="form-group">
        <label class="form-label">Data Sync Interval <span>(Seconds)</span></label>
        <input type="number" class="form-input mono" value="3600">
      </div>
    </div>

    <div style="height:1px; background:var(--slate-200); margin: 20px 0;"></div>

    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Database Engine</label>
        <select class="form-select mono">
          <option value="sqlsrv" selected>SQL Server (PDO_SQLSRV) - MS SQL 2019</option>
          <option value="mysql">MySQL / MariaDB</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">DB Host</label>
        <input type="text" class="form-input mono" value="127.0.0.1, 1433">
      </div>
      <div class="form-group">
        <label class="form-label">Database Name</label>
        <input type="text" class="form-input mono" value="Pornsiri_Farm_IoT">
      </div>
      <div class="form-group">
        <label class="form-label">Table Prefix</label>
        <input type="text" class="form-input mono" value="dbo.">
      </div>
    </div>
  </div>

  <div class="save-bar">
    <div class="save-info">Unsaved changes will be lost if you leave this page.</div>
    <div class="btn-wrap">
      <button class="btn-cancel" onclick="window.location.reload();">Discard</button>
      <button class="btn-save" onclick="saveSettings()">💾 Save Configuration</button>
    </div>
  </div>

  <div id="toast">✅ Settings saved successfully!</div>

<?php
/* ── 5. Footer + Shared JS ───────────────────────── */
require_once '_footer.php';
?>

<script>
function saveSettings() {
  // เปลี่ยนปุ่มเป็นสถานะกำลังโหลด
  const btn = document.querySelector('.btn-save');
  const originalText = btn.innerHTML;
  btn.innerHTML = '⏳ Saving...';
  btn.style.opacity = '0.8';
  btn.disabled = true;

  // จำลองการหน่วงเวลาบันทึกข้อมูล (AJAX request)
  setTimeout(() => {
    btn.innerHTML = originalText;
    btn.style.opacity = '1';
    btn.disabled = false;
    
    // แสดง Toast Notification สวยๆ
    const toast = document.getElementById("toast");
    toast.className = "show";
    setTimeout(function(){ toast.className = toast.className.replace("show", ""); }, 3000);
    
  }, 800);
}
</script>

</main>
</body>
</html>