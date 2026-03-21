<?php
// $page_title et $page_desc doivent être définis avant l'include
$page_title  = $page_title  ?? SITE_NOM;
$page_desc   = $page_desc   ?? 'Portfolio de ' . SITE_AUTEUR . ' — ' . SITE_METIER;
$page_active = $page_active ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= e($page_desc) ?>">
  <title><?= e($page_title) ?></title>
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>

<nav class="nav" id="nav">
  <a href="<?= SITE_URL ?>/" class="nav-logo">
    <span class="logo-a"><?= mb_substr(SITE_AUTEUR, 0, 1) ?></span><span class="logo-b"><?= mb_substr(explode(' ', SITE_AUTEUR)[1] ?? '', 0, 1) ?>.</span>
  </a>
  <button class="nav-burger" id="burger" aria-label="Menu">
    <span></span><span></span><span></span>
  </button>
  <div class="nav-links" id="nav-links">
    <a href="<?= SITE_URL ?>/"        class="<?= $page_active === 'accueil' ? 'active' : '' ?>">Accueil</a>
    <a href="<?= SITE_URL ?>/projets" class="<?= $page_active === 'projets' ? 'active' : '' ?>">Projets</a>
    <a href="<?= SITE_URL ?>/apropos" class="<?= $page_active === 'apropos' ? 'active' : '' ?>">À propos</a>
    <a href="<?= SITE_URL ?>/contact" class="<?= $page_active === 'contact' ? 'active' : '' ?>">Contact</a>
  </div>
</nav>

<main>