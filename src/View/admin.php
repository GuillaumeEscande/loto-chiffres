<?php
/**
 * Vue admin : même grille, cliquable pour changer l'état d'un numéro + bouton Réinitialiser.
 */
$pageTitle = 'Chiffres — Administration tirage';
$drawn = $drawn ?? [];
require __DIR__ . '/partials/header.php';
?>
<header class="bg-indigo-600 text-white shadow">
    <div class="max-w-4xl mx-auto px-4 py-4 flex flex-wrap justify-between items-center gap-2">
        <h1 class="text-xl font-semibold">Chiffres — Administration</h1>
        <div class="flex items-center gap-3">
            <a href="index.php" class="text-indigo-100 text-sm hover:text-white" target="_blank" rel="noopener">Voir l’écran</a>
            <a href="index.php?action=logout" class="text-indigo-100 text-sm hover:text-white whitespace-nowrap">Déconnexion</a>
        </div>
    </div>
</header>

<main class="max-w-4xl mx-auto px-4 py-6">
    <form method="post" action="index.php" class="mb-6" onsubmit="return confirm('Remettre tous les numéros à « non tiré » ?');">
        <input type="hidden" name="action" value="reset">
        <button type="submit" class="w-full py-3 px-4 rounded-xl bg-slate-600 text-white font-medium shadow hover:bg-slate-700 active:scale-[0.98] transition">
            Réinitialiser (tous les numéros non tirés)
        </button>
    </form>

    <p class="text-slate-600 text-sm mb-4">Cliquez sur un numéro pour le marquer comme tiré ou non tiré.</p>

    <div class="grid grid-cols-10 gap-1.5 sm:gap-2 md:gap-3">
        <?php for ($n = LOTO_MIN; $n <= LOTO_MAX; $n++): ?>
            <?php $isDrawn = isset($drawn[$n]); ?>
            <form method="post" action="index.php" class="contents">
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="num" value="<?= $n ?>">
                <button
                    type="submit"
                    class="loto-cell aspect-square flex items-center justify-center rounded-lg text-lg sm:text-xl md:text-2xl font-bold border transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 <?= $isDrawn ? 'bg-blue-600 text-white border-blue-700 hover:bg-blue-700' : 'bg-white text-slate-600 border-slate-300 shadow-sm hover:border-indigo-400 hover:bg-indigo-50' ?>"
                    aria-label="Numéro <?= $n ?>, <?= $isDrawn ? 'tiré (cliquer pour retirer)' : 'non tiré (cliquer pour marquer tiré)' ?>"
                >
                    <?= $n ?>
                </button>
            </form>
        <?php endfor; ?>
    </div>
</main>
<?php require __DIR__ . '/partials/footer.php';
