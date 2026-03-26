<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/phpmailer/Exception.php';
require_once __DIR__ . '/includes/phpmailer/PHPMailer.php';
require_once __DIR__ . '/includes/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
try {
    $mail->SMTPDebug  = SMTP::DEBUG_SERVER;
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';
    $mail->setFrom(MAIL_FROM, MAIL_FROM_NOM);
    $mail->addAddress(MAIL_DEST);
    $mail->Subject = 'Test mail portfolio';
    $mail->Body    = 'Test envoi PHPMailer OK !';
    $mail->send();
    echo '<p style="color:green">Mail envoyé avec succès !</p>';
} catch (Exception $e) {
    echo '<p style="color:red">Erreur : ' . $e->errorMessage() . '</p>';
}