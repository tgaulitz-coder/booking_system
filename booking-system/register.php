<!DOCTYPE html>
<html lang="sv">

<head>
    <meta charset="UTF-8">
    <title>Registrera konto</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <h1>Skapa konto</h1>

    <form id="registerForm">

        <input
            type="text"
            id="name"
            placeholder="Namn"
            required>

        <input
            type="text"
            id="username"
            placeholder="Användarnamn"
            required>

        <input
            type="email"
            id="email"
            placeholder="E-post"
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

    <p>
        <a href="index.php">← Tillbaka till startsidan</a>
    </p>

    <script src="js/register.js"></script>

</body>

</html>