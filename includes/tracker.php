<?php
/**
 * Tracker de visites
 * - Compte 1 visiteur unique par session
 * - Compte chaque page vue
 * - Stocke par jour en BDD
 */
function tracker_visite(PDO $pdo): void {
    if (session_status() === PHP_SESSION_NONE) session_start();

    $today = date('Y-m-d');

    // S'assurer que la ligne du jour existe
    $pdo->prepare(
        'INSERT INTO visites (date, nb_visiteurs, nb_pages_vues)
         VALUES (?, 0, 0)
         ON DUPLICATE KEY UPDATE date = date'
    )->execute([$today]);

    // Compter la page vue à chaque chargement
    $pdo->prepare(
        'UPDATE visites SET nb_pages_vues = nb_pages_vues + 1 WHERE date = ?'
    )->execute([$today]);

    // Compter le visiteur une seule fois par session
    if (empty($_SESSION['visite_comptee'])) {
        $_SESSION['visite_comptee'] = true;
        $pdo->prepare(
            'UPDATE visites SET nb_visiteurs = nb_visiteurs + 1 WHERE date = ?'
        )->execute([$today]);
    }
}

/**
 * Récupérer les stats des N derniers jours
 */
function get_stats_visites(PDO $pdo, int $jours = 30): array {
    $stmt = $pdo->prepare(
        'SELECT date, nb_visiteurs, nb_pages_vues
         FROM visites
         ORDER BY date DESC
         LIMIT ?'
    );
    $stmt->execute([$jours]);
    return $stmt->fetchAll();
}

/**
 * Stats globales
 */
function get_stats_totales(PDO $pdo): array {
    $row = $pdo->query(
        'SELECT
           SUM(nb_visiteurs)   AS total_visiteurs,
           SUM(nb_pages_vues)  AS total_pages,
           COUNT(*)            AS nb_jours
         FROM visites'
    )->fetch();

    $aujourd_hui = $pdo->prepare('SELECT nb_visiteurs, nb_pages_vues FROM visites WHERE date = ?');
    $aujourd_hui->execute([date('Y-m-d')]);
    $today = $aujourd_hui->fetch();

    return [
        'total_visiteurs'  => (int)($row['total_visiteurs']  ?? 0),
        'total_pages'      => (int)($row['total_pages']      ?? 0),
        'nb_jours'         => (int)($row['nb_jours']         ?? 0),
        'today_visiteurs'  => (int)($today['nb_visiteurs']   ?? 0),
        'today_pages'      => (int)($today['nb_pages_vues']  ?? 0),
    ];
}
