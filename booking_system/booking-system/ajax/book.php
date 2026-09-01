<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/db.php";

requireLogin();
header("Content-Type: application/json; charset=utf-8");

$data = json_decode(file_get_contents("php://input"), true) ?? [];
$userId = (int)$_SESSION["user"]["id"];
$appointmentId = (int)($data["appointmentId"] ?? 0);
$message = trim($data["message"] ?? "");

if ($appointmentId <= 0) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Ogiltig tid."], JSON_UNESCAPED_UNICODE);
    exit;
}

// Samma gräns som maxlength på textarean i UI:t. Kollas även här eftersom
// UI:ts maxlength går att kringgå med ett direkt anrop till ajax-endpointen.
if (mb_strlen($message) > 500) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Meddelandet får vara max 500 tecken."], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT id FROM bookings WHERE user_id = ? FOR UPDATE");
    $stmt->execute([$userId]);
    if ($stmt->fetch()) {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Du har redan en bokad tid."], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT id
        FROM appointments
        WHERE id = ?
          AND TIMESTAMP(appointment_date, appointment_time) > NOW()
        FOR UPDATE
    ");
    $stmt->execute([$appointmentId]);
    if (!$stmt->fetch()) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Tiden finns inte längre."], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM bookings WHERE appointment_id = ? FOR UPDATE");
    $stmt->execute([$appointmentId]);
    if ($stmt->fetch()) {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Tiden är redan bokad."], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO bookings (user_id, appointment_id, message)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$userId, $appointmentId, $message !== "" ? $message : null]);

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Bokningen lyckades.",
        "bookingId" => (int)$pdo->lastInsertId()
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $mysqlError = $e->errorInfo[1] ?? null;
    if ($mysqlError === 1062) {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Tiden är redan bokad eller så har du redan en bokning."], JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Bokningen kunde inte sparas."], JSON_UNESCAPED_UNICODE);
}
