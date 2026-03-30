<?php
/**
 * _head.php  —  Shared <head> block
 * Outputs full <head>...</head> including Kanit font, Chart.js, Leaflet, and all shared CSS.
 *
 * Usage:
 *   <?php
 *     require_once '_vars.php';
 *     $page_title = $meta['title'];          // optional override
 *     require_once '_head.php';
 *   ?>
 *
 * Extra CSS can be injected by defining $extra_css before including:
 *   <?php $extra_css = '<style>.my-class{...}</style>'; ?>
 */
$page_title_tag = isset($page_title) ? $page_title : ($meta['title'] ?? 'IoT Dashboard');
?>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($page_title_tag) ?> — ESP32 IoT Dashboard</title>

<!-- ── Google Fonts: Kanit (UI + Heading) + JetBrains Mono (data/code) ── -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=JetBrains+Mono:wght@300;400;500;600&display=swap" rel="stylesheet">

<!-- ── Chart.js ── -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

<!-- ── Leaflet (load only when needed — pages requiring map define $use_leaflet = true) ── -->
<?php if (!empty($use_leaflet)): ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<?php endif; ?>

<style>
/* ══════════════════════════════════════════════════════════════
   DESIGN TOKENS
══════════════════════════════════════════════════════════════ */
:root {
  --bg-root:     #eef2f9;
  --bg-card:     #ffffff;
  --bg-card2:    #f7f9fc;

  /* Sidebar & Topbar blue gradient */
  --sidebar-grad-start: #0d1f6e;
  --sidebar-grad-mid:   #1a3a9f;
  --sidebar-grad-end:   #1e5fcc;
  --topbar-grad-start:  #1232a0;
  --topbar-grad-end:    #1e6ad6;

  /* Blue scale */
  --blue-900: #0f2a5e;  --blue-800: #1a3a7a;  --blue-700: #1e4db7;
  --blue-600: #2563eb;  --blue-500: #3b82f6;  --blue-400: #60a5fa;
  --blue-300: #93c5fd;  --blue-100: #dbeafe;  --blue-50:  #eff6ff;

  /* Neutral scale */
  --slate-700: #334155; --slate-500: #64748b; --slate-400: #94a3b8;
  --slate-300: #cbd5e1; --slate-200: #e2e8f0; --slate-100: #f1f5f9;

  /* Semantic colors */
  --green-500:  #22c55e; --green-400:  #4ade80; --green-100: #dcfce7;
  --yellow-500: #eab308; --yellow-100: #fef9c3;
  --orange-500: #f97316; --orange-100: #ffedd5;
  --red-500:    #ef4444; --red-100:    #fee2e2;
  --purple-500: #a855f7; --purple-100: #f3e8ff;
  --cyan-500:   #06b6d4; --cyan-100:   #cffafe;
  --teal-500:   #14b8a6; --teal-100:   #ccfbf1;

  /* PM2.5 AQI */
  --aqi-good:      #22c55e;
  --aqi-moderate:  #eab308;
  --aqi-sensitive: #f97316;
  --aqi-unhealthy: #ef4444;
  --aqi-very:      #a855f7;
  --aqi-hazard:    #7f1d1d;

  /* Shadows */
  --shadow-sm:   0 1px 3px rgba(15,42,94,.06), 0 1px 2px rgba(15,42,94,.04);
  --shadow-md:   0 4px 16px rgba(15,42,94,.08), 0 2px 6px rgba(15,42,94,.05);
  --shadow-lg:   0 10px 32px rgba(15,42,94,.1),  0 4px 10px rgba(15,42,94,.06);
  --shadow-blue: 0 4px 20px rgba(37,99,235,.18);

  /* Radii */
  --radius-sm: 8px;  --radius-md: 12px;
  --radius-lg: 16px; --radius-xl: 20px;

  /* Layout */
  --sidebar-w: 252px;
  --topbar-h:  62px;

  /* ── FONT SYSTEM (Kanit throughout) ── */
  --font-ui:   'Kanit', sans-serif;
  --font-head: 'Kanit', sans-serif;
  --font-mono: 'JetBrains Mono', monospace;
}

