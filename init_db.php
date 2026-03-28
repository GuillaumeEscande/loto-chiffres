<?php
/**
 * Création / migration des tables.
 *   parties       : liste des parties, une seule active à la fois.
 *   drawn_numbers : numéros tirés par partie (clé composite partie_id + num).
 */

// --- Table parties -----------------------------------------------------------
$pdo->exec("
    CREATE TABLE IF NOT EXISTS parties (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        numero     INTEGER NOT NULL UNIQUE,
        active     INTEGER NOT NULL DEFAULT 0,
        created_at TEXT    NOT NULL DEFAULT (datetime('now','localtime'))
    )
");

// --- Migration drawn_numbers → schéma avec partie_id -------------------------
$cols     = $pdo->query("PRAGMA table_info(drawn_numbers)")->fetchAll(PDO::FETCH_ASSOC);
$colNames = array_column($cols, 'name');

if (!in_array('partie_id', $colNames, true)) {
    // Sauvegarde des données existantes (ancien schéma sans partie_id)
    $existing = [];
    if (!empty($cols)) {
        $existing = $pdo->query("SELECT num, drawn_at FROM drawn_numbers")->fetchAll(PDO::FETCH_ASSOC);
        $pdo->exec("DROP TABLE drawn_numbers");
    }

    // Nouveau schéma
    $pdo->exec("
        CREATE TABLE drawn_numbers (
            partie_id INTEGER NOT NULL REFERENCES parties(id) ON DELETE CASCADE,
            num       INTEGER NOT NULL CHECK (num >= 1 AND num <= 90),
            drawn_at  TEXT    NOT NULL DEFAULT (datetime('now','localtime')),
            PRIMARY KEY (partie_id, num)
        )
    ");

    // Assure qu'il existe au moins une partie
    $partyCount = (int) $pdo->query("SELECT COUNT(*) FROM parties")->fetchColumn();
    if ($partyCount === 0) {
        $pdo->exec("INSERT INTO parties (numero, active) VALUES (1, 1)");
    }

    // Récupère l'id de la première partie pour y migrer les données
    $partieId = (int) $pdo->query("SELECT id FROM parties ORDER BY numero ASC LIMIT 1")->fetchColumn();

    // Migration des numéros existants vers la partie 1
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO drawn_numbers (partie_id, num, drawn_at) VALUES (?, ?, ?)");
    foreach ($existing as $row) {
        $stmt->execute([$partieId, (int) $row['num'], $row['drawn_at']]);
    }
}

// --- Garantit qu'une partie active existe toujours ---------------------------
$activeCount = (int) $pdo->query("SELECT COUNT(*) FROM parties WHERE active = 1")->fetchColumn();
if ($activeCount === 0) {
    $firstId = $pdo->query("SELECT id FROM parties ORDER BY numero ASC LIMIT 1")->fetchColumn();
    if ($firstId) {
        $pdo->prepare("UPDATE parties SET active = 1 WHERE id = ?")->execute([$firstId]);
    } else {
        $pdo->exec("INSERT INTO parties (numero, active) VALUES (1, 1)");
    }
}
