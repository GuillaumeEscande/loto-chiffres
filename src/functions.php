<?php

declare(strict_types=1);

/**
 * Fonctions utilitaires (échappement HTML, auth).
 */
function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

/** Vérifie si l'utilisateur est authentifié (secret admin). */
function is_authenticated(): bool
{
    return isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'] === true;
}

/** Redirige vers la page de connexion si non authentifié. */
function require_admin(): void
{
    if (!is_authenticated()) {
        header('Location: index.php?action=login');
        exit;
    }
}

/** Tronque une chaîne à la longueur max. */
function truncate_to(string $value, int $maxLength): string
{
    if ($maxLength <= 0) {
        return $value;
    }
    $enc = 'UTF-8';
    if (mb_strlen($value, $enc) <= $maxLength) {
        return $value;
    }
    return mb_substr($value, 0, $maxLength, $enc);
}
