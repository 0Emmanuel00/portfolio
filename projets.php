<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = get_db();

$type_actif = in_array($_GET['type'] ?? '', ['site','app','jeu']) ? $_GET['type'] : '';
$projets    = get_projets($pdo, $type_actif);

$counts = ['tous' => 0, 'site' => 0, 'app' => 0, 'jeu' => 0];
$tous   = get_projets($pdo);
$counts['tous'] = count($tous);
foreach ($tous as $p) {
    if (isset($counts[$p['type']])) $counts[$p['type']]++;
}

$page_title  = 'Projets — ' . SITE_NOM;
$page_desc   = 'Tous les projets de ' . SITE_AUTEUR . ' : sites web, applications et jeux.';
$page_active = 'projets';

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="page-hero-inner">
    <span class="hero-pill"><span class="pill-dot"></span> Mes réalisations</span>
    <h1 class="page-title">Tous mes <span class="hero-accent">projets</span></h1>
    <p class="page-sub">Applications, jeux, sites web — tout ce que j'ai créé.</p>
  </div>
</section>

<div class="filters-bar">
  <a href="<?= SITE_URL ?>/projets.php" class="filter-btn <?= $type_actif === '' ? 'active' : '' ?>">
    Tout <span class="filter-count"><?= $counts['tous'] ?></span>
  </a>
  <a href="<?= SITE_URL ?>/projets.php?type=site" class="filter-btn <?= $type_actif === 'site' ? 'active' : '' ?>">
    Sites web <span class="filter-count"><?= $counts['site'] ?></span>
  </a>
  <a href="<?= SITE_URL ?>/projets.php?type=app" class="filter-btn <?= $type_actif === 'app' ? 'active' : '' ?>">
    Applications <span class="filter-count"><?= $counts['app'] ?></span>
  </a>
  <a href="<?= SITE_URL ?>/projets.php?type=jeu" class="filter-btn <?= $type_actif === 'jeu' ? 'active' : '' ?>">
    Jeux <span class="filter-count"><?= $counts['jeu'] ?></span>
  </a>
</div>

<section class="section">
  <?php if (empty($projets)): ?>
    <p class="empty-msg">Aucun projet dans cette catégorie pour l'instant.</p>
  <?php else: ?>
    <div class="projects-grid projects-grid--full">
      <?php foreach ($projets as $p): ?>
        <article class="proj-card proj-card--full">

          <?php if ($p['image']): ?>
            <div class="proj-img" style="background-image:url('<?= e(SITE_URL . '/' . $p['image']) ?>')"></div>
          <?php else: ?>
            <div class="proj-img proj-img--placeholder" data-type="<?= e($p['type']) ?>"></div>
          <?php endif; ?>

          <div class="proj-body">
            <div class="proj-meta">
              <span class="proj-type"><?= e(ucfirst($p['type'])) ?></span>
              <span class="proj-date"><?= date('M Y', strtotime($p['created_at'])) ?></span>
            </div>

            <h2 class="proj-name"><?= e($p['titre']) ?></h2>
            <p class="proj-desc"><?= e(mb_substr($p['description'], 0, 120)) ?>…</p>

            <div class="proj-tags">
              <?php foreach ($p['techs'] as $t): ?>
                <span class="proj-tag" style="--tc:<?= e($t['couleur']) ?>"><?= e($t['nom']) ?></span>
              <?php endforeach; ?>
            </div>

            <?php if ($p['nombre_visite'] > 0 || $p['nombre_download'] > 0): ?>
              <div class="proj-counts">
                <?php if ($p['nombre_visite'] > 0): ?>
                  <span class="proj-count">👁 <?= (int)$p['nombre_visite'] ?> vue<?= $p['nombre_visite'] > 1 ? 's' : '' ?></span>
                <?php endif; ?>
                <?php if ($p['nombre_download'] > 0): ?>
                  <span class="proj-count">↓ <?= (int)$p['nombre_download'] ?> téléchargement<?= $p['nombre_download'] > 1 ? 's' : '' ?></span>
                <?php endif; ?>
              </div>
            <?php endif; ?>

            <div class="proj-actions">
              <a href="<?= SITE_URL ?>/projet?id=<?= (int)$p['id'] ?>" class="btn btn-primary btn-sm">
                Voir le projet
              </a>
              <?php if ($p['url_download']): ?>
                <a href="<?= SITE_URL ?>/actions/download.php?id=<?= (int)$p['id'] ?>" class="btn btn-ghost btn-sm">
                  Télécharger ↓
                </a>
              <?php endif; ?>
            </div>
          </div>

        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<div class="contact-banner" style="margin-bottom:56px">
  <h2 class="cb-title">Un projet en tête ?</h2>
  <p class="cb-sub">Contactez-moi pour discuter d’une éventuelle collaboration.</p>
  <a href="<?= SITE_URL ?>/contact" class="btn btn-primary">Me contacter</a>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
