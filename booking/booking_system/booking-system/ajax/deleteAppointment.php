<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/db.php";

requireLogin();
requireCsrf();
header("Content-Type: application/json; charset=utf-8");

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Ingen behörighet."], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true) ?? [];
$id = (int)($data["id"] ?? 0);

if ($id <= 0) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Ogiltig tid."], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = $pdo->prepare("SELECT 1 FROM bookings WHERE appointment_id = ?");
$stmt->execute([$id]);

if ($stmt->fetchColumn()) {
    http_response_code(409);
    echo json_encode(["success" => false, "message" => "Tiden är bokad och kan inte tas bort."], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ?");
$stmt->execute([$id]);

if ($stmt->rowCount() === 0) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Tiden finns inte längre."], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(["success" => true, "message" => "Tiden togs bort."], JSON_UNESCAPED_UNICODE);
