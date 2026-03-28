<?php
/**
 * Vue admin : navigation entre parties + grille interactive + historique.
 */
$pageTitle  = 'Chiffres — Administration tirage';
$drawn      = $drawn ?? [];
$party      = $party      ?? ['id' => 1, 'numero' => 1, 'active' => 1];
$prevParty  = $prevParty  ?? null;
$nextParty  = $nextParty  ?? null;
$drawnSet   = array_flip($drawn);
$lastDrawn  = $drawn[0] ?? null;
$history    = array_slice($drawn, 0, 5);
$bodyClass  = 'admin-body';
require __DIR__ . '/partials/header.php';
?>
<div class="h-screen flex flex-col overflow-hidden">

    <!-- Header -->
    <header class="bg-indigo-600 text-white shadow shrink-0">
        <div class="w-full px-4 py-2 flex justify-between items-center">
            <h1 class="text-lg font-semibold">Administration — Partie <?= (int) $party['numero'] ?></h1>
            <div class="flex items-center gap-4">
                <a href="index.php" class="text-indigo-100 text-sm hover:text-white" target="_blank" rel="noopener">Voir l'écran</a>
                <a href="index.php?action=logout" class="text-indigo-100 text-sm hover:text-white whitespace-nowrap">Déconnexion</a>
            </div>
        </div>
    </header>

    <main class="flex-1 min-h-0 flex gap-2 p-2 overflow-hidden">

        <!-- Gauche : gestion des parties -->
        <aside class="w-36 shrink-0 flex flex-col gap-2 overflow-hidden">

            <!-- Partie active -->
            <div class="bg-indigo-100 rounded-xl p-3 text-center shrink-0">
                <p class="text-xs text-indigo-500 uppercase font-semibold tracking-wide leading-none mb-1">Partie active</p>
                <p class="text-4xl font-bold text-indigo-700 leading-none"><?= (int) $party['numero'] ?></p>
            </div>

            <!-- Partie précédente -->
            <?php if ($prevParty): ?>
                <form method="post" action="index.php" class="shrink-0">
                    <input type="hidden" name="action" value="prev_party">
                    <button type="submit" class="w-full py-2 px-3 rounded-lg bg-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-300 active:scale-[0.98] transition flex items-center justify-center gap-1">
                        ◀ Partie <?= (int) $prevParty['numero'] ?>
                    </button>
                </form>
            <?php else: ?>
                <div class="shrink-0 py-2 px-3 rounded-lg bg-slate-100 text-slate-400 text-xs text-center">
                    Pas de partie précédente
                </div>
            <?php endif; ?>

            <!-- Partie suivante ou nouvelle -->
            <form method="post" action="index.php" class="shrink-0">
                <input type="hidden" name="action" value="next_party">
                <button type="submit" class="w-full py-2 px-3 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 active:scale-[0.98] transition flex items-center justify-center gap-1">
                    <?php if ($nextParty): ?>
                        Partie <?= (int) $nextParty['numero'] ?> ▶
                    <?php else: ?>
                        + Nouvelle partie
                    <?php endif; ?>
                </button>
            </form>

            <!-- Spacer -->
            <div class="flex-1 min-h-0"></div>

            <!-- Réinitialiser la partie active -->
            <form method="post" action="index.php" class="shrink-0"
                  onsubmit="return confirm('Réinitialiser tous les numéros de la partie <?= (int) $party['numero'] ?> ?');">
                <input type="hidden" name="action" value="reset">
                <button type="submit" class="w-full py-2 px-3 rounded-lg bg-red-100 text-red-700 text-xs font-semibold hover:bg-red-200 active:scale-[0.98] transition">
                    Réinitialiser partie <?= (int) $party['numero'] ?>
                </button>
            </form>

        </aside>

        <!-- Centre : grille des numéros -->
        <div class="flex-1 min-w-0 min-h-0 flex flex-col">
            <div class="admin-grid flex-1 min-h-0 grid grid-cols-10 grid-rows-[repeat(9,minmax(0,1fr))] gap-1">
                <?php for ($n = LOTO_MIN; $n <= LOTO_MAX; $n++): ?>
                    <?php
                        $isDrawn = isset($drawnSet[$n]);
                        if ($n === $lastDrawn)  $btnCls = 'bg-amber-400 text-slate-900 border-amber-500 hover:bg-amber-300';
                        elseif ($isDrawn)       $btnCls = 'bg-blue-600 text-white border-blue-700 hover:bg-blue-700';
                        else                    $btnCls = 'bg-white text-slate-600 border-slate-300 shadow-sm hover:border-indigo-400 hover:bg-indigo-50';
                    ?>
                    <form method="post" action="index.php" class="contents">
                        <input type="hidden" name="action" value="toggle">
                        <input type="hidden" name="num" value="<?= $n ?>">
                        <button
                            type="submit"
                            class="loto-cell flex items-center justify-center rounded-md font-bold border transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500 <?= $btnCls ?>"
                            aria-label="Numéro <?= $n ?>, <?= $isDrawn ? 'tiré' : 'non tiré' ?>"
                        ><span><?= $n ?></span></button>
                    </form>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Droite : historique des 5 derniers -->
        <aside class="shrink-0 w-24 flex flex-col gap-1 overflow-hidden">
            <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide shrink-0">Derniers</h2>
            <?php if (empty($history)): ?>
                <p class="text-slate-400 text-xs italic">Aucun</p>
            <?php else: ?>
                <ol class="flex-1 min-h-0 grid gap-1" style="grid-template-rows: repeat(<?= count($history) ?>, minmax(0,1fr))">
                    <?php foreach ($history as $i => $num): ?>
                        <li class="flex items-center gap-2 min-h-0">
                            <div class="admin-history-badge flex-1 min-h-0 aspect-square flex items-center justify-center rounded-xl font-bold shadow <?= $i === 0 ? 'bg-amber-400 text-slate-900' : 'bg-blue-600 text-white' ?>">
                                <span class="num"><?= $num ?></span>
                            </div>
                            <?php if ($i === 0): ?>
                                <span class="text-xs text-amber-600 font-semibold leading-tight">Der.</span>
                            <?php else: ?>
                                <span class="text-xs text-slate-400 leading-tight">#<?= $i + 1 ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </aside>

    </main>
</div>
<?php require __DIR__ . '/partials/footer.php';
