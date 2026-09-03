<?php
//Hanterar sessioner, behörighetskontroller och CSRF skydd.

//Start sessionen om den inte redan är igång
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
//Kontrollerar om användaren är inloggad.
function isLoggedIn(): bool
{
    return isset($_SESSION["user"]);
}
//Kontrollerar om användaren är inloggad med admin behörighet.
function isAdmin(): bool
{
    return isset($_SESSION["user"])
        && $_SESSION["user"]["role"] === "admin";
}
//Skyddar sidor som kräver inloggning
function requireLogin()
{
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

// Används bara av sidor i admin/-mappen, därför den relativa sökvägen uppåt.
function requireAdmin()
{
    if (!isAdmin()) {
        header("Location: ../dashboard.php");
        exit;
    }
}

// Genererar (första gången) eller returnerar den befintliga CSRF-token som
// är kopplad till sessionen. Token skrivs ut i en <meta> tagg på varje sida
// och skickas sedan tillbaka som header vid alla ändrande AJAX-anrop.
function csrfToken(): string
{
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }

    return $_SESSION["csrf_token"];
}

// Avbryter requesten med ett 403-svar om CSRF token saknas eller inte
// stämmer överens med den som är sparad i sessionen. Anropas i början av
// alla ajax-endpoints som ändrar data (skapa/redigera/ta bort/boka/avboka).
function requireCsrf(): void
{
    $sent = $_SERVER["HTTP_X_CSRF_TOKEN"] ?? "";
    $valid = isset($_SESSION["csrf_token"]) && hash_equals($_SESSION["csrf_token"], $sent);

    if (!$valid) {
        http_response_code(403);
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode([
            "success" => false,
            "message" => "Sessionen har gått ut eller sidan är för gammal. Ladda om sidan och försök igen."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
