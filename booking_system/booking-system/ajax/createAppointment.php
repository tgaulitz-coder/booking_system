<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/db.php";

requireLogin();
header("Content-Type: application/json; charset=utf-8");

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Ingen behörighet."], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true) ?? [];
$date = trim($data["date"] ?? "");
$time = trim($data["time"] ?? "");
$information = trim($data["information"] ?? "");

if ($date === "" || $time === "") {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Datum och tid krävs."], JSON_UNESCAPED_UNICODE);
    exit;
}

// Samma gräns som maxlength på textarean i UI:t. Kollas även här eftersom
// UI:ts maxlength går att kringgå med ett direkt anrop till ajax-endpointen.
if (mb_strlen($information) > 500) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Informationen får vara max 500 tecken."], JSON_UNESCAPED_UNICODE);
    exit;
}

$appointmentTimestamp = strtotime($date . " " . $time);
if ($appointmentTimestamp === false || $appointmentTimestamp <= time()) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Du kan inte skapa en tid som passerat."], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO appointments (appointment_date, appointment_time, information)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$date, $time, $information !== "" ? $information : null]);

    echo json_encode([
        "success" => true,
        "message" => "Tiden skapades.",
        "appointment" => [
            "id" => (int)$pdo->lastInsertId(),
            "appointment_date" => $date,
            "appointment_time" => strlen($time) === 5 ? $time . ":00" : $time,
            "information" => $information,
            "booking_id" => null,
            "status" => "Ledig"
        ]
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Tiden kunde inte skapas."], JSON_UNESCAPED_UNICODE);
}
