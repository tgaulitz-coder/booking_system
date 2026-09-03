<?php
require "includes/auth.php";
?>

<!DOCTYPE html>
<html lang="sv">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

    <title>Registrera konto</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <main>

        <h1>Skapa konto</h1>
        <!-- Maxlength sätts för att undvika onödigt låna strängar. Detta kontrolleras även senare på serversidan. -->
        <form id="registerForm">

            <input
                type="text"
                id="name"
                placeholder="Namn"
                maxlength="100"
                required>

            <input
                type="text"
                id="username"
                placeholder="Användarnamn"
                maxlength="50"
                required>

            <input
                type="email"
                id="email"
                placeholder="E-post"
                maxlength="190"
                required>

            <input
                type="password"
                id="password"
                placeholder="Lösenord"
                required>

            <button type="submit">
                Registrera
            </button>

        </form>


        <p id="message"></p>

        <div class="btnGroup">
            <a class="btn btn-secondary" href="index.php">← Tillbaka till startsidan</a>
        </div>

        <script src="js/utils.js"></script>
        <script src="js/register.js"></script>

    </main>

</body>

</html>