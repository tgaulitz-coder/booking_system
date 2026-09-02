<?php
require "includes/auth.php";
?>

<!DOCTYPE html>

<html lang="sv">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

    <title>Bokningssystem</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <main>

        <h1>Bokningssystem</h1>

        <?php if (isLoggedIn()): ?>

            <p>Välkommen <?= htmlspecialchars($_SESSION["user"]["name"]) ?></p>

            <div class="btnGroup">

                <a class="btn" href="dashboard.php">Dashboard</a>

            </div>

        <?php else: ?>

            <div class="btnGroup">

                <a class="btn" href="login.php">Logga in</a>

                <a class="btn btn-secondary" href="register.php">Registrera</a>

            </div>

        <?php endif; ?>

    </main>

</body>

</html>