/* ══════════════════════════════════════════════════════════════
   RESET + BASE
══════════════════════════════════════════════════════════════ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { font-size: 14px; scroll-behavior: smooth; }
body {
  background: var(--bg-root);
  color: var(--slate-700);
  font-family: var(--font-ui);
  font-weight: 400;
  min-height: 100vh;
  overflow-x: hidden;
  -webkit-font-smoothing: antialiased;
}
a { text-decoration: none; color: inherit; }

/* scrollbar */
::-webkit-scrollbar { width: 5px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: var(--slate-300); border-radius: 3px; }

/* ══════════════════════════════════════════════════════════════
   SIDEBAR
══════════════════════════════════════════════════════════════ */
.sidebar {
  position: fixed; top: 0; left: 0;
  width: var(--sidebar-w); height: 100vh;
  background: linear-gradient(175deg,
    var(--sidebar-grad-start) 0%,
    var(--sidebar-grad-mid)   45%,
    var(--sidebar-grad-end)   100%);
  display: flex; flex-direction: column;
  z-index: 100;
  box-shadow: 4px 0 28px rgba(13,31,110,.35);
  transition: transform .3s cubic-bezier(.4,0,.2,1);
  overflow: hidden;
}
.sidebar::before {
  content: ''; position: absolute; top: -80px; left: -60px;
  width: 320px; height: 320px; border-radius: 50%;
  background: radial-gradient(circle, rgba(99,160,255,.18) 0%, transparent 70%);
  pointer-events: none;
}
.sidebar::after {
  content: ''; position: absolute; bottom: 20px; right: -60px;
  width: 220px; height: 220px; border-radius: 50%;
  background: radial-gradient(circle, rgba(56,120,255,.14) 0%, transparent 70%);
  pointer-events: none;
}
.sidebar-mesh {
  position: absolute; inset: 0; pointer-events: none;
  background-image:
    linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
  background-size: 32px 32px;
}

/* Logo */
.sidebar-logo {
  padding: 0 20px; height: var(--topbar-h);
  border-bottom: 1px solid rgba(255,255,255,.1);
  display: flex; align-items: center; gap: 13px;
  flex-shrink: 0; position: relative; z-index: 1;
}
.logo-mark {
  width: 40px; height: 40px; border-radius: 11px;
  background: rgba(255,255,255,.15);
  border: 1.5px solid rgba(255,255,255,.25);
  display: grid; place-items: center;
  box-shadow: 0 4px 14px rgba(0,0,0,.2), inset 0 1px 0 rgba(255,255,255,.2);
  flex-shrink: 0; backdrop-filter: blur(8px);
}
.logo-mark svg { width: 22px; height: 22px; }
.logo-name {
  font-family: var(--font-head); font-weight: 700;
  font-size: 1rem; color: #fff; letter-spacing: .02em;
}
.logo-ver { font-size: .64rem; color: rgba(180,210,255,.7); font-family: var(--font-mono); margin-top: 1px; }

/* Scroll area */
.sidebar-scroll {
  flex: 1; overflow-y: auto; padding: 10px;
  position: relative; z-index: 1;
  scrollbar-width: thin;
  scrollbar-color: rgba(255,255,255,.15) transparent;
}
.sidebar-scroll::-webkit-scrollbar { width: 4px; }
.sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 2px; }

/* Nav group label */
.nav-group-label {
  padding: 14px 12px 6px;
  font-size: .6rem; font-weight: 600;
  letter-spacing: .2em; text-transform: uppercase;
  color: rgba(160,200,255,.6);
  font-family: var(--font-mono);
}

/* Nav item */
.nav-item {
  display: flex; align-items: center; gap: 10px;
  padding: 9px 13px; border-radius: var(--radius-sm);
  cursor: pointer; margin-bottom: 2px;
  font-size: .88rem; font-weight: 400;
  color: rgba(210,230,255,.85);
  transition: all .18s cubic-bezier(.4,0,.2,1);
  position: relative;
}
.nav-item:hover {
  background: rgba(255,255,255,.1);
  color: #fff;
  transform: translateX(2px);
}
.nav-item.active {
  background: rgba(255,255,255,.15);
  color: #fff;
  font-weight: 600;
  box-shadow: 0 2px 10px rgba(0,0,0,.12), inset 0 1px 0 rgba(255,255,255,.15);
  border: 1px solid rgba(255,255,255,.15);
}
.nav-item.active::before {
  content: '';
  position: absolute; left: 0; top: 18%; height: 64%; width: 3px;
  background: #7dd3fc;
  border-radius: 0 3px 3px 0;
  box-shadow: 0 0 8px rgba(125,211,252,.7);
}
.nav-icon { font-size: .95rem; width: 20px; text-align: center; flex-shrink: 0; }
.nav-badge {
  margin-left: auto;
  background: rgba(239,68,68,.85); color: #fff;
  font-size: .6rem; padding: 2px 7px; border-radius: 10px;
  font-family: var(--font-mono); font-weight: 600;
  box-shadow: 0 2px 6px rgba(239,68,68,.4);
}
.nav-badge.info {
  background: rgba(125,211,252,.2); color: #7dd3fc;
  border: 1px solid rgba(125,211,252,.3); box-shadow: none;
}
.nav-badge.live {
  background: rgba(34,197,94,.25); color: #4ade80;
  border: 1px solid rgba(74,222,128,.3); box-shadow: none;
  animation: livePulse 1.5s infinite;
}
@keyframes livePulse { 0%,100%{opacity:1} 50%{opacity:.5} }

