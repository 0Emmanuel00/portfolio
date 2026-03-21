<?php
$current = basename($_SERVER['PHP_SELF']);
if (!isset($pdo)) { $pdo = get_db(); }
$nonlus = (int)$pdo->query('SELECT COUNT(*) FROM messages WHERE lu = 0')->fetchColumn();
$initiale = mb_strtoupper(mb_substr(SITE_AUTEUR, 0, 1));
?>
<aside class="admin-sidebar" id="admin-sidebar">
  <div class="sidebar-logo">
    <div class="sidebar-logo-mark"><?= $initiale ?></div>
    <div>
      <div class="sidebar-logo-text"><?= e(SITE_AUTEUR) ?></div>
      <div class="sidebar-logo-sub">Espace admin</div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="sidebar-section-lbl">Navigation</div>

    <a href="<?= SITE_URL ?>/admin/" class="sidebar-link <?= $current === 'index.php' ? 'active' : '' ?>">
      <span class="sidebar-icon">📊</span> Dashboard
    </a>
    <a href="<?= SITE_URL ?>/admin/projets.php" class="sidebar-link <?= $current === 'projets.php' ? 'active' : '' ?>">
      <span class="sidebar-icon">🗂</span> Projets
    </a>
    <a href="<?= SITE_URL ?>/admin/messages.php" class="sidebar-link <?= $current === 'messages.php' ? 'active' : '' ?>">
      <span class="sidebar-icon">📩</span> Messages
      <?php if ($nonlus > 0): ?>
        <span class="sidebar-badge"><?= $nonlus ?></span>
      <?php endif; ?>
    </a>

    <div class="sidebar-sep"></div>
    <div class="sidebar-section-lbl">Général</div>

    <a href="<?= SITE_URL ?>/" target="_blank" class="sidebar-link">
      <span class="sidebar-icon">🌐</span> Voir le site
    </a>
    <a href="<?= SITE_URL ?>/admin/logout.php" class="sidebar-link sidebar-link--logout">
      <span class="sidebar-icon">🚪</span> Déconnexion
    </a>
  </nav>

  <div class="sidebar-footer">
    Connecté en tant que <strong style="color:var(--purple-l)"><?= e($_SESSION['admin_login'] ?? 'Admin') ?></strong>
  </div>
</aside>
