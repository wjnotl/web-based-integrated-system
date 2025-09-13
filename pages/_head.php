<?php
require_once __DIR__ . "/_base.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? "Superme Malaysia"); ?></title>
    <link rel="icon" type="image/png" href="/src/img/favicon/favicon.png">
    <link rel="stylesheet" href="/src/css/main.css">
    <?php if (file_exists("src/css/$navigate_to.css")) : ?>
        <link rel='stylesheet' href='/src/css/<?= htmlspecialchars($navigate_to) ?>.css'>
    <?php endif; ?>

    <script src="/src/js/jquery.min.js"></script>
    <?php if ($request == "admin-home" && $role == "admin") : ?>
        <script src="/src/js/chart.min.js"></script>
    <?php endif; ?>
    <script type="module" src="/src/js/main.js"></script>
    <?php if (file_exists("src/js/$navigate_to.js")) : ?>
        <script type='module' src='/src/js/<?= htmlspecialchars($navigate_to) ?>.js'></script>
    <?php endif; ?>
</head>

<body>
    <?php
    if ($role === "customer") {
        require "_header.php";
    } else {
        require "_header-admin.php";
    }
    ?>
    <main>