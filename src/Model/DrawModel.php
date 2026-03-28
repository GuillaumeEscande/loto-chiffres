<?php

declare(strict_types=1);

namespace App\Model;

use PDO;

/**
 * Modèle : état du tirage pour une partie donnée.
 */
class DrawModel
{
    public function __construct(private PDO $pdo) {}

    /**
     * Numéros tirés pour une partie, ordonnés du plus récent au plus ancien.
     * Le premier élément est le dernier numéro saisi.
     *
     * @return int[]
     */
    public function getDrawnNumbers(int $partieId): array
    {
        $st = $this->pdo->prepare("SELECT num FROM drawn_numbers WHERE partie_id = ? ORDER BY drawn_at DESC, rowid DESC");
        $st->execute([$partieId]);
        return array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN));
    }

    /** Vérifie si un numéro est tiré dans une partie. */
    public function isDrawn(int $num, int $partieId): bool
    {
        $st = $this->pdo->prepare("SELECT 1 FROM drawn_numbers WHERE partie_id = ? AND num = ?");
        $st->execute([$partieId, $num]);
        return (bool) $st->fetchColumn();
    }

    /** Inverse l'état d'un numéro dans une partie (tiré ↔ non tiré). */
    public function toggle(int $num, int $partieId): void
    {
        $num = max(LOTO_MIN, min(LOTO_MAX, $num));
        if ($this->isDrawn($num, $partieId)) {
            $this->pdo->prepare("DELETE FROM drawn_numbers WHERE partie_id = ? AND num = ?")->execute([$partieId, $num]);
        } else {
            $this->pdo->prepare("INSERT OR IGNORE INTO drawn_numbers (partie_id, num) VALUES (?, ?)")->execute([$partieId, $num]);
        }
    }

    /** Remet tous les numéros d'une partie à « non tiré ». */
    public function resetAll(int $partieId): void
    {
        $this->pdo->prepare("DELETE FROM drawn_numbers WHERE partie_id = ?")->execute([$partieId]);
    }
}
