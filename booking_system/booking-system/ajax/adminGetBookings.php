<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/db.php";

requireLogin();
header("Content-Type: application/json; charset=utf-8");

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(["message" => "Ingen behörighet."], JSON_UNESCAPED_UNICODE);
    exit;
}

$sql = "
    SELECT
        b.id AS booking_id,
        u.name,
        u.username,
        u.email,
        a.appointment_date,
        a.appointment_time,
        a.information,
        b.message
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN appointments a ON b.appointment_id = a.id
    ORDER BY a.appointment_date, a.appointment_time
";

$stmt = $pdo->query($sql);
echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE);
