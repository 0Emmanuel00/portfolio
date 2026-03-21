<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/auth.php';

$pdo    = get_db();
$action = $_GET['action'] ?? 'list';
$id     = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($action === 'delete' && $id) {
  $pdo->prepare('DELETE FROM projets WHERE id = ?')->execute([$id]);
  header('Location: ' . SITE_URL . '/admin/projets.php?deleted=1'); exit;
}
if ($action === 'toggle' && $id) {
  $pdo->prepare('UPDATE projets SET visible = 1 - visible WHERE id = ?')->execute([$id]);
  header('Location: ' . SITE_URL . '/admin/projets.php'); exit;
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['new','edit'])) {
  $titre       = clean($_POST['titre']       ?? '');
  $description = clean($_POST['description'] ?? '');
  $type        = in_array($_POST['type'] ?? '', ['site','app','jeu']) ? $_POST['type'] : 'site';
  $url_dl      = clean($_POST['url_download'] ?? '');
  $image       = clean($_POST['image']        ?? '');
  $visible     = isset($_POST['visible']) ? 1 : 0;
  $ordre       = (int)($_POST['ordre'] ?? 0);
  $techs_sel   = array_map('intval', $_POST['techs'] ?? []);

  if (!$titre || !$description) {
    $msg = 'error:Le titre et la description sont obligatoires.';
  } else {
    if ($action === 'new') {
      $stmt = $pdo->prepare('INSERT INTO projets (titre,description,type,url_download,image,visible,ordre,created_at) VALUES (?,?,?,?,?,?,?,NOW())');
      $stmt->execute([$titre,$description,$type,$url_dl,$image,$visible,$ordre]);
      $new_id = (int)$pdo->lastInsertId();
      foreach ($techs_sel as $tid) {
        $pdo->prepare('INSERT IGNORE INTO projet_tech(projet_id,tech_id) VALUES(?,?)')->execute([$new_id,$tid]);
      }
    } else {
      $pdo->prepare('UPDATE projets SET titre=?,description=?,type=?,url_download=?,image=?,visible=?,ordre=? WHERE id=?')
          ->execute([$titre,$description,$type,$url_dl,$image,$visible,$ordre,$id]);
      $pdo->prepare('DELETE FROM projet_tech WHERE projet_id = ?')->execute([$id]);
      foreach ($techs_sel as $tid) {
        $pdo->prepare('INSERT INTO projet_tech(projet_id,tech_id) VALUES(?,?)')->execute([$id,$tid]);
      }
    }
    header('Location: ' . SITE_URL . '/admin/projets.php?saved=1'); exit;
  }
}

$toutes_techs = $pdo->query('SELECT * FROM technologies ORDER BY nom')->fetchAll();
if (in_array($action, ['edit','new'])) {
  $projet     = $id ? get_projet($pdo, $id) : null;
  $proj_techs = $projet ? array_column($projet['techs'], 'id') : [];
}
$projets = $pdo->query('SELECT * FROM projets ORDER BY ordre ASC, created_at DESC')->fetchAll();
foreach ($projets as &$p) { $p['techs'] = get_techs_projet($pdo, $p['id']); } unset($p);

$page_label = in_array($action, ['new','edit']) ? ($action === 'new' ? 'Nouveau projet' : 'Modifier le projet') : 'Projets';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($page_label) ?> — Admin</title>
  <link rel="stylesheet" href="<?= SITE_URL ?>/admin/admin.css">
