<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/db.php";

requireLogin();
header("Content-Type: application/json; charset=utf-8");

$userId = (int)$_SESSION["user"]["id"];

$sql = "
    SELECT
        b.id AS booking_id,
        a.appointment_date,
        a.appointment_time,
        a.information,
        b.message
    FROM bookings b
    JOIN appointments a ON b.appointment_id = a.id
    WHERE b.user_id = ?
    ORDER BY a.appointment_date, a.appointment_time
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);

echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE);
