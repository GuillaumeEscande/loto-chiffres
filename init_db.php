<?php
/**
 * Création des tables si besoin (utilise $pdo déjà défini par inc/db.php).
 * drawn_numbers : numéros actuellement tirés (un enregistrement par numéro tiré).
 */
$pdo->exec("
    CREATE TABLE IF NOT EXISTS drawn_numbers (
        num INTEGER PRIMARY KEY CHECK (num >= 1 AND num <= 90),
        drawn_at TEXT NOT NULL DEFAULT (datetime('now', 'localtime'))
    )
");
