<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!empty($_SESSION['admin'])) { header('Location: ' . SITE_URL . '/admin/'); exit; }

$error = $expired = false;
if (isset($_GET['expired'])) $expired = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
  if (!isset($_SESSION['login_last']))     $_SESSION['login_last']     = time();

  if ($_SESSION['login_attempts'] >= 5 && (time() - $_SESSION['login_last']) < 300) {
    $error = 'Trop de tentatives. Réessayez dans 5 minutes.';
  } else {
    $login    = trim($_POST['login']    ?? '');
    $password = trim($_POST['password'] ?? '');
    if (!$login || !$password) {
      $error = 'Veuillez remplir tous les champs.';
    } else {
      $pdo  = get_db();
      $stmt = $pdo->prepare('SELECT * FROM admin WHERE login = ? LIMIT 1');
      $stmt->execute([$login]);
      $admin = $stmt->fetch();
      if ($admin && password_verify($password, $admin['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin']         = true;
        $_SESSION['admin_login']   = $admin['login'];
        $_SESSION['last_activity'] = time();
        $_SESSION['login_attempts']= 0;
        $pdo->prepare('UPDATE admin SET last_login = NOW() WHERE id = ?')->execute([$admin['id']]);
        header('Location: ' . SITE_URL . '/admin/'); exit;
      } else {
        $_SESSION['login_attempts']++;
        $_SESSION['login_last'] = time();
        $error = 'Identifiant ou mot de passe incorrect.';
        sleep(1);
      }
    }
  }
}

function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion — Admin</title>
  <link rel="stylesheet" href="<?= SITE_URL ?>/admin/admin.css">
</head>
<body>
<div class="login-page">
  <div class="login-card">

    <div class="login-logo-wrap">
      <div class="login-logo-mark">A</div>
    </div>

    <h1 class="login-title">Espace admin</h1>
    <p class="login-sub">Connectez-vous pour gérer votre portfolio</p>

    <?php if ($expired): ?>
      <div class="alert alert-error">Session expirée. Reconnectez-vous.</div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="login-form">
      <div class="field">
        <label for="login">Identifiant</label>
        <input type="text" id="login" name="login" placeholder="admin" required autofocus autocomplete="username">
      </div>
      <div class="field">
        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn btn-primary">Se connecter</button>
    </form>

    <a href="<?= SITE_URL ?>/" class="login-back">← Retour au site</a>
  </div>
</div>
</body>
</html>