/* Footer / status pills */
.sidebar-footer {
  flex-shrink: 0; position: relative; z-index: 1;
  padding: 14px;
  border-top: 1px solid rgba(255,255,255,.1);
  background: rgba(0,0,0,.15);
  backdrop-filter: blur(6px);
}
.status-pill {
  display: flex; align-items: center; gap: 8px;
  padding: 7px 11px; border-radius: 8px;
  background: rgba(255,255,255,.07);
  border: 1px solid rgba(255,255,255,.1);
  margin-bottom: 6px;
  font-size: .75rem; color: rgba(200,225,255,.8);
  transition: background .15s;
}
.status-pill:hover { background: rgba(255,255,255,.11); }
.status-pill:last-child { margin-bottom: 0; }
.status-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
.dot-green  { background: #4ade80; box-shadow: 0 0 6px rgba(74,222,128,.7); }
.dot-yellow { background: #fbbf24; box-shadow: 0 0 6px rgba(251,191,36,.7); animation: blink 1.5s infinite; }
.dot-blue   { background: #93c5fd; box-shadow: 0 0 6px rgba(147,197,253,.7); }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.25} }
.status-val { margin-left: auto; font-family: var(--font-mono); font-size: .63rem; color: rgba(160,200,255,.6); }

/* ══════════════════════════════════════════════════════════════
   TOPBAR
══════════════════════════════════════════════════════════════ */
.topbar {
  position: fixed; top: 0; left: var(--sidebar-w); right: 0;
  height: var(--topbar-h);
  background: linear-gradient(95deg, var(--topbar-grad-start) 0%, var(--topbar-grad-end) 100%);
  display: flex; align-items: center; gap: 14px;
  padding: 0 26px;
  z-index: 90;
  box-shadow: 0 2px 20px rgba(18,50,160,.35);
  overflow: hidden;
}
.topbar::before {
  content: ''; position: absolute; inset: 0;
  background: linear-gradient(180deg, rgba(255,255,255,.07) 0%, transparent 100%);
  pointer-events: none;
}
.topbar::after {
  content: ''; position: absolute; inset: 0;
  background-image:
    linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px);
  background-size: 40px 40px;
  pointer-events: none;
}
.hamburger {
  display: none;
  background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.2);
  color: #fff; font-size: 1.1rem; cursor: pointer; padding: 6px 9px;
  border-radius: 8px; transition: background .15s; position: relative; z-index: 1;
}
.hamburger:hover { background: rgba(255,255,255,.22); }
.topbar-titles { position: relative; z-index: 1; }
.page-title {
  font-family: var(--font-head); font-size: 1.05rem; font-weight: 600;
  color: #fff; letter-spacing: .03em;
}
.page-sub {
  font-size: .65rem; color: rgba(180,215,255,.75);
  font-family: var(--font-mono); margin-top: 2px;
}
.topbar-right { margin-left: auto; display: flex; align-items: center; gap: 10px; position: relative; z-index: 1; }
.topbar-divider { width: 1px; height: 26px; background: rgba(255,255,255,.18); margin: 0 2px; }

.sync-chip {
  display: flex; align-items: center; gap: 8px;
  background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2);
  backdrop-filter: blur(8px); padding: 6px 14px; border-radius: 20px;
  font-size: .78rem; transition: background .15s;
}
.sync-chip:hover { background: rgba(255,255,255,.18); }
.sync-label { color: rgba(200,230,255,.85); font-weight: 500; }
.sync-countdown { font-family: var(--font-mono); font-weight: 600; color: #fff; }
.sync-ring { width: 18px; height: 18px; position: relative; flex-shrink: 0; }
.sync-ring svg { transform: rotate(-90deg); }
.sync-ring-bg   { fill: none; stroke: rgba(255,255,255,.2); stroke-width: 2.5; }
.sync-ring-fill {
  fill: none; stroke: #7dd3fc; stroke-width: 2.5; stroke-linecap: round;
  stroke-dasharray: 44; stroke-dashoffset: 44;
  transition: stroke-dashoffset .9s linear;
  filter: drop-shadow(0 0 3px rgba(125,211,252,.6));
}
.time-chip {
  display: flex; align-items: center; gap: 7px;
  background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.18);
  padding: 6px 13px; border-radius: 20px; font-size: .78rem;
  backdrop-filter: blur(8px);
}
.time-chip .clock { font-family: var(--font-mono); font-weight: 600; color: #fff; }
.time-chip .clock-icon { color: rgba(200,230,255,.8); }
.icon-btn {
  width: 36px; height: 36px; border-radius: 50%;
  background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2);
  display: grid; place-items: center; cursor: pointer;
  font-size: .95rem; position: relative; transition: all .15s; backdrop-filter: blur(6px);
}
.icon-btn:hover { background: rgba(255,255,255,.22); transform: scale(1.05); }
.icon-btn .badge {
  position: absolute; top: -2px; right: -2px; width: 10px; height: 10px;
  background: #f87171; border-radius: 50%; border: 2px solid transparent;
  box-shadow: 0 0 6px rgba(248,113,113,.6);
}
.user-btn {
  display: flex; align-items: center; gap: 9px;
  background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2);
  padding: 5px 14px 5px 5px; border-radius: 20px; cursor: pointer;
  backdrop-filter: blur(8px); transition: all .15s;
}
.user-btn:hover { background: rgba(255,255,255,.2); box-shadow: 0 4px 14px rgba(0,0,0,.15); }
.user-avatar {
  width: 28px; height: 28px; border-radius: 50%;
  background: linear-gradient(135deg, rgba(255,255,255,.35), rgba(255,255,255,.15));
  border: 1.5px solid rgba(255,255,255,.4);
  display: grid; place-items: center; color: #fff; font-size: .75rem; font-weight: 700;
  box-shadow: 0 2px 6px rgba(0,0,0,.15);
}
.user-name { font-size: .82rem; font-weight: 500; color: #fff; }

/* ══════════════════════════════════════════════════════════════
   MAIN CONTENT AREA
══════════════════════════════════════════════════════════════ */
.main {
  margin-left: var(--sidebar-w);
  margin-top: var(--topbar-h);
  padding: 24px;
  min-height: calc(100vh - var(--topbar-h));
}

/* ── Section header ── */
.section-header {
  display: flex; align-items: center; gap: 10px;
  margin-bottom: 14px; margin-top: 6px;
}
.section-header h2 {
  font-family: var(--font-head); font-size: .95rem; font-weight: 600;
  color: var(--blue-900); letter-spacing: .02em; white-space: nowrap;
}
.section-line { flex: 1; height: 1px; background: var(--slate-200); }
.section-meta { font-size: .7rem; color: var(--slate-400); font-family: var(--font-mono); white-space: nowrap; }

/* ── Panel ── */
.panel {
  background: var(--bg-card);
  border: 1px solid var(--slate-200);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
  transition: box-shadow .2s;
}
.panel:hover { box-shadow: var(--shadow-md); }
.panel-header {
  display: flex; align-items: center; gap: 10px;
  padding: 13px 18px;
  border-bottom: 1px solid var(--slate-100);
}
.panel-dot { width: 9px; height: 9px; border-radius: 50%; }
.pd-blue   { background: var(--blue-500);   box-shadow: 0 0 5px var(--blue-400); }
.pd-green  { background: var(--green-500);  box-shadow: 0 0 5px var(--green-500); }
.pd-orange { background: var(--orange-500); box-shadow: 0 0 5px var(--orange-500); }
.pd-yellow { background: var(--yellow-500); box-shadow: 0 0 5px var(--yellow-500); }
.pd-purple { background: var(--purple-500); box-shadow: 0 0 5px var(--purple-500); }
.pd-red    { background: var(--red-500);    box-shadow: 0 0 5px var(--red-500); }
.pd-cyan   { background: var(--cyan-500);   box-shadow: 0 0 5px var(--cyan-500); }
.pd-teal   { background: var(--teal-500);   box-shadow: 0 0 5px var(--teal-500); }
.panel-title {
  font-family: var(--font-head); font-size: .82rem; font-weight: 600;
  color: var(--blue-900); text-transform: uppercase; letter-spacing: .06em;
}
.panel-sub { font-size: .68rem; color: var(--slate-400); margin-left: auto; font-family: var(--font-mono); }
.panel-body    { padding: 18px; }
.panel-body-sm { padding: 12px 16px; }

/* ── Grids ── */
.grid-2-1 { display: grid; grid-template-columns: 2fr 1fr;      gap: 16px; margin-bottom: 16px; }
.grid-3   { display: grid; grid-template-columns: repeat(3,1fr); gap: 16px; margin-bottom: 16px; }
.grid-2   { display: grid; grid-template-columns: 1fr 1fr;       gap: 16px; margin-bottom: 16px; }
.grid-4   { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; margin-bottom: 16px; }
.grid-5   { display: grid; grid-template-columns: repeat(5,1fr); gap: 14px; margin-bottom: 22px; }

/* ── Summary cards ── */
.summary-card {
  background: var(--bg-card); border: 1px solid var(--slate-200);
  border-radius: var(--radius-lg); padding: 18px 18px 14px;
  box-shadow: var(--shadow-sm); position: relative; overflow: hidden;
  transition: all .2s; cursor: default;
}
.summary-card:hover { box-shadow: var(--shadow-md); transform: translateY(-1px); }
.summary-card::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
  border-radius: var(--radius-lg) var(--radius-lg) 0 0;
}
.sc-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.sc-label { font-size: .72rem; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; color: var(--slate-400); }
.sc-icon-wrap { width: 32px; height: 32px; border-radius: 8px; display: grid; place-items: center; font-size: .95rem; }
.sc-value { font-family: var(--font-mono); font-size: 2rem; font-weight: 600; color: var(--blue-900); line-height: 1; margin-bottom: 6px; }
.sc-unit  { font-size: .8rem; color: var(--slate-400); font-weight: 400; margin-left: 3px; }
.sc-delta { display: flex; align-items: center; gap: 4px; font-size: .72rem; font-family: var(--font-mono); }
.delta-up   { color: var(--green-500); }
.delta-down { color: var(--red-500); }
.delta-note { color: var(--slate-400); font-size: .65rem; }

