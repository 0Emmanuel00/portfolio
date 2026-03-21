<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

// Accepter uniquement POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . SITE_URL . '/contact');
    exit;
}

// Vérification CSRF
if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    header('Location: ' . SITE_URL . '/contact?error=1');
    exit;
}
// Renouveler le token après usage
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Honeypot anti-spam : si ce champ est rempli, c'est un bot
if (!empty($_POST['website'])) {
    header('Location: ' . SITE_URL . '/contact?sent=1'); // Fausse réussite
    exit;
}

// Récupérer et nettoyer les champs
$nom     = clean($_POST['nom']     ?? '');
$email   = clean($_POST['email']   ?? '');
$objet   = clean($_POST['objet']   ?? '');
$message = clean($_POST['message'] ?? '');

// Validation
$objets_valides = ['Proposition de recrutement', 'Stage', 'Alternance', 'Collaboration', 'Autre'];
$errors = [];

if (mb_strlen($nom) < 2 || mb_strlen($nom) > 100) {
    $errors[] = 'nom';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
    $errors[] = 'email';
}
if (!in_array($objet, $objets_valides, true)) {
    $errors[] = 'objet';
}
if (mb_strlen($message) < 10 || mb_strlen($message) > 3000) {
    $errors[] = 'message';
}

if (!empty($errors)) {
    header('Location: ' . SITE_URL . '/contact?error=1');
    exit;
}

// Sauvegarde en BDD
try {
    $pdo  = get_db();
    $stmt = $pdo->prepare(
        'INSERT INTO messages (nom, email, objet, message, lu, created_at)
         VALUES (?, ?, ?, ?, 0, NOW())'
    );
    $stmt->execute([$nom, $email, $objet, $message]);
} catch (Exception $e) {
    // Continuer même si la BDD échoue — l'email reste prioritaire
    error_log('Contact BDD error: ' . $e->getMessage());
}

// --- Envoi des mails ---
$headers_base = [
    'Content-Type: text/html; charset=UTF-8',
    'From: ' . SITE_NOM . ' <' . MAIL_FROM . '>',
    'X-Mailer: PHP/' . phpversion(),
];

// 1) Mail de notification à toi
$sujet_notif = '[Portfolio] Nouveau message : ' . $objet . ' — ' . $nom;
$corps_notif = '
<!DOCTYPE html><html lang="fr"><body style="font-family:\'Comic Sans MS\',cursive;background:#0f0a1e;color:#f9fafb;padding:32px;max-width:600px;margin:auto">
  <div style="border:1px solid rgba(124,58,237,.4);border-radius:12px;padding:28px;background:#160d2a">
    <h2 style="color:#a78bfa;margin-top:0">Nouveau message reçu</h2>
    <table style="width:100%;border-collapse:collapse;font-size:14px">
      <tr><td style="padding:8px 0;color:#7c6a9a;width:100px">Nom</td>
          <td style="padding:8px 0;color:#f9fafb"><strong>' . htmlspecialchars($nom) . '</strong></td></tr>
      <tr><td style="padding:8px 0;color:#7c6a9a">E-mail</td>
          <td style="padding:8px 0"><a href="mailto:' . htmlspecialchars($email) . '" style="color:#a78bfa">' . htmlspecialchars($email) . '</a></td></tr>
      <tr><td style="padding:8px 0;color:#7c6a9a">Objet</td>
          <td style="padding:8px 0;color:#f9fafb">' . htmlspecialchars($objet) . '</td></tr>
    </table>
    <hr style="border:0;border-top:1px solid rgba(124,58,237,.2);margin:16px 0">
    <p style="color:#7c6a9a;font-size:13px;margin-bottom:6px">Message :</p>
    <div style="background:#0f0a1e;border-radius:8px;padding:16px;color:#f9fafb;font-size:14px;line-height:1.7;white-space:pre-wrap">' . htmlspecialchars($message) . '</div>
    <p style="color:#4a3d60;font-size:11px;margin-top:20px">Reçu le ' . date('d/m/Y à H:i') . ' · ' . SITE_NOM . '</p>
  </div>
</body></html>';

mail(
    MAIL_DEST,
    $sujet_notif,
    $corps_notif,
    implode("\r\n", array_merge($headers_base, ['Reply-To: ' . $nom . ' <' . $email . '>']))
);

// 2) Mail de confirmation à l'expéditeur
$sujet_conf = 'Message bien reçu — ' . SITE_NOM;
$corps_conf = '
<!DOCTYPE html><html lang="fr"><body style="font-family:\'Comic Sans MS\',cursive;background:#0f0a1e;color:#f9fafb;padding:32px;max-width:600px;margin:auto">
  <div style="border:1px solid rgba(124,58,237,.4);border-radius:12px;padding:28px;background:#160d2a">
    <h2 style="color:#a78bfa;margin-top:0">Message bien reçu !</h2>
    <p style="color:#7c6a9a;line-height:1.7">Bonjour <strong style="color:#f9fafb">' . htmlspecialchars($nom) . '</strong>,</p>
    <p style="color:#7c6a9a;line-height:1.7">
      Votre message a bien été reçu. Je vous répondrai dans les plus brefs délais, généralement sous 24h.
    </p>
    <div style="background:#0f0a1e;border-radius:8px;padding:16px;margin:20px 0;border-left:3px solid #7c3aed">
      <p style="color:#4a3d60;font-size:12px;margin:0 0 6px">Votre message :</p>
      <p style="color:#7c6a9a;font-size:13px;margin:0;white-space:pre-wrap">' . htmlspecialchars(mb_substr($message, 0, 300)) . (mb_strlen($message) > 300 ? '…' : '') . '</p>
    </div>
    <p style="color:#4a3d60;font-size:12px;margin-top:20px">
      Ceci est un message automatique, merci de ne pas y répondre directement.<br>
      — ' . htmlspecialchars(SITE_AUTEUR) . ' · ' . SITE_NOM . '
    </p>
  </div>
</body></html>';

mail(
    $email,
    $sujet_conf,
    $corps_conf,
    implode("\r\n", $headers_base)
);

// Redirection vers la page de succès
header('Location: ' . SITE_URL . '/contact?sent=1');
exit;