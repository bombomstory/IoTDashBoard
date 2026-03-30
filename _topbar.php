<?php
/**
 * _topbar.php  —  Shared Topbar
 * Requires $meta from _vars.php
 * Optional: $topbar_extra_html  — extra chips/buttons to inject before user-btn
 *
 * Usage:  <?php require_once '_topbar.php'; ?>
 */
?>
<header class="topbar">
  <button class="hamburger" onclick="toggleSidebar()">☰</button>

  <div class="topbar-titles">
    <div class="page-title"><?= htmlspecialchars_decode($meta['title']) ?></div>
    <div class="page-sub"><?= htmlspecialchars($meta['sub']) ?> · <?= SITE_LOCATION ?></div>
  </div>

  <div class="topbar-right">

    <!-- Sync countdown ring -->
    <div class="sync-chip">
      <div class="sync-ring">
        <svg viewBox="0 0 18 18" width="18" height="18">
          <circle class="sync-ring-bg"   cx="9" cy="9" r="7"/>
          <circle class="sync-ring-fill" cx="9" cy="9" r="7" id="syncRingFill"/>
        </svg>
      </div>
      <span class="sync-label">Next sync</span>
      <span class="sync-countdown" id="nextSync">59:59</span>
    </div>

    <div class="topbar-divider"></div>

    <!-- Clock -->
    <div class="time-chip">
      <span class="clock-icon">🕐</span>
      <span class="clock" id="clockDisplay">--:--:--</span>
    </div>

    <?php if (!empty($topbar_extra_html)) echo $topbar_extra_html; ?>

    <div class="topbar-divider"></div>

    <!-- Notification bell -->
    <div class="icon-btn">🔔<span class="badge"></span></div>

    <!-- User button -->
    <div class="user-btn">
      <div class="user-avatar">A</div>
      <span class="user-name">Admin</span>
    </div>

  </div>
</header>
