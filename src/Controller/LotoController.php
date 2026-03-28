<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\DrawModel;
use App\Model\PartyModel;

/**
 * Contrôleur : affichage écran (grille lecture seule) et admin (grille interactive).
 */
class LotoController
{
    public function __construct(
        private DrawModel  $drawModel,
        private PartyModel $partyModel
    ) {}

    /** Vue affichage : grille lecture seule, auto-rafraîchissement 1 s. */
    public function display(): void
    {
        $party = $this->partyModel->getActive();
        $drawn = $this->drawModel->getDrawnNumbers((int) $party['id']);
        $this->render('display', [
            'drawn'       => $drawn,
            'partyNumero' => (int) $party['numero'],
        ]);
    }

    /** Vue admin : grille cliquable + navigation entre parties. */
    public function admin(): void
    {
        $party     = $this->partyModel->getActive();
        $drawn     = $this->drawModel->getDrawnNumbers((int) $party['id']);
        $prevParty = $this->partyModel->getPrevious((int) $party['numero']);
        $nextParty = $this->partyModel->getNext((int) $party['numero']);
        $this->render('admin', [
            'drawn'     => $drawn,
            'party'     => $party,
            'prevParty' => $prevParty,
            'nextParty' => $nextParty,
        ]);
    }

    /** Toggle un numéro dans la partie active (POST). */
    public function toggle(): void
    {
        $num   = isset($_POST['num']) ? (int) $_POST['num'] : 0;
        $party = $this->partyModel->getActive();
        if ($num >= LOTO_MIN && $num <= LOTO_MAX) {
            $this->drawModel->toggle($num, (int) $party['id']);
        }
        $this->redirect('index.php?action=admin');
    }

    /** Remise à zéro des numéros de la partie active (POST). */
    public function reset(): void
    {
        $party = $this->partyModel->getActive();
        $this->drawModel->resetAll((int) $party['id']);
        $this->redirect('index.php?action=admin');
    }

    /** Passe à la partie précédente (POST). */
    public function prevParty(): void
    {
        $party = $this->partyModel->getActive();
        $prev  = $this->partyModel->getPrevious((int) $party['numero']);
        if ($prev) {
            $this->partyModel->setActive((int) $prev['id']);
        }
        $this->redirect('index.php?action=admin');
    }

    /** Passe à la partie suivante ou en crée une nouvelle (POST). */
    public function nextParty(): void
    {
        $party = $this->partyModel->getActive();
        $next  = $this->partyModel->getNext((int) $party['numero']);
        if (!$next) {
            $next = $this->partyModel->createNext();
        }
        $this->partyModel->setActive((int) $next['id']);
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
