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
$bookingId = (int)($data["bookingId"] ?? 0);

if ($bookingId <= 0) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Ogiltig bokning."], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
$stmt->execute([$bookingId]);

if ($stmt->rowCount() === 0) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Bokningen finns inte längre."], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(["success" => true, "message" => "Bokningen är avbokad."], JSON_UNESCAPED_UNICODE);
