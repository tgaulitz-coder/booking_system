<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/db.php";

requireLogin();
header("Content-Type: application/json; charset=utf-8");

$sql = "
    SELECT
        a.id,
        a.appointment_date,
        a.appointment_time,
        a.information
    FROM appointments a
    LEFT JOIN bookings b ON a.id = b.appointment_id
    WHERE b.id IS NULL
      AND TIMESTAMP(a.appointment_date, a.appointment_time) > NOW()
    ORDER BY a.appointment_date, a.appointment_time
";

$stmt = $pdo->query($sql);
echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE);
