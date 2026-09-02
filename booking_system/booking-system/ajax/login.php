<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/db.php";

header("Content-Type: application/json");

requireCsrf();

$data = json_decode(file_get_contents("php://input"), true);

$username = trim($data["username"] ?? "");
$password = $data["password"] ?? "";

if ($username === "" || $password === "") {

    echo json_encode([
        "success" => false,
        "message" => "Fyll i alla fält."
    ]);

    exit;
}

$sql = "SELECT * FROM users WHERE username = ?";

$stmt = $pdo->prepare($sql);

$stmt->execute([$username]);

$user = $stmt->fetch();

if (!$user) {

    echo json_encode([
        "success" => false,
        "message" => "Fel användarnamn eller lösenord."
    ]);

    exit;
}

if (!password_verify($password, $user["password"])) {

    echo json_encode([
        "success" => false,
        "message" => "Fel användarnamn eller lösenord."
    ]);

    exit;
}

unset($user["password"]);

// Byt sessions-ID vid inloggning (skyddar mot session fixation). Sessionens
// befintliga data, inklusive CSRF-token, följer med automatiskt.
session_regenerate_id(true);

$_SESSION["user"] = $user;

echo json_encode([
    "success" => true,
    "message" => "Inloggningen lyckades."
]);
