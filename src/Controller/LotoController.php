<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\DrawModel;

/**
 * Contrôleur : affichage écran (grille lecture seule) et admin (grille interactive).
 */
class LotoController
{
    public function __construct(private DrawModel $drawModel) {}

    /** Vue affichage : grille lecture seule, auto-rafraîchissement 1 s. */
    public function display(): void
    {
        $drawn = $this->drawModel->getDrawnNumbers();
        $lastDrawn = $this->drawModel->getLastDrawnNumber();
        $this->render('display', [
            'drawn'     => $drawn,
            'lastDrawn' => $lastDrawn,
        ]);
    }

    /** Vue admin : grille cliquable + bouton réinitialiser. */
    public function admin(): void
    {
        $drawn = $this->drawModel->getDrawnNumbers();
        $this->render('admin', [
            'drawn' => $drawn,
        ]);
    }

    /** Toggle un numéro (POST). */
    public function toggle(): void
    {
        $num = isset($_POST['num']) ? (int) $_POST['num'] : 0;
        if ($num >= LOTO_MIN && $num <= LOTO_MAX) {
            $this->drawModel->toggle($num);
        }
        $this->redirect('index.php?action=admin');
    }

    /** Remise à zéro de tous les numéros (POST). */
    public function reset(): void
    {
        $this->drawModel->resetAll();
        $this->redirect('index.php?action=admin');
    }

    private function render(string $view, array $data): void
    {
        extract($data, EXTR_SKIP);
        $viewPath = dirname(__DIR__, 2) . '/src/View/' . $view . '.php';
        if (!is_file($viewPath)) {
            throw new \RuntimeException("Vue introuvable : {$view}");
        }
        require $viewPath;
    }

    private function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}
