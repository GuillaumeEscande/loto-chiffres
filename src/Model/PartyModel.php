<?php

declare(strict_types=1);

namespace App\Model;

use PDO;

/**
 * Modèle : gestion des parties de loto.
 * Une seule partie est "active" à la fois — c'est celle affichée sur l'écran public.
 */
class PartyModel
{
    public function __construct(private PDO $pdo) {}

    /** Retourne la partie active (toujours une). */
    public function getActive(): array
    {
        $row = $this->pdo->query("SELECT * FROM parties WHERE active = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            throw new \RuntimeException('Aucune partie active en base.');
        }
        return $row;
    }

    /** Partie précédant $currentNumero, ou null si inexistante. */
    public function getPrevious(int $currentNumero): ?array
    {
        $st = $this->pdo->prepare("SELECT * FROM parties WHERE numero < ? ORDER BY numero DESC LIMIT 1");
        $st->execute([$currentNumero]);
        return $st->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /** Partie suivant $currentNumero, ou null si inexistante. */
    public function getNext(int $currentNumero): ?array
    {
        $st = $this->pdo->prepare("SELECT * FROM parties WHERE numero > ? ORDER BY numero ASC LIMIT 1");
        $st->execute([$currentNumero]);
        return $st->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /** Rend la partie $id active (désactive toutes les autres). */
    public function setActive(int $id): void
    {
        $this->pdo->exec("UPDATE parties SET active = 0");
        $this->pdo->prepare("UPDATE parties SET active = 1 WHERE id = ?")->execute([$id]);
    }

    /** Crée la prochaine partie (numéro = max + 1) et la retourne. */
    public function createNext(): array
    {
        $maxNumero = (int) $this->pdo->query("SELECT COALESCE(MAX(numero), 0) FROM parties")->fetchColumn();
        $newNumero = $maxNumero + 1;
        $this->pdo->prepare("INSERT INTO parties (numero, active) VALUES (?, 0)")->execute([$newNumero]);
        return ['id' => (int) $this->pdo->lastInsertId(), 'numero' => $newNumero, 'active' => 0];
    }
}
