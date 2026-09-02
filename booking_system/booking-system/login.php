<?php
require "includes/auth.php";

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="sv">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

    <title>Logga in</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <main>

        <h1>Logga in</h1>

        <form id="loginForm">

            <input
                type="text"
                id="username"
                placeholder="Användarnamn"
                required>

            <input
                type="password"
                id="password"
                placeholder="Lösenord"
                required>

            <button type="submit">
                Logga in
            </button>

        </form>

        <p id="message"></p>

        <script src="js/utils.js"></script>
        <script src="js/login.js"></script>

        <div class="btnGroup">
            <a class="btn btn-secondary" href="index.php">← Tillbaka till startsidan</a>
        </div>

        <p>
            Har du inget konto?
            <a class="textLink" href="register.php">Registrera dig här</a>
        </p>

    </main>

</body>

</html>