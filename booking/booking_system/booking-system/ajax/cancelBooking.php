<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/db.php";

requireLogin();
requireCsrf();
header("Content-Type: application/json; charset=utf-8");

$rawBody = file_get_contents("php://input");
$data = json_decode($rawBody, true);

// Tillåt både JSON och vanlig POST-data.
if (!is_array($data)) {
    $data = $_POST;
}

$bookingId = filter_var(
    $data["booking_id"] ?? $data["bookingId"] ?? $data["id"] ?? null,
    FILTER_VALIDATE_INT
);
$userId = (int)$_SESSION["user"]["id"];

if ($bookingId === false || $bookingId === null || $bookingId <= 0) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Ogiltig bokning. Boknings-ID saknas."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->execute([$bookingId, $userId]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Bokningen finns inte eller tillhör inte ditt konto."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode([
        "success" => true,
        "message" => "Bokningen är avbokad."
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Ett fel uppstod när bokningen skulle avbokas."
    ], JSON_UNESCAPED_UNICODE);
}
