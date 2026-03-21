<?php

// Sécuriser l'affichage d'une chaîne
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Récupérer les projets visibles (avec leurs techs)
function get_projets(PDO $pdo, string $type = ''): array {
    $sql = 'SELECT * FROM projets WHERE visible = 1';
    $params = [];
    if ($type !== '') {
        $sql .= ' AND type = ?';
        $params[] = $type;
    }
    $sql .= ' ORDER BY ordre ASC, created_at DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $projets = $stmt->fetchAll();

    // Charger les techs de chaque projet
    foreach ($projets as &$p) {
        $p['techs'] = get_techs_projet($pdo, $p['id']);
    }
    return $projets;
}

// Récupérer un projet par son ID
function get_projet(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare('SELECT * FROM projets WHERE id = ? AND visible = 1');
    $stmt->execute([$id]);
    $projet = $stmt->fetch();
    if (!$projet) return null;
    $projet['techs'] = get_techs_projet($pdo, $id);
    return $projet;
}

// Récupérer les technos d'un projet
function get_techs_projet(PDO $pdo, int $projet_id): array {
    $stmt = $pdo->prepare(
        'SELECT t.* FROM technologies t
         JOIN projet_tech pt ON pt.tech_id = t.id
         WHERE pt.projet_id = ?'
    );
    $stmt->execute([$projet_id]);
    return $stmt->fetchAll();
}

// Compter les projets visibles
function count_projets(PDO $pdo): int {
    return (int) $pdo->query('SELECT COUNT(*) FROM projets WHERE visible = 1')->fetchColumn();
}

// Compter les technologies distinctes utilisées
function count_techs(PDO $pdo): int {
    return (int) $pdo->query('SELECT COUNT(*) FROM technologies')->fetchColumn();
}

// Nettoyer une entrée utilisateur
function clean(string $str): string {
    return trim(strip_tags($str));
}

// Vérifier si admin connecté
function is_admin(): bool {
    return isset($_SESSION['admin']) && $_SESSION['admin'] === true;
}