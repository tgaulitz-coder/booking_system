<?php
require "includes/auth.php";
?>

<!DOCTYPE html>

<html lang="sv">

<head>

    <meta charset="UTF-8">

    <title>Bokningssystem</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <h1>Bokningssystem</h1>

    <?php if (isLoggedIn()): ?>

        <p>Välkommen <?= htmlspecialchars($_SESSION["user"]["name"]) ?></p>

        <p>

            <a href="dashboard.php">Dashboard</a>

        </p>

    <?php else: ?>

        <p>

            <a href="login.php">Logga in</a>

        </p>

        <p>

            <a href="register.php">Registrera</a>

        </p>

    <?php endif; ?>

</body>

</html>