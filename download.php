<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Mode compteur uniquement — appelé via fetch() JS en arrière-plan
if (isset($_GET['count_only'])) {
    if ($id) {
        $pdo = get_db();
        $pdo->prepare('UPDATE projets SET nombre_download = nombre_download + 1 WHERE id = ?')
            ->execute([$id]);
    }
    http_response_code(200);
    exit;
}

// Mode téléchargement normal (fallback si JS désactivé)
if (!$id) {
    header('Location: ' . SITE_URL . '/projets.php');
    exit;
}

$pdo  = get_db();
$stmt = $pdo->prepare('SELECT * FROM projets WHERE id = ? AND visible = 1');
$stmt->execute([$id]);
$projet = $stmt->fetch();

if (!$projet || !$projet['url_download']) {
    header('Location: ' . SITE_URL . '/projets.php');
    exit;
}

// Incrémenter le compteur
$pdo->prepare('UPDATE projets SET nombre_download = nombre_download + 1 WHERE id = ?')->execute([$id]);

$fichier = $projet['url_download'];

// URL externe → rediriger
if (str_starts_with($fichier, 'http://') || str_starts_with($fichier, 'https://')) {
    header('Location: ' . $fichier);
    exit;
}

// Fichier local → servir directement
$chemin = realpath(__DIR__ . '/../' . $fichier);
$base   = realpath(__DIR__ . '/../assets/uploads/');

if (!$chemin || !str_starts_with($chemin, $base) || !file_exists($chemin)) {
    header('Location: ' . SITE_URL . '/projets.php');
    exit;
}

$nom_fichier = basename($chemin);
$mime        = mime_content_type($chemin) ?: 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $nom_fichier . '"');
header('Content-Length: ' . filesize($chemin));
header('Cache-Control: no-cache, must-revalidate');
readfile($chemin);
exit;