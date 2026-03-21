<?php
// Chargement du .env
$env_path = __DIR__ . '/config.ini';
if (file_exists($env_path)) {
    foreach (file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$key, $val] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($val);
    }
}

// Infos du site
define('SITE_NOM',        $_ENV['SITE_NOM']        ?? 'Mon Portfolio');
define('SITE_URL',        $_ENV['SITE_URL']         ?? 'http://localhost/portfolio');
define('SITE_AUTEUR',     $_ENV['SITE_AUTEUR']      ?? 'Prénom Nom');
define('SITE_METIER',     $_ENV['SITE_METIER']      ?? 'Développeur web');
define('SITE_LOCALITE',   $_ENV['SITE_LOCALITE']    ?? 'France');
define('SITE_GITHUB',     $_ENV['SITE_GITHUB']      ?? '#');
define('SITE_LINKEDIN',   $_ENV['SITE_LINKEDIN']    ?? '#');

// Base de données
define('DB_HOST',   $_ENV['DB_HOST']   ?? 'localhost');
define('DB_NAME',   $_ENV['DB_NAME']   ?? 'portfolio');
define('DB_USER',   $_ENV['DB_USER']   ?? 'root');
define('DB_PASS',   $_ENV['DB_PASS']   ?? '');
define('DB_CHARSET',                      'utf8mb4');

// Mail
define('MAIL_DEST',  $_ENV['MAIL_DEST']  ?? '');  // Ton adresse perso
define('MAIL_FROM',  $_ENV['MAIL_FROM']  ?? '');  // Adresse d'envoi du site

// Environnement
define('ENV', $_ENV['APP_ENV'] ?? 'production');

// Affichage erreurs (dev uniquement)
if (ENV === 'dev') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
}