<!DOCTYPE html>
<html lang="sv">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registrera konto</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <main>

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

        <div class="btnGroup">
            <a class="btn btn-secondary" href="index.php">← Tillbaka till startsidan</a>
        </div>

        <script src="js/register.js"></script>

    </main>

</body>

</html>