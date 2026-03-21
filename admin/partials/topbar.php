<?php $page_label = $page_label ?? 'Dashboard'; ?>
<header class="admin-topbar">
  <div class="topbar-left">
    <button class="topbar-burger" onclick="document.getElementById('admin-sidebar').classList.toggle('open')">
      <span></span><span></span><span></span>
    </button>
    <div class="topbar-breadcrumb">
      Admin <span>›</span> <strong><?= e($page_label) ?></strong>
    </div>
  </div>
  <div class="topbar-right">
    <a href="<?= SITE_URL ?>/" target="_blank" class="topbar-site-link">Voir le site →</a>
    <div class="topbar-user">
      <div class="topbar-user-dot"></div>
      <?= e($_SESSION['admin_login'] ?? 'Admin') ?>
    </div>
  </div>
</header>
