<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = get_db();

// Données pour les stats
$nb_projets = count_projets($pdo);
$nb_techs   = count_techs($pdo);

// 3 derniers projets en vedette
$projets_home = get_projets($pdo);
$projets_home = array_slice($projets_home, 0, 3);

// Meta
$page_title  = SITE_NOM . ' — ' . SITE_METIER;
$page_desc   = 'Portfolio de ' . SITE_AUTEUR . '. Découvrez mes projets web, applications et jeux.';
$page_active = 'accueil';

require_once __DIR__ . '/includes/header.php';
?>

<!-- ======= HERO ======= -->
<section class="hero">
  <div class="hero-inner">

    <span class="hero-pill">
      <span class="pill-dot"></span>
      <?= e(SITE_METIER) ?> · Disponible
    </span>

    <h1 class="hero-title">
      Je code des<br>
      <span class="hero-accent">apps, jeux &amp;</span><br>
      sites web.
    </h1>

    <p class="hero-sub">
      Portfolio de <?= e(SITE_AUTEUR) ?> — étudiant en informatique passionné
      par le développement web, les jeux et les applications.
    </p>

    <div class="hero-btns">
      <a href="<?= SITE_URL ?>/projets" class="btn btn-primary">Voir mes projets</a>
      <a href="<?= SITE_URL ?>/contact" class="btn btn-secondary">Me contacter</a>
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
        <span class="stat-val"><?= date('Y') - 2022 ?></span>
        <span class="stat-lbl">Années d'étude</span>
      </div>
    </div>
  </div>
</section>

<!-- ======= SKILLS STRIP ======= -->
<div class="skills-strip">
  <span class="skills-lbl">Stack :</span>
  <?php
  $pdo_techs = get_db();
  $techs_all = $pdo_techs->query('SELECT * FROM technologies ORDER BY id')->fetchAll();
  foreach ($techs_all as $t):
  ?>
    <span class="skill-tag" style="--tc:<?= e($t['couleur']) ?>"><?= e($t['nom']) ?></span>
  <?php endforeach; ?>
</div>

<!-- ======= PROJETS EN VEDETTE ======= -->
<section class="section">
  <div class="section-header">
    <h2 class="section-title">Mes projets</h2>
    <a href="<?= SITE_URL ?>/projets" class="section-link">Voir tout →</a>
  </div>

  <?php if (empty($projets_home)): ?>
    <p class="empty-msg">Aucun projet pour l'instant — revenez bientôt !</p>
  <?php else: ?>
    <div class="projects-grid">
      <?php foreach ($projets_home as $p): ?>
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
              <a href="<?= SITE_URL ?>/projet?id=<?= (int)$p['id'] ?>" class="btn btn-sm">Voir le projet</a>
              <?php if ($p['url_demo']): ?>
                <a href="<?= e($p['url_demo']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-ghost">Visiter</a>
              <?php endif; ?>
              <?php if ($p['url_download']): ?>
                <a href="<?= e($p['url_download']) ?>" class="btn btn-sm btn-ghost">Télécharger</a>
              <?php endif; ?>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<!-- ======= À PROPOS (mini) ======= -->
<section class="about-strip">
  <div class="about-avatar"><?= e(mb_substr(SITE_AUTEUR, 0, 1)) ?></div>
  <div class="about-text">
    <p class="about-name"><?= e(SITE_AUTEUR) ?></p>
    <p class="about-desc">
      Étudiant en informatique, je développe des projets web depuis <?= date('Y') - 2022 ?> ans.
      Je cherche une alternance ou un stage dans le développement web / fullstack.
      Basé à <?= e(SITE_LOCALITE) ?>.
    </p>
  </div>
  <a href="<?= SITE_URL ?>/apropos" class="btn btn-secondary">En savoir plus</a>
</section>

<!-- ======= BANNIÈRE CONTACT ======= -->
<section class="contact-banner">
  <h2 class="cb-title">Vous voulez travailler avec moi ?</h2>
  <p class="cb-sub">Recruteur, patron ou curieux — envoyez-moi un message, je réponds rapidement.</p>
  <a href="<?= SITE_URL ?>/contact" class="btn btn-primary">M'envoyer un message</a>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>