/* ── Badges ── */
.badge-pill { display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; border-radius: 10px; font-size: .68rem; font-weight: 600; }
.badge-online  { background: var(--green-100); color: #16a34a; }
.badge-warn    { background: var(--yellow-100); color: #92400e; }
.badge-offline { background: var(--red-100);   color: var(--red-500); }
.badge-info    { background: var(--blue-100);  color: var(--blue-700); }
.badge-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }

/* ── Bar battery ── */
.bar-batt { height: 7px; border-radius: 3px; background: var(--slate-200); width: 60px; overflow: hidden; }
.bar-batt-fill { height: 100%; border-radius: 3px; transition: width .4s; }

/* ── RSSI bars ── */
.rssi-bars { display: flex; gap: 2px; align-items: flex-end; height: 13px; }
.rssi-b    { width: 4px; border-radius: 1px; background: var(--slate-200); }
.rssi-b.on { background: var(--blue-500); }

/* ── Gauge ── */
.gauge-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.gauge-item { display: flex; flex-direction: column; align-items: center; gap: 4px; }
.gauge-svg  { width: 110px; height: 66px; }
.gauge-lbl  { font-size: .68rem; color: var(--slate-400); text-align: center; letter-spacing: .06em; text-transform: uppercase; font-weight: 600; }
.gauge-num  { font-family: var(--font-mono); font-size: .95rem; font-weight: 600; color: var(--slate-700); }

/* ── Alert items ── */
.alert-item { display: flex; align-items: flex-start; gap: 10px; padding: 10px 0; border-bottom: 1px solid var(--slate-100); }
.alert-item:last-child { border-bottom: none; }
.alert-icon-wrap { width: 30px; height: 30px; border-radius: 8px; display: grid; place-items: center; font-size: .9rem; flex-shrink: 0; }
.ai-red    { background: var(--red-100); }   .ai-yellow { background: var(--yellow-100); }
.ai-green  { background: var(--green-100); } .ai-blue   { background: var(--blue-50); }
.alert-content { flex: 1; }
.alert-msg  { font-size: .82rem; color: var(--slate-700); font-weight: 500; line-height: 1.4; }
.alert-time { font-size: .66rem; color: var(--slate-400); font-family: var(--font-mono); }

/* ── Timeline ── */
.tl-item  { display: flex; gap: 12px; padding: 5px 0; }
.tl-aside { display: flex; flex-direction: column; align-items: center; }
.tl-dot   { width: 10px; height: 10px; border-radius: 50%; border: 2px solid var(--blue-400); background: white; flex-shrink: 0; margin-top: 4px; }
.tl-dot.ok  { border-color: var(--green-500); background: var(--green-100); }
.tl-dot.err { border-color: var(--red-500);   background: var(--red-100); }
.tl-seg   { width: 1px; flex: 1; background: var(--slate-200); min-height: 14px; margin: 2px 0; }
.tl-title { font-size: .8rem; color: var(--slate-700); font-weight: 500; }
.tl-time  { font-size: .64rem; color: var(--slate-400); font-family: var(--font-mono); }

/* ── Device table ── */
.device-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
.device-table th { color: var(--slate-400); font-weight: 600; letter-spacing: .08em; text-transform: uppercase; font-size: .62rem; padding: 8px 12px; border-bottom: 1px solid var(--slate-200); text-align: left; }
.device-table td { padding: 10px 12px; border-bottom: 1px solid var(--slate-100); vertical-align: middle; }
.device-table tr:last-child td { border-bottom: none; }
.device-table tr:hover td { background: var(--blue-50); }
.node-name { font-family: var(--font-mono); font-size: .78rem; font-weight: 600; color: var(--blue-700); }
.node-loc  { font-size: .66rem; color: var(--slate-400); }

/* ── PM2.5 Banner ── */
.pm25-banner { background: var(--bg-card); border: 1px solid var(--slate-200); border-radius: var(--radius-xl); padding: 22px 26px; box-shadow: var(--shadow-md); margin-bottom: 22px; display: grid; grid-template-columns: auto 1fr auto; gap: 28px; align-items: center; position: relative; overflow: hidden; }
.pm25-banner::before { content: ''; position: absolute; top: 0; left: 0; bottom: 0; width: 5px; border-radius: var(--radius-xl) 0 0 var(--radius-xl); background: var(--aqi-good); transition: background .5s; }
.pm25-banner.aqi-moderate::before  { background: var(--aqi-moderate); }
.pm25-banner.aqi-sensitive::before { background: var(--aqi-sensitive); }
.pm25-banner.aqi-unhealthy::before { background: var(--aqi-unhealthy); }
.pm25-big { font-family: var(--font-mono); font-size: 4rem; font-weight: 600; line-height: 1; color: var(--aqi-good); transition: color .5s; letter-spacing: -.03em; }
.pm25-unit-lbl { font-size: .78rem; color: var(--slate-400); margin-top: 4px; font-family: var(--font-mono); }
.pm25-info { display: flex; flex-direction: column; gap: 10px; }
.aqi-badge { display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; border-radius: 20px; font-weight: 600; font-size: .88rem; width: fit-content; background: var(--green-100); color: var(--green-500); transition: all .5s; }
.aqi-badge .aqi-dot { width: 9px; height: 9px; border-radius: 50%; background: currentColor; }
.pm25-desc { font-size: .84rem; color: var(--slate-500); line-height: 1.5; max-width: 380px; }
.pm25-recommendation { display: flex; align-items: flex-start; gap: 8px; padding: 9px 13px; background: var(--blue-50); border-radius: 8px; border: 1px solid var(--blue-100); font-size: .8rem; color: var(--slate-600); }
.pm25-scale { display: flex; flex-direction: column; gap: 6px; min-width: 160px; }
.aqi-row { display: flex; align-items: center; gap: 8px; padding: 6px 10px; border-radius: 7px; font-size: .76rem; border: 1px solid transparent; }
.aqi-pip { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.aqi-range { font-family: var(--font-mono); color: var(--slate-400); margin-left: auto; font-size: .65rem; }

/* ── Donut legend ── */
.donut-legend { display: flex; flex-direction: column; gap: 7px; }
.dl-item { display: flex; align-items: center; gap: 8px; font-size: .8rem; }
.dl-dot  { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }
.dl-name { color: var(--slate-600); flex: 1; }
.dl-pct  { font-family: var(--font-mono); font-weight: 600; color: var(--slate-700); }

/* ── Footer ── */
.footer {
  margin-top: 10px; padding: 16px 0 4px;
  border-top: 1px solid var(--slate-200);
  display: flex; align-items: center; gap: 14px; flex-wrap: wrap;
  font-size: .7rem; color: var(--slate-400); font-family: var(--font-mono);
}
.footer-sep    { color: var(--slate-300); }
.footer-status { color: var(--green-500); font-weight: 600; }
.footer-right  { margin-left: auto; }

/* ── Sidebar overlay (mobile) ── */
.sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(15,42,94,.3); z-index: 99; backdrop-filter: blur(2px); }
.sidebar-overlay.show { display: block; }

/* ── Map (Leaflet) ── */
.leaflet-map-wrap { border-radius: var(--radius-md); overflow: hidden; border: 1px solid var(--slate-200); }
.leaflet-control-attribution { font-size: .58rem !important; background: rgba(255,255,255,.75) !important; backdrop-filter: blur(4px); }
.map-stat-chip { background: rgba(255,255,255,.88); backdrop-filter: blur(6px); border: 1px solid rgba(255,255,255,.6); border-radius: 7px; padding: 5px 10px; font-size: .66rem; font-family: var(--font-mono); box-shadow: 0 2px 8px rgba(0,0,0,.15); display: flex; align-items: center; gap: 5px; }
.msc-dot { width: 7px; height: 7px; border-radius: 50%; }
.map-tb-btn { background: rgba(255,255,255,.92); backdrop-filter: blur(6px); border: 1px solid var(--slate-200); border-radius: 8px; padding: 5px 10px; font-size: .7rem; font-family: var(--font-ui); font-weight: 500; color: var(--blue-700); cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,.1); transition: all .15s; white-space: nowrap; }
.map-tb-btn:hover  { background: var(--blue-50); border-color: var(--blue-300); }
.map-tb-btn.active { background: var(--blue-600); color: white; border-color: var(--blue-700); }

