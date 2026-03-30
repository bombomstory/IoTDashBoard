<?php
/**
 * _footer.php  —  Shared page footer strip + shared JS utilities
 * Must be included just before </body>
 *
 * Usage:  <?php require_once '_footer.php'; ?>
 *
 * Optional:  $extra_js  — additional inline <script> block content (no <script> tags)
 */
?>

<!-- ── Footer strip ── -->
<div class="footer">
  <span>ESP32 IoT Dashboard</span>
  <span class="footer-sep">·</span>
  <span>MySQL: <span class="footer-status">ONLINE</span></span>
  <span class="footer-sep">·</span>
  <span>MQTT: <span class="footer-status">ACTIVE</span></span>
  <span class="footer-sep">·</span>
  <span>Sync interval: <span style="color:var(--blue-600);font-weight:600">3600s</span></span>
  <span class="footer-sep">·</span>
  <span id="footerClock"></span>
  <span class="footer-right"><?= SITE_VERSION ?> · PMS5003 + DHT22 + BH1750 + BMP280</span>
</div>

<!-- ══════════════════════════════════════════════════════
     SHARED JS  —  clock, sync countdown, sidebar toggle
══════════════════════════════════════════════════════ -->
<script>
/* ── Clock ───────────────────────────────────────── */
function updateClock() {
  const t = new Date().toLocaleTimeString('th-TH', {hour12: false});
  const el = document.getElementById('clockDisplay');
  const fc = document.getElementById('footerClock');
  if (el) el.textContent = t;
  if (fc) fc.textContent = t;
}
setInterval(updateClock, 1000);
updateClock();

/* ── Sync countdown ───────────────────────────────── */
const SYNC_INTERVAL = 3600;
let _syncLeft    = SYNC_INTERVAL - (new Date().getSeconds() + new Date().getMinutes() * 60);
let _syncElapsed = SYNC_INTERVAL - _syncLeft;

function _doSync() {
  const t = new Date().toLocaleTimeString('th-TH', {hour12:false, hour:'2-digit', minute:'2-digit'});
  const lbl = document.getElementById('lastSyncLabel');
  if (lbl) lbl.textContent = 'Last sync: ' + t;
  if (typeof onSyncEvent === 'function') onSyncEvent(t);  // hook for page-level handler
}

function _tickSync() {
  _syncLeft--;
  _syncElapsed++;
  if (_syncLeft <= 0) { _syncLeft = SYNC_INTERVAL; _syncElapsed = 0; _doSync(); }
  const m = String(Math.floor(_syncLeft / 60)).padStart(2, '0');
  const s = String(_syncLeft % 60).padStart(2, '0');
  const el = document.getElementById('nextSync');
  const ring = document.getElementById('syncRingFill');
  if (el)   el.textContent = m + ':' + s;
  if (ring) ring.style.strokeDashoffset = 44 - (_syncElapsed / SYNC_INTERVAL) * 44;
}
setInterval(_tickSync, 1000);
_tickSync();

// Set "Last sync" to current hour:00
(function () {
  const lbl = document.getElementById('lastSyncLabel');
  if (!lbl) return;
  lbl.textContent = 'Last sync: ' +
    new Date(Date.now() - (new Date().getMinutes() * 60 + new Date().getSeconds()) * 1000)
      .toLocaleTimeString('th-TH', {hour12:false, hour:'2-digit', minute:'2-digit'});
})();

/* ── Sidebar toggle ───────────────────────────────── */
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('overlay').classList.toggle('show');
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('overlay').classList.remove('show');
}

/* ── AQI helpers (shared across pages) ────────────── */
const AQI_LEVELS = [
  { max:12,    label:'ดี (Good)',                  color:'#22c55e', bg:'#dcfce7', icon:'😊', cls:'good',
    rec:'สามารถทำกิจกรรมกลางแจ้งได้ตามปกติ ไม่จำเป็นต้องสวมหน้ากาก',
    desc:'คุณภาพอากาศดีมาก ฝุ่น PM2.5 ต่ำกว่าเกณฑ์ WHO เหมาะสำหรับทุกกิจกรรม', short:'ดี' },
  { max:35.4,  label:'ปานกลาง (Moderate)',        color:'#eab308', bg:'#fef9c3', icon:'😐', cls:'moderate',
    rec:'กลุ่มเสี่ยง (โรคหอบหืด โรคหัวใจ) ควรระวัง',
    desc:'คุณภาพอากาศอยู่ในระดับพอรับได้ กลุ่มผู้มีความเสี่ยงควรระมัดระวัง', short:'ปานกลาง' },
  { max:55.4,  label:'กลุ่มเสี่ยง (USG)',         color:'#f97316', bg:'#ffedd5', icon:'😷', cls:'sensitive',
    rec:'กลุ่มเสี่ยงควรงดกิจกรรมกลางแจ้ง พิจารณาสวมหน้ากาก N95',
    desc:'อาจส่งผลต่อสุขภาพของกลุ่มเสี่ยง เช่น เด็ก ผู้สูงอายุ และผู้ป่วยโรคทางเดินหายใจ', short:'กลุ่มเสี่ยง' },
  { max:150.4, label:'ไม่ดีต่อสุขภาพ (Unhealthy)',color:'#ef4444', bg:'#fee2e2', icon:'🚨', cls:'unhealthy',
    rec:'ทุกคนควรสวมหน้ากาก N95 และหลีกเลี่ยงกิจกรรมกลางแจ้ง',
    desc:'คุณภาพอากาศอยู่ในระดับที่เป็นอันตรายต่อสุขภาพทุกคน', short:'ไม่ดี' },
  { max:999,   label:'อันตราย (Hazardous)',        color:'#a855f7', bg:'#f3e8ff', icon:'☠️', cls:'very',
    rec:'ห้ามออกนอกอาคาร ปิดหน้าต่าง สวมหน้ากาก N95 ตลอดเวลา',
    desc:'ระดับอันตราย กระทบต่อสุขภาพอย่างรุนแรง ทุกคนได้รับผลกระทบ', short:'อันตราย' },
];
function getAQILevel(v) { return AQI_LEVELS.find(l => v <= l.max) || AQI_LEVELS[4]; }

/* ── Chart.js shared tooltip preset ──────────────── */
const CHART_TOOLTIP = {
  backgroundColor:'#fff', borderColor:'#e2e8f0', borderWidth:1,
  titleColor:'#1e4db7', bodyColor:'#334155',
  titleFont:{ family:'Kanit', size:11, weight:'600' },
  bodyFont: { family:'Kanit', size:11 },
  padding:10, cornerRadius:8,
};
const CHART_LEGEND = {
  labels:{ color:'#64748b', font:{ family:'Kanit', size:12 }, boxWidth:12, padding:16 }
};
const CHART_TICK_STYLE = { color:'#94a3b8', font:{ family:'JetBrains Mono', size:9 } };
</script>

<?php if (!empty($extra_js)): ?>
<script>
<?= $extra_js ?>
</script>
<?php endif; ?>
