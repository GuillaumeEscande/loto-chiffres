<?php
/**
 * Vue affichage : grille des numéros du loto (blanc = non tiré, bleu = tiré).
 * Auto-rafraîchissement toutes les secondes pour écran dédié (ex. Raspberry Pi).
 */
$pageTitle = 'Chiffres — Tirage Loto';
$drawn     = $drawn ?? [];
$lastDrawn = $lastDrawn ?? null;
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
            <?php
                if ($n === $lastDrawn)       $cellCls = 'bg-amber-400 text-slate-900';
                elseif ($isDrawn)            $cellCls = 'bg-blue-600 text-white';
                else                         $cellCls = 'bg-white text-slate-600 border border-slate-300 shadow-sm';
            ?>
            <div
                id="cell-<?= $n ?>"
                class="loto-cell flex items-center justify-center rounded-md md:rounded-lg font-bold transition-colors min-h-0 min-w-0 <?= $cellCls ?>"
                aria-label="Numéro <?= $n ?><?= $isDrawn ? ', tiré' : ', non tiré' ?>"
            >
                <span class="loto-num"><?= $n ?></span>
            </div>
        <?php endfor; ?>
    </div>
</main>
<script>
(function() {
    const CLS_DEFAULT = 'bg-white text-slate-600 border border-slate-300 shadow-sm';
    const CLS_DRAWN   = 'bg-blue-600 text-white';
    const CLS_LAST    = 'bg-amber-400 text-slate-900';

    function applyClass(cell, cls) {
        [CLS_DEFAULT, CLS_DRAWN, CLS_LAST].forEach(c => c.split(' ').forEach(k => cell.classList.remove(k)));
        cls.split(' ').forEach(k => cell.classList.add(k));
    }

    let prevLast = <?= $lastDrawn ?? 'null' ?>;

    async function refresh() {
        try {
            const res = await fetch('index.php?action=drawn_json');
            if (!res.ok) return;
            const { drawn, last } = await res.json();
            const drawnSet = new Set(drawn);

            for (let n = <?= LOTO_MIN ?>; n <= <?= LOTO_MAX ?>; n++) {
                const cell = document.getElementById('cell-' + n);
                if (!cell) continue;
                const isDrawn = drawnSet.has(n);
                const isLast  = n === last;
                const wasLast = n === prevLast;

                // Mise à jour uniquement si l'état change
                if (isLast && !cell.classList.contains('bg-amber-400')) {
                    applyClass(cell, CLS_LAST);
                    cell.setAttribute('aria-label', 'Numéro ' + n + ', dernier tiré');
                } else if (!isLast && wasLast) {
                    applyClass(cell, isDrawn ? CLS_DRAWN : CLS_DEFAULT);
                    cell.setAttribute('aria-label', 'Numéro ' + n + (isDrawn ? ', tiré' : ', non tiré'));
                } else if (!isLast) {
                    const shouldBeDrawn = cell.classList.contains('bg-blue-600');
                    if (isDrawn !== shouldBeDrawn) {
                        applyClass(cell, isDrawn ? CLS_DRAWN : CLS_DEFAULT);
                        cell.setAttribute('aria-label', 'Numéro ' + n + (isDrawn ? ', tiré' : ', non tiré'));
                    }
                }
            }
            prevLast = last;
        } catch (e) { /* silently retry next tick */ }
    }

    setInterval(refresh, 1000);
})();
</script>
<?php require __DIR__ . '/partials/footer.php';
