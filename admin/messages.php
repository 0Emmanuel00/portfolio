<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/auth.php';

$pdo    = get_db();
$id     = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$action = $_GET['action'] ?? '';

if ($action === 'delete' && $id) {
  $pdo->prepare('DELETE FROM messages WHERE id = ?')->execute([$id]);
  header('Location: ' . SITE_URL . '/admin/messages.php?deleted=1'); exit;
}
if ($action === 'toggle_lu' && $id) {
  $pdo->prepare('UPDATE messages SET lu = 1 - lu WHERE id = ?')->execute([$id]);
  header('Location: ' . SITE_URL . '/admin/messages.php?id=' . $id); exit;
}
if ($action === 'delete_all') {
  $pdo->query('DELETE FROM messages WHERE lu = 1');
  header('Location: ' . SITE_URL . '/admin/messages.php?purged=1'); exit;
}

$message = null;
if ($id) {
  $stmt = $pdo->prepare('SELECT * FROM messages WHERE id = ?');
  $stmt->execute([$id]);
  $message = $stmt->fetch();
  if ($message && !$message['lu']) {
    $pdo->prepare('UPDATE messages SET lu = 1 WHERE id = ?')->execute([$id]);
    $message['lu'] = 1;
  }
}

$messages = $pdo->query('SELECT * FROM messages ORDER BY created_at DESC')->fetchAll();
$nb_nonlus = count(array_filter($messages, fn($m) => !$m['lu']));

$page_label = 'Messages';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Messages — Admin</title>
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
          <h1 class="admin-page-title">Messages reçus</h1>
          <p class="admin-page-sub">
            <?= count($messages) ?> message<?= count($messages) > 1 ? 's' : '' ?>
            <?php if ($nb_nonlus > 0): ?>
              · <span style="color:#fbbf24"><?= $nb_nonlus ?> non lu<?= $nb_nonlus > 1 ? 's' : '' ?></span>
            <?php endif; ?>
          </p>
        </div>
        <?php if (count($messages) > 0): ?>
          <a href="<?= SITE_URL ?>/admin/messages.php?action=delete_all"
             class="btn btn-ghost btn-sm"
             onclick="return confirm('Supprimer tous les messages lus ?')">
            Vider les lus
          </a>
        <?php endif; ?>
      </div>

      <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-error">Message supprimé.</div>
      <?php elseif (isset($_GET['purged'])): ?>
        <div class="alert alert-success">✓ Messages lus supprimés.</div>
      <?php endif; ?>

      <?php if (empty($messages)): ?>
        <div class="empty-state">
          <div class="empty-state-icon">📭</div>
          <p>Aucun message reçu pour l'instant.</p>
        </div>
      <?php else: ?>
        <div class="messages-layout">

          <!-- Liste -->
          <div class="messages-list">
            <?php foreach ($messages as $m): ?>
              <a href="<?= SITE_URL ?>/admin/messages.php?id=<?= (int)$m['id'] ?>"
                 class="msg-item <?= !$m['lu'] ? 'msg-item--unread' : '' ?> <?= $id === (int)$m['id'] ? 'msg-item--active' : '' ?>">
                <div class="msg-item-top">
                  <span class="msg-item-nom"><?= e($m['nom']) ?></span>
                  <span class="msg-item-date"><?= date('d/m', strtotime($m['created_at'])) ?></span>
                </div>
                <div class="msg-item-objet"><?= e(mb_substr($m['objet'], 0, 38)) ?></div>
                <div class="msg-item-preview"><?= e(mb_substr($m['message'], 0, 55)) ?>…</div>
              </a>
            <?php endforeach; ?>
          </div>

          <!-- Détail -->
          <div class="messages-detail">
            <?php if ($message): ?>
              <div class="msg-detail-header">
                <div class="msg-detail-top">
                  <div>
                    <div class="msg-detail-objet"><?= e($message['objet']) ?></div>
                    <div class="msg-detail-meta">
                      De <strong><?= e($message['nom']) ?></strong>
                      — <a href="mailto:<?= e($message['email']) ?>"><?= e($message['email']) ?></a><br>
                      Le <?= date('d/m/Y à H:i', strtotime($message['created_at'])) ?>
                    </div>
                  </div>
                  <div class="msg-detail-actions">
                    <a href="mailto:<?= e($message['email']) ?>?subject=Re: <?= e($message['objet']) ?>" class="btn btn-primary btn-sm">Répondre</a>
                    <a href="<?= SITE_URL ?>/admin/messages.php?action=toggle_lu&id=<?= (int)$message['id'] ?>" class="btn btn-ghost btn-sm">
                      <?= $message['lu'] ? 'Marquer non lu' : 'Marquer lu' ?>
                    </a>
                    <a href="<?= SITE_URL ?>/admin/messages.php?action=delete&id=<?= (int)$message['id'] ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Supprimer ce message ?')">Supprimer</a>
                  </div>
                </div>
              </div>
              <div class="msg-detail-body"><?= nl2br(e($message['message'])) ?></div>
            <?php else: ?>
              <div class="msg-placeholder">
                <div class="msg-placeholder-icon">📩</div>
                <p>Sélectionnez un message pour le lire.</p>
              </div>
            <?php endif; ?>
          </div>

        </div>
      <?php endif; ?>

    </div>
  </div>
</div>
</body>
</html>
