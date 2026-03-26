<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Chargement PHPMailer
require_once __DIR__ . '/../includes/phpmailer/Exception.php';
require_once __DIR__ . '/../includes/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../includes/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if (session_status() === PHP_SESSION_NONE) session_start();

// Accepter uniquement POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . SITE_URL . '/contact.php');
    exit;
}

// Vérification CSRF
if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    header('Location: ' . SITE_URL . '/contact.php?error=1');
    exit;
}
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Honeypot anti-spam
if (!empty($_POST['website'])) {
    header('Location: ' . SITE_URL . '/contact.php?sent=1');
    exit;
}

// Récupérer et nettoyer les champs
$nom     = clean($_POST['nom']     ?? '');
$email   = clean($_POST['email']   ?? '');
$objet   = clean($_POST['objet']   ?? '');
$message = clean($_POST['message'] ?? '');

// Validation
$objets_valides = ['Stage', 'Alternance', 'Collaboration', 'Question sur un projet', 'Autre'];
$errors = [];

if (mb_strlen($nom) < 2 || mb_strlen($nom) > 100)              $errors[] = 'nom';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))                 $errors[] = 'email';
if (!in_array($objet, $objets_valides, true))                   $errors[] = 'objet';
if (mb_strlen($message) < 10 || mb_strlen($message) > 3000)    $errors[] = 'message';

if (!empty($errors)) {
    header('Location: ' . SITE_URL . '/contact.php?error=1');
    exit;
}

// Sauvegarde en BDD
try {
    $pdo  = get_db();
    $stmt = $pdo->prepare(
        'INSERT INTO messages (nom, email, objet, message, lu, created_at) VALUES (?, ?, ?, ?, 0, NOW())'
    );
    $stmt->execute([$nom, $email, $objet, $message]);
} catch (Exception $e) {
    error_log('Contact BDD error: ' . $e->getMessage());
}

// Fonction d'envoi PHPMailer
function envoyer_mail(string $to, string $to_name, string $subject, string $body): bool {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NOM);
        $mail->addAddress($to, $to_name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('PHPMailer error: ' . $e->errorMessage());
        return false;
    }
}

// Template HTML commun
function template_mail(string $titre, string $contenu): string {
    return '<!DOCTYPE html><html lang="fr"><body style="font-family:Arial,sans-serif;background:#0f0a1e;color:#f9fafb;padding:32px;max-width:600px;margin:auto">
    <div style="border:1px solid rgba(124,58,237,.4);border-radius:12px;padding:28px;background:#160d2a">
        <h2 style="color:#a78bfa;margin-top:0">' . $titre . '</h2>
        ' . $contenu . '
        <p style="color:#4a3d60;font-size:11px;margin-top:20px">— ' . htmlspecialchars(SITE_NOM) . ' · ' . htmlspecialchars(SITE_URL) . '</p>
    </div></body></html>';
}

// 1) Mail de notification à toi (les deux adresses)
$corps_notif = template_mail(
    'Nouveau message reçu',
    '<table style="width:100%;font-size:14px;border-collapse:collapse">
        <tr><td style="padding:8px 0;color:#7c6a9a;width:100px">Nom</td><td style="padding:8px 0;color:#f9fafb"><strong>' . htmlspecialchars($nom) . '</strong></td></tr>
        <tr><td style="padding:8px 0;color:#7c6a9a">E-mail</td><td style="padding:8px 0"><a href="mailto:' . htmlspecialchars($email) . '" style="color:#a78bfa">' . htmlspecialchars($email) . '</a></td></tr>
        <tr><td style="padding:8px 0;color:#7c6a9a">Objet</td><td style="padding:8px 0;color:#f9fafb">' . htmlspecialchars($objet) . '</td></tr>
    </table>
    <hr style="border:0;border-top:1px solid rgba(124,58,237,.2);margin:16px 0">
    <p style="color:#7c6a9a;font-size:13px;margin-bottom:6px">Message :</p>
    <div style="background:#0f0a1e;border-radius:8px;padding:16px;color:#f9fafb;font-size:14px;line-height:1.7;white-space:pre-wrap">' . htmlspecialchars($message) . '</div>
    <p style="color:#4a3d60;font-size:11px;margin-top:16px">Reçu le ' . date('d/m/Y à H:i') . '</p>'
);

envoyer_mail(MAIL_DEST,  SITE_AUTEUR, '[Portfolio] Nouveau message : ' . $objet . ' — ' . $nom, $corps_notif);
if (MAIL_DEST2 && MAIL_DEST2 !== MAIL_DEST) {
    envoyer_mail(MAIL_DEST2, SITE_AUTEUR, '[Portfolio] Nouveau message : ' . $objet . ' — ' . $nom, $corps_notif);
}

// 2) Mail de confirmation à l'expéditeur
$corps_conf = template_mail(
    'Message bien reçu !',
    '<p style="color:#7c6a9a;line-height:1.7">Bonjour <strong style="color:#f9fafb">' . htmlspecialchars($nom) . '</strong>,</p>
    <p style="color:#7c6a9a;line-height:1.7">Votre message a bien été reçu. Je vous répondrai dans les plus brefs délais.</p>
    <div style="background:#0f0a1e;border-radius:8px;padding:16px;margin:20px 0;border-left:3px solid #7c3aed">
        <p style="color:#4a3d60;font-size:12px;margin:0 0 6px">Votre message :</p>
        <p style="color:#7c6a9a;font-size:13px;margin:0;white-space:pre-wrap">' . htmlspecialchars(mb_substr($message, 0, 300)) . (mb_strlen($message) > 300 ? '…' : '') . '</p>
    </div>
    <p style="color:#4a3d60;font-size:12px">Ceci est un message automatique, merci de ne pas y répondre directement.</p>'
);

envoyer_mail($email, $nom, 'Message bien reçu — ' . SITE_NOM, $corps_conf);

header('Location: ' . SITE_URL . '/contact.php?sent=1');
exit;
