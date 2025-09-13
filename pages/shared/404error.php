<?php
require_once __DIR__ . "/../_base.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - Superme Malaysia</title>
    <link href="https://fonts.googleapis.com/css?family=Hammersmith+One" rel="stylesheet">
    <link rel="icon" type="image/png" href="/src/img/favicon/favicon.png">
    <link rel="stylesheet" href="/src/css/main.css">
    <link rel="stylesheet" href="/src/css/<?= htmlspecialchars($navigate_to); ?>.css">
</head>

<body>
    <div id="error-container">
        <h1 id="error-404">404</h1>
        <h1 id="error-message">NOT FOUND</h1>
        <h2 id="error-description">It looks like something is missing!</h2>
        <a href="/" id="error-back" class="link-button">Back to Homepage</a>
    </div>
</body>

</html>