<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$success = isset($_GET['sent'])  && $_GET['sent']  === '1';
$error   = isset($_GET['error']) && $_GET['error'] === '1';

$page_title  = 'Contact — ' . SITE_NOM;
$page_desc   = 'Contactez ' . SITE_AUTEUR . ' — étudiant développeur web.';
$page_active = 'contact';

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="page-hero-inner">
    <span class="hero-pill"><span class="pill-dot"></span> Me contacter</span>
    <h1 class="page-title">Envoyez-moi un <span class="hero-accent">message</span></h1>
    <p class="page-sub">Une question, une idée, une opportunité ? Je lis tous les messages et réponds rapidement.</p>
  </div>
</section>

<?php if ($success): ?>
  <div class="alert alert-success" style="margin: 0 40px 24px">
    ✓ Message envoyé ! Je vous répondrai dans les plus brefs délais.
  </div>
<?php elseif ($error): ?>
  <div class="alert alert-error" style="margin: 0 40px 24px">
    Une erreur est survenue lors de l'envoi. Veuillez réessayer.
  </div>
<?php endif; ?>

<div class="contact-layout">

  <div class="contact-infos">
    <h2 class="contact-infos-title">Informations</h2>

    <div class="cinfo-list">
      <div class="cinfo-item">
        <div class="cinfo-icon">📍</div>
        <div>
          <div class="cinfo-lbl">Localisation</div>
          <div class="cinfo-val"><?= e(SITE_LOCALITE) ?></div>
        </div>
      </div>
      <div class="cinfo-item">
        <div class="cinfo-icon">🎓</div>
        <div>
          <div class="cinfo-lbl">Statut</div>
          <div class="cinfo-val">Étudiant en informatique</div>
        </div>
      </div>
      <div class="cinfo-item">
        <div class="cinfo-icon">⏱</div>
        <div>
          <div class="cinfo-lbl">Temps de réponse</div>
          <div class="cinfo-val">Moins de 48h</div>
        </div>
      </div>
    </div>

    <div class="contact-socials">
      <?php if (SITE_GITHUB !== '#'): ?>
        <a href="<?= e(SITE_GITHUB) ?>" target="_blank" rel="noopener" class="social-btn">GitHub</a>
      <?php endif; ?>
      <?php if (SITE_LINKEDIN !== '#'): ?>
        <a href="<?= e(SITE_LINKEDIN) ?>" target="_blank" rel="noopener" class="social-btn">LinkedIn</a>
      <?php endif; ?>
    </div>

    <div class="contact-note">
      <span class="contact-note-icon">🔒</span>
      <p>Votre adresse e-mail ne sera jamais partagée. Le message m'est transmis de façon sécurisée.</p>
    </div>
  </div>

  <div class="contact-form-wrap">
    <h2 class="contact-form-title">Votre message (Si possible en français)</h2>

    <form action="<?= SITE_URL ?>/actions/send_contact.php" method="POST" class="contact-form" id="contact-form" novalidate>
      <?php
      if (session_status() === PHP_SESSION_NONE) session_start();
      if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
      ?>
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

      <div class="form-row">
        <div class="field">
          <label for="nom">Votre nom <span class="req">*</span></label>
          <input type="text" id="nom" name="nom" placeholder="Jean Dupont"
                 maxlength="100" required autocomplete="name">
        </div>
        <div class="field">
          <label for="email">Votre e-mail <span class="req">*</span></label>
          <input type="email" id="email" name="email" placeholder="votre@email.com"
                 maxlength="150" required autocomplete="email">
        </div>
      </div>

      <div class="field">
        <label for="objet">Motif de contact <span class="req">*</span></label>
        <select id="objet" name="objet" required>
          <option value="">— Choisissez un objet —</option>
          <option value="Collaboration">Une collaboration</option>
          <option value="Question sur un projet">Question sur un projet</option>
          <option value="Autre">Autre</option>
        </select>
      </div>

      <div class="field">
        <label for="message">Message <span class="req">*</span></label>
        <textarea id="message" name="message" rows="6"
                  placeholder="Écrivez votre message ici..." maxlength="3000" required></textarea>
        <span class="char-count" id="char-count">0 / 3000</span>
      </div>

      <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">

      <button type="submit" class="btn btn-primary btn-submit" id="submit-btn">
        Envoyer le message
      </button>
    </form>
  </div>

</div>

<script>
const textarea  = document.getElementById('message');
const charCount = document.getElementById('char-count');
if (textarea && charCount) {
  textarea.addEventListener('input', () => {
    const n = textarea.value.length;
    charCount.textContent = n + ' / 3000';
    charCount.style.color = n > 2700 ? '#f87171' : '';
  });
}
const form = document.getElementById('contact-form');
const btn  = document.getElementById('submit-btn');
if (form && btn) {
  form.addEventListener('submit', () => {
    btn.disabled = true;
    btn.textContent = 'Envoi en cours…';
  });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
