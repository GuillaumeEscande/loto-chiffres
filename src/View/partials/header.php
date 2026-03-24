<?php
$pageTitle = $pageTitle ?? 'Chiffres — Loto';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?></title>
    <script src="assets/tailwind.js"></script>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="<?= isset($bodyClass) ? h($bodyClass) : 'bg-slate-100 min-h-screen' ?>">
