<?php
/**
 * Connexion PDO partagée + création des tables si besoin
 */
if (!defined('BASE_PATH')) {
    require_once dirname(__DIR__) . '/config.php';
}

try {
    $pdo = new PDO(DB_DSN);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur base de données : ' . $e->getMessage());
}

require_once dirname(__DIR__) . '/init_db.php';