/* ── Fade-up animation ── */
@keyframes fadeUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
.fade-up   { animation: fadeUp .35s both; }
.fade-up-1 { animation: fadeUp .35s .05s both; }
.fade-up-2 { animation: fadeUp .35s .10s both; }
.fade-up-3 { animation: fadeUp .35s .15s both; }
.fade-up-4 { animation: fadeUp .35s .20s both; }
.fade-up-5 { animation: fadeUp .35s .25s both; }

/* ══════════════════════════════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════════════════════════════ */
@media (max-width: 1280px) {
  .grid-5 { grid-template-columns: repeat(3,1fr); }
  .pm25-banner { grid-template-columns: auto 1fr; }
  .pm25-scale  { display: none; }
}
@media (max-width: 1024px) {
  .grid-5   { grid-template-columns: repeat(3,1fr); }
  .grid-2-1 { grid-template-columns: 1fr; }
  .grid-3   { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 768px) {
  :root { --sidebar-w: 0px; }
  .sidebar { width: 252px; transform: translateX(-252px); }
  .sidebar.open { transform: translateX(0); }
  .topbar { left: 0; }
  .main { margin-left: 0; padding: 14px; }
  .hamburger { display: flex; }
  .grid-5 { grid-template-columns: 1fr 1fr; }
  .grid-3 { grid-template-columns: 1fr; }
  .grid-2 { grid-template-columns: 1fr; }
  .grid-2-1 { grid-template-columns: 1fr; }
  .pm25-banner { grid-template-columns: 1fr; gap: 16px; }
  .pm25-big { font-size: 3rem; }
  .gauge-grid { grid-template-columns: repeat(4,1fr); }
  .sync-chip .sync-label { display: none; }
  .topbar-divider { display: none; }
}
@media (max-width: 480px) {
  .grid-5 { grid-template-columns: 1fr; }
  .time-chip { display: none; }
  .gauge-grid { grid-template-columns: 1fr 1fr; }
}
</style>

<?php if (!empty($extra_css)) echo $extra_css; ?>
</head>
