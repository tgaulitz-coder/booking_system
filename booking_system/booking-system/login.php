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

    <title>Logga in</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

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

    <script src="js/login.js"></script>

    <p>
        <a href="index.php">← Tillbaka till startsidan</a>
    </p>

    <p>
        Har du inget konto?
        <a href="register.php">Registrera dig här</a>
    </p>
</body>

</html>