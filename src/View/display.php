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
<main class="screen-display h-screen w-screen max-w-full overflow-hidden flex flex-col">
    <header class="loto-header shrink-0 flex items-center px-3 py-1">
        <img src="assets/logo-ste-therese.png" alt="Logo Ste Thérèse" class="loto-header-logo">
        <h1 class="loto-header-title">Loto Ste Thérèse 2026</h1>
    </header>
    <div class="loto-grid flex-1 min-h-0 grid grid-cols-10 grid-rows-[repeat(9,minmax(0,1fr))] gap-1 md:gap-2 p-2 md:p-3">
        <?php for ($n = LOTO_MIN; $n <= LOTO_MAX; $n++): ?>
            <?php $isDrawn = isset($drawn[$n]); ?>
            <div
                id="cell-<?= $n ?>"
                class="loto-cell flex items-center justify-center rounded-md md:rounded-lg font-bold transition-colors min-h-0 min-w-0 <?= $isDrawn ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-300 shadow-sm' ?>"
                aria-label="Numéro <?= $n ?><?= $isDrawn ? ', tiré' : ', non tiré' ?>"
            >
                <span class="loto-num"><?= $n ?></span>
            </div>
        <?php endfor; ?>
    </div>
</main>
<script>
(function() {
    const DRAWN_CLS = 'bg-blue-600 text-white';
    const DEFAULT_CLS = 'bg-white text-slate-600 border border-slate-300 shadow-sm';

    async function refresh() {
        try {
            const res = await fetch('index.php?action=drawn_json');
            if (!res.ok) return;
            const drawnNums = new Set(await res.json());

            for (let n = <?= LOTO_MIN ?>; n <= <?= LOTO_MAX ?>; n++) {
                const cell = document.getElementById('cell-' + n);
                if (!cell) continue;
                const isDrawn = drawnNums.has(n);
                const wasDrawn = cell.classList.contains('bg-blue-600');
                if (isDrawn === wasDrawn) continue;

                if (isDrawn) {
                    DEFAULT_CLS.split(' ').forEach(c => cell.classList.remove(c));
                    DRAWN_CLS.split(' ').forEach(c => cell.classList.add(c));
                    cell.setAttribute('aria-label', 'Numéro ' + n + ', tiré');
                } else {
                    DRAWN_CLS.split(' ').forEach(c => cell.classList.remove(c));
                    DEFAULT_CLS.split(' ').forEach(c => cell.classList.add(c));
                    cell.setAttribute('aria-label', 'Numéro ' + n + ', non tiré');
                }
            }
        } catch (e) { /* silently retry next tick */ }
    }

    setInterval(refresh, 1000);
})();
</script>
<?php require __DIR__ . '/partials/footer.php';
