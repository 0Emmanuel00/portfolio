<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/auth.php';

$pdo = get_db();
$nb_projets  = (int)$pdo->query('SELECT COUNT(*) FROM projets')->fetchColumn();
$nb_visibles = (int)$pdo->query('SELECT COUNT(*) FROM projets WHERE visible=1')->fetchColumn();
$nb_messages = (int)$pdo->query('SELECT COUNT(*) FROM messages')->fetchColumn();
$nb_nonlus   = (int)$pdo->query('SELECT COUNT(*) FROM messages WHERE lu=0')->fetchColumn();
$nb_techs    = (int)$pdo->query('SELECT COUNT(*) FROM technologies')->fetchColumn();

$derniers_msgs = $pdo->query('SELECT * FROM messages ORDER BY created_at DESC LIMIT 6')->fetchAll();
$derniers_proj = $pdo->query('SELECT * FROM projets ORDER BY created_at DESC LIMIT 5')->fetchAll();
foreach ($derniers_proj as &$p) { $p['techs'] = get_techs_projet($pdo, $p['id']); } unset($p);

$page_label = 'Dashboard';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — Admin</title>
  <link rel="stylesheet" href="<?= SITE_URL ?>/admin/admin.css">
</head>
<body>
<div class="admin-wrap">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <div class="admin-main">
    <?php include __DIR__ . '/partials/topbar.php'; ?>
    <div class="admin-content">

      <div class="admin-page-header">
        <div>
          <h1 class="admin-page-title">Bonjour, <?= e($_SESSION['admin_login'] ?? 'Admin') ?> 👋</h1>
          <p class="admin-page-sub">Voici un aperçu de votre portfolio</p>
        </div>
        <a href="<?= SITE_URL ?>/admin/projets.php?action=new" class="btn btn-primary">+ Nouveau projet</a>
      </div>

      <!-- Stats -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-card-icon-wrap">🗂</div>
          <div>
            <div class="stat-card-val"><?= $nb_projets ?></div>
            <div class="stat-card-lbl"><?= $nb_visibles ?> visible<?= $nb_visibles > 1 ? 's' : '' ?> · <?= $nb_projets - $nb_visibles ?> masqué<?= ($nb_projets - $nb_visibles) > 1 ? 's' : '' ?></div>
            <div class="stat-card-lbl" style="color:var(--purple-l);margin-top:2px">Projets</div>
          </div>
        </div>
        <div class="stat-card <?= $nb_nonlus > 0 ? 'stat-card--alert' : 'stat-card--ok' ?>">
          <div class="stat-card-icon-wrap">📩</div>
          <div>
            <div class="stat-card-val">
              <?= $nb_messages ?>
              <?php if ($nb_nonlus > 0): ?>
                <span class="badge-nonlu"><?= $nb_nonlus ?> nouveau<?= $nb_nonlus > 1 ? 'x' : '' ?></span>
              <?php endif; ?>
            </div>
            <div class="stat-card-lbl" style="color:var(--purple-l);margin-top:2px">Messages</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon-wrap">🏷</div>
          <div>
            <div class="stat-card-val"><?= $nb_techs ?></div>
            <div class="stat-card-lbl" style="color:var(--purple-l);margin-top:2px">Technologies</div>
          </div>
        </div>
      </div>

      <!-- Derniers messages -->
      <div class="admin-section">
        <div class="admin-section-header">
          <h2 class="admin-section-title">📩 Derniers messages</h2>
          <a href="<?= SITE_URL ?>/admin/messages.php" class="admin-section-link">Voir tout →</a>
        </div>
        <?php if (empty($derniers_msgs)): ?>
          <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <p>Aucun message reçu pour l'instant.</p>
          </div>
        <?php else: ?>
          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead><tr><th>Nom</th><th>Objet</th><th>Date</th><th>Statut</th><th>Action</th></tr></thead>
              <tbody>
                <?php foreach ($derniers_msgs as $m): ?>
                  <tr class="<?= !$m['lu'] ? 'row-unread' : '' ?>">
                    <td><strong><?= e($m['nom']) ?></strong></td>
                    <td><?= e(mb_substr($m['objet'], 0, 45)) ?></td>
                    <td style="white-space:nowrap"><?= date('d/m/Y H:i', strtotime($m['created_at'])) ?></td>
                    <td><?= !$m['lu'] ? '<span class="badge badge-new">Nouveau</span>' : '<span class="badge badge-read">Lu</span>' ?></td>
                    <td><a href="<?= SITE_URL ?>/admin/messages.php?id=<?= (int)$m['id'] ?>" class="btn btn-sm btn-ghost">Lire</a></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <!-- Derniers projets -->
      <div class="admin-section">
        <div class="admin-section-header">
          <h2 class="admin-section-title">🗂 Derniers projets</h2>
          <a href="<?= SITE_URL ?>/admin/projets.php" class="admin-section-link">Gérer →</a>
        </div>
        <?php if (empty($derniers_proj)): ?>
          <div class="empty-state">
            <div class="empty-state-icon">📂</div>
            <p>Aucun projet. <a href="<?= SITE_URL ?>/admin/projets.php?action=new" style="color:var(--purple-l)">Créer le premier</a></p>
          </div>
        <?php else: ?>
          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead><tr><th>Titre</th><th>Type</th><th>Technologies</th><th>Visible</th><th>Actions</th></tr></thead>
              <tbody>
                <?php foreach ($derniers_proj as $p): ?>
                  <tr>
                    <td><strong><?= e($p['titre']) ?></strong></td>
                    <td><span class="badge badge-type"><?= e(ucfirst($p['type'])) ?></span></td>
                    <td>
                      <div class="tags-wrap">
                        <?php foreach ($p['techs'] as $t): ?>
                          <span class="proj-tag" style="--tc:<?= e($t['couleur']) ?>"><?= e($t['nom']) ?></span>
                        <?php endforeach; ?>
                      </div>
                    </td>
                    <td><?= $p['visible'] ? '<span class="badge badge-ok">Oui</span>' : '<span class="badge badge-off">Non</span>' ?></td>
                    <td class="td-actions">
                      <a href="<?= SITE_URL ?>/admin/projets.php?action=edit&id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-ghost">Modifier</a>
                      <a href="<?= SITE_URL ?>/projet?id=<?= (int)$p['id'] ?>" target="_blank" class="btn btn-sm btn-ghost">Voir →</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>
</body>
</html>
