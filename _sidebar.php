<?php
/**
 * _sidebar.php  —  Shared Sidebar
 * Requires $nav_menu, $status_pills, $current_page from _vars.php
 *
 * Usage:  <?php require_once '_sidebar.php'; ?>
 */
?>
<div class="sidebar-overlay" id="overlay" onclick="closeSidebar()"></div>

<nav class="sidebar" id="sidebar">
  <div class="sidebar-mesh"></div>

  <!-- Logo -->
  <div class="sidebar-logo">
    <div class="logo-mark">
      <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8">
        <rect x="2" y="3" width="20" height="14" rx="2.5"/>
        <circle cx="8" cy="10" r="1.5" fill="white"/>
        <circle cx="16" cy="10" r="1.5" fill="white"/>
        <path d="M8 10h8" stroke-dasharray="2 2"/>
        <path d="M8 21h8M12 17v4"/>
      </svg>
    </div>
    <div>
      <div class="logo-name"><?= SITE_NAME ?></div>
      <div class="logo-ver">IoT Dashboard <?= SITE_VERSION ?></div>
    </div>
  </div>

  <!-- Navigation -->
  <div class="sidebar-scroll">
    <?php foreach ($nav_menu as $group => $items): ?>
    <div class="nav-group-label"><?= htmlspecialchars($group) ?></div>
    <?php foreach ($items as $item):
      $is_active = ($current_page === $item['file']);
      $active_class = $is_active ? ' active' : '';
      $badge_html = '';
      if (!empty($item['badge'])) {
        $b = $item['badge'];
        $badge_class = 'nav-badge';
        if ($b['type'] === 'info')   $badge_class .= ' info';
        if ($b['type'] === 'live')   $badge_class .= ' live';
        // danger = default red (no extra class)
        $badge_html = "<span class=\"{$badge_class}\">{$b['text']}</span>";
      }
    ?>
    <a href="<?= htmlspecialchars($item['file']) ?>" class="nav-item<?= $active_class ?>">
      <span class="nav-icon"><?= $item['icon'] ?></span>
      <?= htmlspecialchars_decode($item['label']) ?>
      <?= $badge_html ?>
    </a>
    <?php endforeach; ?>
    <?php endforeach; ?>
  </div>

  <!-- Status pills -->
  <div class="sidebar-footer">
    <?php foreach ($status_pills as $pill): ?>
    <div class="status-pill">
      <div class="status-dot <?= $pill['dot'] ?>"></div>
      <?= htmlspecialchars($pill['label']) ?>
      <span class="status-val"><?= htmlspecialchars($pill['val']) ?></span>
    </div>
    <?php endforeach; ?>
  </div>
</nav>
