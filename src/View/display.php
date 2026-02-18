<?php
/**
 * Vue affichage : grille des numéros du loto (blanc = non tiré, bleu = tiré).
 * Auto-rafraîchissement toutes les secondes pour écran dédié (ex. Raspberry Pi).
 */
$pageTitle = 'Chiffres — Tirage Loto';
$drawn = $drawn ?? [];
$bodyClass = 'screen-display-body';
require __DIR__ . '/partials/header.php';
?>
<meta http-equiv="refresh" content="1">
<main class="screen-display h-screen w-screen max-w-full overflow-hidden flex flex-col p-2 md:p-3">
    <h1 class="sr-only">Tirage du Loto</h1>
    <div class="loto-grid flex-1 min-h-0 grid grid-cols-10 grid-rows-[repeat(9,minmax(0,1fr))] gap-1 md:gap-2">
        <?php for ($n = LOTO_MIN; $n <= LOTO_MAX; $n++): ?>
            <?php $isDrawn = isset($drawn[$n]); ?>
            <div
                class="loto-cell flex items-center justify-center rounded-md md:rounded-lg font-bold transition-colors min-h-0 min-w-0 <?= $isDrawn ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-300 shadow-sm' ?>"
                aria-label="Numéro <?= $n ?><?= $isDrawn ? ', tiré' : ', non tiré' ?>"
            >
                <span class="loto-num"><?= $n ?></span>
            </div>
        <?php endfor; ?>
    </div>
    <p class="screen-display-refresh text-slate-400 text-[10px] md:text-xs text-right py-0.5 shrink-0">Rafraîchissement 1 s</p>
</main>
<?php require __DIR__ . '/partials/footer.php';
