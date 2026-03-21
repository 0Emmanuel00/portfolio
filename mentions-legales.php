<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$page_title  = 'Mentions légales — ' . SITE_NOM;
$page_desc   = 'Mentions légales du portfolio de ' . SITE_AUTEUR;
$page_active = '';

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="page-hero-inner">
    <span class="hero-pill"><span class="pill-dot"></span> Informations légales</span>
    <h1 class="page-title">Mentions <span class="hero-accent">légales</span></h1>
    <p class="page-sub">Conformément à la loi n°2004-575 du 21 juin 2004 pour la confiance dans l'économie numérique.</p>
  </div>
</section>

<div class="mentions-wrap">

  <div class="mentions-card">
    <h2 class="mentions-title">Éditeur du site</h2>
    <div class="mentions-content">
      <p>Ce site est édité par <strong><?= e(SITE_AUTEUR) ?></strong>, étudiant en informatique.</p>
      <p>Localisation : <?= e(SITE_LOCALITE) ?></p>
      <?php if (SITE_GITHUB !== '#'): ?>
        <p>GitHub : <a href="<?= e(SITE_GITHUB) ?>" target="_blank" rel="noopener"><?= e(SITE_GITHUB) ?></a></p>
      <?php endif; ?>
      <?php if (SITE_LINKEDIN !== '#'): ?>
        <p>LinkedIn : <a href="<?= e(SITE_LINKEDIN) ?>" target="_blank" rel="noopener"><?= e(SITE_LINKEDIN) ?></a></p>
      <?php endif; ?>
    </div>
  </div>

  <div class="mentions-card">
    <h2 class="mentions-title">Hébergement</h2>
    <div class="mentions-content">
      <p>Ce site est hébergé par un prestataire d'hébergement web.</p>
      <p>Les informations d'hébergement sont disponibles sur demande via le formulaire de contact.</p>
    </div>
  </div>

  <div class="mentions-card">
    <h2 class="mentions-title">Propriété intellectuelle</h2>
    <div class="mentions-content">
      <p>L'ensemble du contenu de ce site (textes, images, code source, projets présentés) est la propriété exclusive de <strong><?= e(SITE_AUTEUR) ?></strong>, sauf mention contraire.</p>
      <p>Toute reproduction, représentation, modification, publication ou transmission de tout ou partie du contenu de ce site, par quelque moyen que ce soit, est interdite sans autorisation préalable.</p>
    </div>
  </div>

  <div class="mentions-card">
    <h2 class="mentions-title">Données personnelles</h2>
    <div class="mentions-content">
      <p>Ce site collecte uniquement les données que vous saisissez volontairement via le formulaire de contact (nom, adresse e-mail, message).</p>
      <p>Ces données sont utilisées exclusivement pour répondre à vos messages et ne sont jamais transmises à des tiers.</p>
      <p>Conformément au Règlement Général sur la Protection des Données (RGPD), vous disposez d'un droit d'accès, de rectification et de suppression de vos données. Pour exercer ce droit, contactez-moi via le <a href="<?= SITE_URL ?>/contact.php" class="mentions-link">formulaire de contact</a>.</p>
    </div>
  </div>

  <div class="mentions-card">
    <h2 class="mentions-title">Cookies</h2>
    <div class="mentions-content">
      <p>Ce site n'utilise pas de cookies de traçage ou publicitaires.</p>
      <p>Un cookie de session est utilisé uniquement pour la partie administration du site, inaccessible aux visiteurs.</p>
    </div>
  </div>

  <div class="mentions-card">
    <h2 class="mentions-title">Responsabilité</h2>
    <div class="mentions-content">
      <p>Les informations présentes sur ce site sont fournies à titre indicatif. L'éditeur s'efforce de maintenir ces informations à jour mais ne peut garantir leur exhaustivité.</p>
      <p>Ce site peut contenir des liens vers des sites externes. L'éditeur n'est pas responsable du contenu de ces sites tiers.</p>
    </div>
  </div>

  <div class="mentions-card">
    <h2 class="mentions-title">Contact</h2>
    <div class="mentions-content">
      <p>Pour toute question relative à ces mentions légales, vous pouvez me contacter via le <a href="<?= SITE_URL ?>/contact.php" class="mentions-link">formulaire de contact</a>.</p>
    </div>
  </div>

  <p class="mentions-date">Dernière mise à jour : <?= date('d/m/Y') ?></p>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>