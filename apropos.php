<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = get_db();

// Compétences avec pourcentages — à personnaliser
$competences = [
    ['nom' => 'HTML / CSS',     'pct' => 80, 'couleur' => '#6ee7b7'],
    ['nom' => 'PHP',            'pct' => 80, 'couleur' => '#60a5fa'],
    ['nom' => 'Godot Engine',   'pct' => 70, 'couleur' => '#818cf8'],
    ['nom' => 'Python',         'pct' => 55, 'couleur' => '#22B14C'],
    ['nom' => 'Langage C',      'pct' => 35, 'couleur' => '#86efac'],
    ['nom' => 'Langage C++',    'pct' => 30, 'couleur' => '#86efac'],
    ['nom' => 'Git',            'pct' => 10, 'couleur' => '#f87171'],
];

// Formation — à personnaliser
$formation = [
    ['annee' => '2024 – 2026', 'titre' => 'BTS CIEL',                   'lieu' => 'En cours — DHUODA'],
    ['annee' => '2024',        'titre' => 'Baccalauréat',               'lieu' => 'Lycée LaSalle'],
];

// Loisirs
$loisirs = ['Développement', 'Jeux vidéo'];

$nb_projets = count_projets($pdo);
$nb_techs   = count_techs($pdo);

$page_title  = 'À propos — ' . SITE_NOM;
$page_desc   = 'Découvrez le parcours et les compétences de ' . SITE_AUTEUR . ', étudiant développeur web.';
$page_active = 'apropos';

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="page-hero-inner">
    <span class="hero-pill"><span class="pill-dot"></span> Qui suis-je ?</span>
    <h1 class="page-title">À <span class="hero-accent">propos</span></h1>
    <p class="page-sub">Mon parcours, mes compétences, ce que je recherche.</p>
  </div>
</section>

<!-- Intro -->
<section class="about-intro-section">
  <div class="about-intro-avatar">
    <div class="big-avatar"><?= e(mb_substr(SITE_AUTEUR, 0, 1)) ?></div>
  </div>
  <div class="about-intro-text">
    <h2 class="about-intro-name">
      <?= e(SITE_AUTEUR) ?>
      <span class="about-intro-badge">Disponible</span>
    </h2>
    <p class="about-intro-role"><?= e(SITE_METIER) ?> · <?= e(SITE_LOCALITE) ?></p>
    <p class="about-intro-bio">
      Passionné par le développement depuis le le collège, je crée des projets web, des applications
      ainsi des jeux. Je suis actuellement en étude supérieur, et je suis toujours passionné par les mêmes chose.
    </p>
    <div class="about-intro-stats">
      <div class="istat">
        <span class="istat-val"><?= $nb_projets ?></span>
        <span class="istat-lbl">Projets</span>
      </div>
      <div class="istat">
        <span class="istat-val"><?= $nb_techs ?>+</span>
        <span class="istat-lbl">Technologies</span>
      </div>
      <div class="istat">
        <span class="istat-val"><?= date('Y') - 2022 ?> ans</span>
        <span class="istat-lbl">d'étude</span>
      </div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:20px">
      <a href="<?= SITE_URL ?>/projets" class="btn btn-primary">Voir mes projets</a>
      <a href="<?= SITE_URL ?>/contact" class="btn btn-secondary">Me contacter</a>
    </div>
  </div>
</section>

<!-- Grille compétences + formation -->
<section class="section about-grid">

  <!-- Compétences -->
  <div class="about-card">
    <h2 class="about-card-title">
      <span class="about-card-icon">💻</span> Compétences
    </h2>
    <div class="skills-bars">
      <?php foreach ($competences as $c): ?>
        <div class="skill-row">
          <div class="skill-row-top">
            <span class="skill-name"><?= e($c['nom']) ?></span>
            <span class="skill-pct"><?= (int)$c['pct'] ?>%</span>
          </div>
          <div class="skill-bar">
            <div class="skill-fill"
                 style="width:<?= (int)$c['pct'] ?>%;background:<?= e($c['couleur']) ?>">
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Formation -->
  <div class="about-card">
    <h2 class="about-card-title">
      <span class="about-card-icon">🎓</span> Formation
    </h2>
    <div class="timeline">
      <?php foreach ($formation as $f): ?>
        <div class="tl-item">
          <div class="tl-left">
            <div class="tl-dot"></div>
            <div class="tl-line"></div>
          </div>
          <div class="tl-content">
            <p class="tl-year"><?= e($f['annee']) ?></p>
            <p class="tl-title"><?= e($f['titre']) ?></p>
            <p class="tl-lieu"><?= e($f['lieu']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Ce que je recherche
  <div class="about-card">
    <h2 class="about-card-title">
      <span class="about-card-icon">🎯</span> Ce que je recherche
    </h2>
    <p class="about-card-text">
      Je suis à la recherche d'un
      <span class="highlight">stage</span> ou d'une
      <span class="highlight">alternance</span>
      dans le développement web fullstack. Motivé, curieux et autonome,
      je m'investis pleinement dans chaque projet.
    </p>
    <div class="recherche-tags">
      <span class="rtag">Stage</span>
      <span class="rtag">Alternance</span>
      <span class="rtag">Développement web</span>
      <span class="rtag">Fullstack</span>
    </div>
  </div> -->

  <!-- Loisirs -->
  <div class="about-card">
    <h2 class="about-card-title">
      <span class="about-card-icon">🎮</span> Centres d'intérêt
    </h2>
    <div class="hobbies">
      <?php foreach ($loisirs as $l): ?>
        <span class="hobby"><?= e($l) ?></span>
      <?php endforeach; ?>
    </div>
  </div>

</section>

<!-- CTA contact -->
<div class="contact-banner" style="margin-bottom:56px">
  <h2 class="cb-title">Intéressé par mon profil ?</h2>
  <p class="cb-sub">Recruteurs, n'hésitez pas à me contacter directement.</p>
  <a href="<?= SITE_URL ?>/contact" class="btn btn-primary">M'envoyer un message</a>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>