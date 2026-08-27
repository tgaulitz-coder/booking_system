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

    $stmt = $pdo->prepare("\n        SELECT id\n        FROM appointments\n        WHERE id = ?\n          AND TIMESTAMP(appointment_date, appointment_time) > NOW()\n        FOR UPDATE\n    ");
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

    $stmt = $pdo->prepare("\n        INSERT INTO bookings (user_id, appointment_id, message)\n        VALUES (?, ?, ?)\n    ");
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
