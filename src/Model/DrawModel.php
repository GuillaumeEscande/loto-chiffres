<?php

declare(strict_types=1);

namespace App\Model;

use PDO;

/**
 * Modèle : état du tirage (numéros tirés).
 */
class DrawModel
{
    public function __construct(private PDO $pdo) {}

    /** Liste des numéros actuellement tirés (clés = numéros). */
    public function getDrawnNumbers(): array
    {
        $st = $this->pdo->query("SELECT num FROM drawn_numbers ORDER BY num");
        $rows = $st->fetchAll(PDO::FETCH_COLUMN);
        return array_fill_keys(array_map('intval', $rows), true);
    }

    /** Vérifie si un numéro est tiré. */
    public function isDrawn(int $num): bool
    {
        $st = $this->pdo->prepare("SELECT 1 FROM drawn_numbers WHERE num = ?");
        $st->execute([$num]);
        return (bool) $st->fetchColumn();
    }

    /** Inverse l'état d'un numéro (tiré ↔ non tiré). */
    public function toggle(int $num): void
    {
        $num = max(LOTO_MIN, min(LOTO_MAX, $num));
        if ($this->isDrawn($num)) {
            $this->pdo->prepare("DELETE FROM drawn_numbers WHERE num = ?")->execute([$num]);
        } else {
            $this->pdo->prepare("INSERT OR IGNORE INTO drawn_numbers (num) VALUES (?)")->execute([$num]);
        }
    }

    /** Remet tous les numéros à « non tiré ». */
    public function resetAll(): void
    {
        $this->pdo->exec("DELETE FROM drawn_numbers");
    }
}
