<?php
require_once __DIR__ . '/../includes/config.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: ' . SITE_URL . '/admin/login.php');
    exit;
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
    session_unset();
    session_destroy();
    header('Location: ' . SITE_URL . '/admin/login.php?expired=1');
    exit;
}
$_SESSION['last_activity'] = time();