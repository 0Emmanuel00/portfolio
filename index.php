<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = get_db();

$nb_projets   = count_projets($pdo);
$nb_techs     = count_techs($pdo);
$projets_home = array_slice(get_projets($pdo), 0, 3);

$page_title  = SITE_NOM . ' — ' . SITE_METIER;
$page_desc   = 'Portfolio de ' . SITE_AUTEUR . '. Découvrez mes projets web, applications et jeux.';
$page_active = 'accueil';

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="hero-inner">

    <span class="hero-pill">
      <span class="pill-dot"></span>
      <?= e(SITE_METIER) ?> · Disponible
    </span>

    <h1 class="hero-title">
      Je code des<br>
      <span class="hero-accent">apps, jeux &amp; <br> sites web.</span><br>
      
    </h1>

    <p class="hero-sub">
      Portfolio d'<?= e(SITE_AUTEUR) ?> — étudiant en BTS informatique passionné
      par le développement web de jeux et d'applications.
    </p>

    <div class="hero-btns">
      <a href="<?= SITE_URL ?>/projets.php" class="btn btn-primary">Voir mes projets</a>
      <a href="<?= SITE_URL ?>/contact.php" class="btn btn-secondary">Me contacter</a>
    </div>

    <div class="hero-stats">
      <div class="stat">
        <span class="stat-val"><?= $nb_projets ?></span>
        <span class="stat-lbl">Projets réalisés</span>
      </div>
      <div class="stat">
        <span class="stat-val"><?= $nb_techs ?>+</span>
        <span class="stat-lbl">Technologies</span>
      </div>
      <div class="stat">
        <span class="stat-val"><?= date('Y') - 2024 ?></span>
        <span class="stat-lbl">Années d'étude</span>
      </div>
    </div>
  </div>
</section>

<div class="skills-strip">
  <span class="skills-lbl">Stack: </span>
  <?php
  $techs_all = $pdo->query('SELECT * FROM technologies ORDER BY id')->fetchAll();
  foreach ($techs_all as $t):
  ?>
    <span class="skill-tag" style="--tc:<?= e($t['couleur']) ?>"><?= e($t['nom']) ?></span>
  <?php endforeach; ?>
</div>

<section class="section">
  <div class="section-header">
    <h2 class="section-title">Mes projets</h2>
    <a href="<?= SITE_URL ?>/projets.php" class="section-link">Voir tout →</a>
  </div>

  <?php if (empty($projets_home)): ?>
    <p class="empty-msg">Aucun projet pour l'instant — revenez bientôt !</p>
  <?php else: ?>
    <div class="projects-grid">
      <?php foreach ($projets_home as $p):
        $est_externe  = !empty($p['url_download']) && str_starts_with($p['url_download'], 'http');
        $url_fichier  = !empty($p['url_download'])
            ? ($est_externe ? $p['url_download'] : SITE_URL . '/' . $p['url_download'])
            : '';
        $url_compteur = SITE_URL . '/actions/download.php?id=' . (int)$p['id'] . '&count_only=1';
      ?>
        <article class="proj-card">
          <?php if ($p['image']): ?>
            <div class="proj-img" style="background-image:url('<?= e(SITE_URL . '/' . $p['image']) ?>')"></div>
          <?php else: ?>
            <div class="proj-img proj-img--placeholder" data-type="<?= e($p['type']) ?>"></div>
          <?php endif; ?>
          <div class="proj-body">
            <span class="proj-type"><?= e(ucfirst($p['type'])) ?></span>
            <h3 class="proj-name"><?= e($p['titre']) ?></h3>
            <p class="proj-desc"><?= e(mb_substr($p['description'], 0, 90)) ?>…</p>
            <div class="proj-tags">
              <?php foreach ($p['techs'] as $t): ?>
                <span class="proj-tag" style="--tc:<?= e($t['couleur']) ?>"><?= e($t['nom']) ?></span>
              <?php endforeach; ?>
            </div>
            <div class="proj-actions">
              <a href="<?= SITE_URL ?>/projet.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-primary">
                Voir le projet
              </a>
              <?php if (!empty($p['url_download'])): ?>
                <a href="<?= e($url_fichier) ?>"
                   <?= $est_externe ? 'target="_blank" rel="noopener"' : 'download' ?>
                   class="btn btn-sm btn-ghost"
                   onclick="fetch('<?= $url_compteur ?>')">
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

<section class="about-strip">
  <div class="about-avatar"><?= e(mb_substr(SITE_AUTEUR, 0, 1)) ?></div>
  <div class="about-text">
    <p class="about-name"><?= e(SITE_AUTEUR) ?></p>
    <p class="about-desc">
      Étudiant en informatique, je développe depuis maintenant <?= date('Y') - 2018 ?> ans.
      J'aime bien développer, c'est une passion donc depuis peux je les partages avec le monde entier !
    </p>
  </div>
  <a href="<?= SITE_URL ?>/apropos.php" class="btn btn-secondary">En savoir plus</a>
</section>

<section class="contact-banner">
  <h2 class="cb-title">Si vous souhaitez me contacter.</h2>
  <p class="cb-sub">Qui que vous soyez — envoyez-moi un message, j'essayerai d'y repondre rapidement possible.</p>
  <a href="<?= SITE_URL ?>/contact.php" class="btn btn-primary">M'envoyer un message</a>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>