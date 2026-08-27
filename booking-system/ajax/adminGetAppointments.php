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
        a.id,
        a.appointment_date,
        a.appointment_time,
        a.information,
        b.id AS booking_id,
        CASE
            WHEN TIMESTAMP(a.appointment_date, a.appointment_time) < NOW() THEN
                CASE WHEN b.id IS NULL THEN 'Passerad' ELSE 'Passerad bokad' END
            WHEN b.id IS NULL THEN 'Ledig'
            ELSE 'Bokad'
        END AS status
    FROM appointments a
    LEFT JOIN bookings b ON a.id = b.appointment_id
    ORDER BY a.appointment_date, a.appointment_time
";

$stmt = $pdo->query($sql);
echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE);