</head>
<body>
<div class="admin-wrap">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <div class="admin-main">
    <?php include __DIR__ . '/partials/topbar.php'; ?>
    <div class="admin-content">

    <?php if (in_array($action, ['new','edit'])): ?>
      <!-- FORMULAIRE -->
      <div class="admin-page-header">
        <div>
          <h1 class="admin-page-title"><?= $action === 'new' ? '+ Nouveau projet' : 'Modifier le projet' ?></h1>
          <p class="admin-page-sub"><?= $action === 'new' ? 'Remplissez les informations ci-dessous' : 'Modifiez les informations du projet' ?></p>
        </div>
        <a href="<?= SITE_URL ?>/admin/projets.php" class="btn btn-ghost">← Retour</a>
      </div>

      <?php if (str_starts_with($msg, 'error:')): ?>
        <div class="alert alert-error"><?= e(substr($msg, 6)) ?></div>
      <?php endif; ?>

      <form method="POST" class="admin-form">
        <div class="form-row">
          <div class="field">
            <label>Titre <span class="req">*</span></label>
            <input type="text" name="titre" required maxlength="150"
                   value="<?= e($projet['titre'] ?? '') ?>" placeholder="Nom du projet">
          </div>
          <div class="field">
            <label>Type <span class="req">*</span></label>
            <select name="type">
              <?php foreach (['site' => 'Site web','app' => 'Application','jeu' => 'Jeu'] as $val => $lbl): ?>
                <option value="<?= $val ?>" <?= ($projet['type'] ?? '') === $val ? 'selected' : '' ?>>
                  <?= $lbl ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="field">
          <label>Description <span class="req">*</span></label>
          <textarea name="description" required rows="7"
                    placeholder="Décrivez le projet en détail..."><?= e($projet['description'] ?? '') ?></textarea>
        </div>

        <div class="form-row">
          <div class="field">
            <label>URL Téléchargement / Visite</label>
            <input type="text" name="url_download" placeholder="assets/uploads/monfichier.zip  ou  https://…" value="<?= e($projet['url_download'] ?? '') ?>">
          </div>
          <div class="field">
            <label>Image du projet <span style="color:var(--text-3);font-size:11px">(chemin relatif)</span></label>
            <input type="text" name="image" placeholder="assets/img/mon-projet.png" value="<?= e($projet['image'] ?? '') ?>">
          </div>
        </div>

        <div class="form-row">
          <div class="field">
            <label>Ordre d'affichage <span style="color:var(--text-3);font-size:11px">(0 = premier)</span></label>
            <input type="number" name="ordre" min="0" value="<?= (int)($projet['ordre'] ?? 0) ?>">
          </div>
          <div class="field" style="justify-content:flex-end">
            <label class="check-label" style="height:42px">
              <input type="checkbox" name="visible" value="1" <?= ($projet['visible'] ?? 1) ? 'checked' : '' ?>>
              Visible sur le site
            </label>
          </div>
        </div>

        <div class="field">
          <label>Technologies utilisées</label>
          <div class="techs-checkboxes">
            <?php foreach ($toutes_techs as $t): ?>
              <label class="tech-check">
                <input type="checkbox" name="techs[]" value="<?= (int)$t['id'] ?>"
                       <?= in_array($t['id'], $proj_techs ?? []) ? 'checked' : '' ?>>
                <span class="tech-check-label" style="--tc:<?= e($t['couleur']) ?>">
                  <?= e($t['nom']) ?>
                </span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">
            <?= $action === 'new' ? '✓ Créer le projet' : '✓ Enregistrer' ?>
          </button>
          <a href="<?= SITE_URL ?>/admin/projets.php" class="btn btn-ghost">Annuler</a>
        </div>
      </form>

    <?php else: ?>
      <!-- LISTE -->
      <div class="admin-page-header">
        <div>
          <h1 class="admin-page-title">Projets</h1>
          <p class="admin-page-sub"><?= count($projets) ?> projet<?= count($projets) > 1 ? 's' : '' ?> au total</p>
        </div>
        <a href="<?= SITE_URL ?>/admin/projets.php?action=new" class="btn btn-primary">+ Nouveau projet</a>
      </div>

      <?php if (isset($_GET['saved'])): ?>
        <div class="alert alert-success">✓ Projet enregistré avec succès !</div>
      <?php elseif (isset($_GET['deleted'])): ?>
        <div class="alert alert-error">Projet supprimé.</div>
      <?php endif; ?>

      <?php if (empty($projets)): ?>
        <div class="empty-state">
          <div class="empty-state-icon">📂</div>
          <p>Aucun projet pour l'instant.</p>
          <a href="<?= SITE_URL ?>/admin/projets.php?action=new" class="btn btn-primary">+ Créer le premier</a>
        </div>
      <?php else: ?>
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Titre</th>
                <th>Type</th>
                <th>Technologies</th>
                <th>Ordre</th>
                <th>Téléchargements</th>
                <th>Visites</th>
                <th>Visible</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($projets as $p): ?>
                <tr>
                  <td><strong><?= e($p['titre']) ?></strong></td>
                  <td><span class="badge badge-type"><?= e(ucfirst($p['type'])) ?></span></td>
                  <td>
                    <div class="tags-wrap">
                      <?php foreach ($p['techs'] as $t): ?>
                        <span class="proj-tag" style="--tc:<?= e($t['couleur']) ?>"><?= e($t['nom']) ?></span>
                      <?php endforeach; ?>
                      <?php if (empty($p['techs'])): ?>
                        <span style="color:var(--text-3);font-size:11px">—</span>
                      <?php endif; ?>
                    </div>
                  </td>
                  <td><?= (int)$p['ordre'] ?></td>
                  <td>
                    <span style="color:var(--purple-l);font-weight:500"><?= (int)$p['nombre_download'] ?></span>
                    <span style="color:var(--text-3);font-size:11px"> dl</span>
                  </td>
                  <td>
                    <span style="color:var(--purple-l);font-weight:500"><?= (int)$p['nombre_visite'] ?></span>
                    <span style="color:var(--text-3);font-size:11px"> vues</span>
                  </td>
                  <td>
                    <a href="<?= SITE_URL ?>/admin/projets.php?action=toggle&id=<?= (int)$p['id'] ?>"
                       style="cursor:pointer">
                      <?= $p['visible']
                        ? '<span class="badge badge-ok">Oui</span>'
                        : '<span class="badge badge-off">Non</span>' ?>
                    </a>
                  </td>
                  <td class="td-actions">
                    <a href="<?= SITE_URL ?>/admin/projets.php?action=edit&id=<?= (int)$p['id'] ?>"
                       class="btn btn-sm btn-ghost">Modifier</a>
                    <a href="<?= SITE_URL ?>/projet?id=<?= (int)$p['id'] ?>"
                       target="_blank" class="btn btn-sm btn-ghost">Voir</a>
                    <a href="<?= SITE_URL ?>/admin/projets.php?action=delete&id=<?= (int)$p['id'] ?>"
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Supprimer définitivement ce projet ?')">Suppr.</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    </div>
  </div>
</div>
</body>
</html>