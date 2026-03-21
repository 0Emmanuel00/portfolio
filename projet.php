<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = get_db();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: ' . SITE_URL . '/projets.php');
    exit;
}

$projet = get_projet($pdo, $id);
if (!$projet) {
    header('HTTP/1.0 404 Not Found');
    $page_title  = 'Projet introuvable — ' . SITE_NOM;
    $page_active = 'projets';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="section" style="text-align:center;padding:80px 40px">
            <p style="font-size:48px;margin-bottom:16px">🔍</p>
            <h1 style="color:var(--purple-l);margin-bottom:12px">Projet introuvable</h1>
            <p style="color:var(--text-2);margin-bottom:24px">Ce projet n\'existe pas ou a été retiré.</p>
            <a href="' . SITE_URL . '/projets.php" class="btn btn-primary">Voir tous les projets</a>
          </div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Incrémenter le compteur de visites
$pdo->prepare('UPDATE projets SET nombre_visite = nombre_visite + 1 WHERE id = ?')->execute([$id]);

// Projets similaires
$stmt = $pdo->prepare('SELECT * FROM projets WHERE visible = 1 AND type = ? AND id != ? ORDER BY ordre ASC LIMIT 3');
$stmt->execute([$projet['type'], $id]);
$similaires = $stmt->fetchAll();
foreach ($similaires as &$s) { $s['techs'] = get_techs_projet($pdo, $s['id']); }
unset($s);

// Préparer les infos de téléchargement
$has_download  = !empty($projet['url_download']);
$est_externe   = $has_download && str_starts_with($projet['url_download'], 'http');
$url_fichier   = $has_download
    ? ($est_externe ? $projet['url_download'] : SITE_URL . '/' . $projet['url_download'])
    : '';
$url_compteur  = SITE_URL . '/actions/download.php?id=' . (int)$projet['id'] . '&count_only=1';

$page_title  = e($projet['titre']) . ' — ' . SITE_NOM;
$page_desc   = mb_substr($projet['description'], 0, 155);
$page_active = 'projets';

require_once __DIR__ . '/includes/header.php';
?>

<nav class="breadcrumb" aria-label="Fil d'Ariane">
  <a href="<?= SITE_URL ?>/">Accueil</a>
  <span class="bc-sep">›</span>
  <a href="<?= SITE_URL ?>/projets.php">Projets</a>
  <span class="bc-sep">›</span>
  <span class="bc-current"><?= e($projet['titre']) ?></span>
</nav>

<section class="proj-detail-hero">

  <?php if ($projet['image']): ?>
    <div class="proj-detail-img" style="background-image:url('<?= e(SITE_URL . '/' . $projet['image']) ?>')"></div>
  <?php else: ?>
    <div class="proj-detail-img proj-detail-img--placeholder" data-type="<?= e($projet['type']) ?>"></div>
  <?php endif; ?>

  <div class="proj-detail-inner">
    <div class="proj-detail-meta">
      <span class="proj-type"><?= e(ucfirst($projet['type'])) ?></span>
      <span class="proj-date"><?= date('d/m/Y', strtotime($projet['created_at'])) ?></span>
    </div>

    <h1 class="proj-detail-title"><?= e($projet['titre']) ?></h1>

    <div class="proj-tags" style="margin-bottom:24px">
      <?php foreach ($projet['techs'] as $t): ?>
        <span class="proj-tag" style="--tc:<?= e($t['couleur']) ?>"><?= e($t['nom']) ?></span>
      <?php endforeach; ?>
    </div>

    <!-- Compteurs -->
    <div class="proj-counts" style="margin-bottom:24px">
      <span class="proj-count">👁 <?= (int)$projet['nombre_visite'] + 1 ?> vue<?= ($projet['nombre_visite'] + 1) > 1 ? 's' : '' ?></span>
      <?php if ($projet['nombre_download'] > 0): ?>
        <span class="proj-count">↓ <?= (int)$projet['nombre_download'] ?> téléchargement<?= $projet['nombre_download'] > 1 ? 's' : '' ?></span>
      <?php endif; ?>
    </div>

    <div class="hero-btns">
      <?php if ($has_download): ?>
        <a href="<?= e($url_fichier) ?>"
           <?= $est_externe ? 'target="_blank" rel="noopener"' : 'download' ?>
           class="btn btn-primary"
           onclick="fetch('<?= $url_compteur ?>')">
          Télécharger ↓
        </a>
      <?php endif; ?>
      <a href="<?= SITE_URL ?>/projets.php" class="btn btn-ghost">← Retour aux projets</a>
    </div>
  </div>
</section>

<div class="proj-detail-layout">

  <section class="proj-detail-content">
    <h2 class="detail-section-title">À propos du projet</h2>
    <div class="proj-description">
      <?= nl2br(e($projet['description'])) ?>
    </div>
  </section>

  <aside class="proj-detail-sidebar">

    <div class="sidebar-card">
      <h3 class="sidebar-title">Informations</h3>
      <ul class="sidebar-list">
        <li>
          <span class="sidebar-lbl">Type</span>
          <span class="sidebar-val"><?= e(ucfirst($projet['type'])) ?></span>
        </li>
        <li>
          <span class="sidebar-lbl">Date</span>
          <span class="sidebar-val"><?= date('F Y', strtotime($projet['created_at'])) ?></span>
        </li>
        <li>
          <span class="sidebar-lbl">Vues</span>
          <span class="sidebar-val"><?= (int)$projet['nombre_visite'] + 1 ?></span>
        </li>
        <?php if ($projet['nombre_download'] > 0): ?>
          <li>
            <span class="sidebar-lbl">Téléchargements</span>
            <span class="sidebar-val"><?= (int)$projet['nombre_download'] ?></span>
          </li>
        <?php endif; ?>
        <?php if ($has_download): ?>
          <li>
            <span class="sidebar-lbl">Fichier</span>
            <a href="<?= e($url_fichier) ?>"
               <?= $est_externe ? 'target="_blank" rel="noopener"' : 'download' ?>
               class="sidebar-link"
               onclick="fetch('<?= $url_compteur ?>')">
              Télécharger ↓
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>

    <div class="sidebar-card">
      <h3 class="sidebar-title">Technologies</h3>
      <div class="proj-tags" style="gap:6px">
        <?php foreach ($projet['techs'] as $t): ?>
          <span class="proj-tag proj-tag--lg" style="--tc:<?= e($t['couleur']) ?>">
            <?= e($t['nom']) ?>
          </span>
        <?php endforeach; ?>
        <?php if (empty($projet['techs'])): ?>
          <span style="font-size:13px;color:var(--text-2)">Non renseigné</span>
        <?php endif; ?>
      </div>
    </div>

    <a href="<?= SITE_URL ?>/contact.php" class="sidebar-contact-btn">
      Intéressé par ce projet ? Contactez-moi →
    </a>

  </aside>
</div>

<?php if (!empty($similaires)): ?>
  <section class="section" style="border-top:0.5px solid var(--border)">
    <div class="section-header">
      <h2 class="section-title">Projets similaires</h2>
      <a href="<?= SITE_URL ?>/projets.php?type=<?= e($projet['type']) ?>" class="section-link">Voir tout →</a>
    </div>
    <div class="projects-grid">
      <?php foreach ($similaires as $s): ?>
        <article class="proj-card">
          <?php if ($s['image']): ?>
            <div class="proj-img" style="background-image:url('<?= e(SITE_URL . '/' . $s['image']) ?>')"></div>
          <?php else: ?>
            <div class="proj-img proj-img--placeholder" data-type="<?= e($s['type']) ?>"></div>
          <?php endif; ?>
          <div class="proj-body">
            <span class="proj-type"><?= e(ucfirst($s['type'])) ?></span>
            <h3 class="proj-name"><?= e($s['titre']) ?></h3>
            <p class="proj-desc"><?= e(mb_substr($s['description'], 0, 90)) ?>…</p>
            <div class="proj-tags" style="margin-bottom:12px">
              <?php foreach ($s['techs'] as $t): ?>
                <span class="proj-tag" style="--tc:<?= e($t['couleur']) ?>"><?= e($t['nom']) ?></span>
              <?php endforeach; ?>
            </div>
            <a href="<?= SITE_URL ?>/projet.php?id=<?= (int)$s['id'] ?>" class="btn btn-sm btn-secondary">
              Voir le projet
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>