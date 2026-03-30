<?php
/**
 * _vars.php  —  Global config shared across all pages
 * Include this first in every page before any output.
 *
 * Usage:  <?php require_once '_vars.php'; ?>
 */

// ── Active page detection ──────────────────────────
$current_page = basename($_SERVER['PHP_SELF']);

// ── Site info ──────────────────────────────────────
define('SITE_NAME',    'ESP32 HUB');
define('SITE_VERSION', 'v2.5');
define('SITE_LOCATION','Nakhon Phanom');

// ── Navigation menu structure ──────────────────────
// 'file'   => PHP filename (used to auto-detect active)
// 'label'  => Display text
// 'icon'   => Emoji icon
// 'badge'  => optional ['text'=>'...', 'type'=>'danger|info|live']
$nav_menu = [
  'Overview' => [
    ['file'=>'index.php',           'label'=>'Dashboard',       'icon'=>'⬡'],
    ['file'=>'live-sensors.php',    'label'=>'Live Sensors',    'icon'=>'📡'],
    ['file'=>'analytics.php',       'label'=>'Analytics',       'icon'=>'📊'],
  ],
  'Air Quality' => [
    ['file'=>'pm25-monitor.php',    'label'=>'PM2.5 Monitor',   'icon'=>'🌫️',  'badge'=>['text'=>'LIVE','type'=>'info']],
    ['file'=>'aqi-history.php',     'label'=>'AQI History',     'icon'=>'🌬️'],
    ['file'=>'air-map.php',         'label'=>'Air Map',         'icon'=>'🗺️'],
  ],
  'Sensors' => [
    ['file'=>'esp32-nodes.php',     'label'=>'ESP32 Nodes',     'icon'=>'🔲',  'badge'=>['text'=>'5','type'=>'info']],
    ['file'=>'temp-humidity.php',   'label'=>'Temp / Humidity', 'icon'=>'🌡️'],
    ['file'=>'soil-water.php',      'label'=>'Soil &amp; Water','icon'=>'💧'],
    ['file'=>'light-uv.php',        'label'=>'Light &amp; UV',  'icon'=>'💡'],
    ['file'=>'power-monitor.php',   'label'=>'Power Monitor',   'icon'=>'⚡'],
  ],
  'System' => [
    ['file'=>'mysql-database.php',  'label'=>'MySQL Database',  'icon'=>'🗄️'],
    ['file'=>'settings.php',        'label'=>'Settings',        'icon'=>'⚙️'],
    ['file'=>'alerts.php',          'label'=>'Alerts',          'icon'=>'🔔',  'badge'=>['text'=>'3','type'=>'danger']],
  ],
];

// ── Status pills (sidebar footer) ─────────────────
$status_pills = [
  ['dot'=>'dot-green',  'label'=>'MySQL Server', 'val'=>'Connected'],
  ['dot'=>'dot-blue',   'label'=>'MQTT Broker',  'val'=>'Active'],
  ['dot'=>'dot-yellow', 'label'=>'Node-03 Battery','val'=>'18%'],
];

// ── Page meta map (title + breadcrumb per page) ───
$page_meta = [
  'index.php'          => ['title'=>'IoT Dashboard',   'sub'=>'HOME / OVERVIEW'],
  'live-sensors.php'   => ['title'=>'Live Sensors',    'sub'=>'HOME / LIVE SENSORS'],
  'analytics.php'      => ['title'=>'Analytics',       'sub'=>'HOME / ANALYTICS'],
  'pm25-monitor.php'   => ['title'=>'PM2.5 Monitor',   'sub'=>'HOME / AIR QUALITY / PM2.5'],
  'aqi-history.php'    => ['title'=>'AQI History',     'sub'=>'HOME / AIR QUALITY / AQI HISTORY'],
  'air-map.php'        => ['title'=>'Air Map',         'sub'=>'HOME / AIR QUALITY / AIR MAP'],
  'esp32-nodes.php'    => ['title'=>'ESP32 Nodes',     'sub'=>'HOME / SENSORS / NODES'],
  'temp-humidity.php'  => ['title'=>'Temp / Humidity', 'sub'=>'HOME / SENSORS / TEMP-HUMIDITY'],
  'soil-water.php'     => ['title'=>'Soil &amp; Water','sub'=>'HOME / SENSORS / SOIL-WATER'],
  'light-uv.php'       => ['title'=>'Light &amp; UV',  'sub'=>'HOME / SENSORS / LIGHT-UV'],
  'power-monitor.php'  => ['title'=>'Power Monitor',   'sub'=>'HOME / SENSORS / POWER'],
  'mysql-database.php' => ['title'=>'MySQL Database',  'sub'=>'HOME / SYSTEM / DATABASE'],
  'settings.php'       => ['title'=>'Settings',        'sub'=>'HOME / SYSTEM / SETTINGS'],
  'alerts.php'         => ['title'=>'Alerts',          'sub'=>'HOME / SYSTEM / ALERTS'],
];

$meta = $page_meta[$current_page] ?? ['title'=>'Dashboard', 'sub'=>'HOME'];
