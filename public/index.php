<?php

declare(strict_types=1);

/**
 * Point d'entrée : affichage écran (public) et administration (authentification par secret).
 * - action=display (défaut) : grille lecture seule, rafraîchissement auto 1 s.
 * - action=login / login_check / logout / admin : espace admin (secret en config).
 */
session_start();

$projectRoot = dirname(__DIR__);

require_once $projectRoot . '/config.php';
require_once $projectRoot . '/inc/db.php';
require_once $projectRoot . '/src/functions.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

if (!isset($pdo)) {
    die('Erreur : base de données non initialisée.');
}

$drawModel = new \App\Model\DrawModel($pdo);
$lotoController = new \App\Controller\LotoController($drawModel);

$action = $_POST['action'] ?? $_GET['action'] ?? 'display';

// Déconnexion
if ($action === 'logout') {
    $_SESSION['admin_authenticated'] = false;
    session_destroy();
    header('Location: index.php?action=login');
    exit;
}

// Connexion : formulaire ou vérification du secret
if ($action === 'login') {
    if (is_authenticated()) {
        header('Location: index.php?action=admin');
        exit;
    }
    $loginError = isset($_GET['error']);
    $pageTitle = 'Chiffres — Connexion';
    require $projectRoot . '/src/View/auth/login.php';
    exit;
}

if ($action === 'login_check') {
    $secret = truncate_to(trim((string) ($_POST['secret'] ?? '')), ADMIN_SECRET_MAX);
    if ($secret !== '' && $secret === ADMIN_SECRET) {
        $_SESSION['admin_authenticated'] = true;
        header('Location: index.php?action=admin');
        exit;
    }
    header('Location: index.php?action=login&error=1');
    exit;
}

// API JSON : numéros tirés (pour rafraîchissement sans rechargement)
if ($action === 'drawn_json') {
    header('Content-Type: application/json');
    echo json_encode(array_keys($drawModel->getDrawnNumbers()));
    exit;
}

// Affichage écran : public, pas d'auth
if ($action === 'display') {
    $lotoController->display();
    exit;
}

// Actions admin : authentification requise
require_admin();

match ($action) {
    'admin' => $lotoController->admin(),
    'toggle' => $lotoController->toggle(),
    'reset' => $lotoController->reset(),
    default => $lotoController->admin(),
};
