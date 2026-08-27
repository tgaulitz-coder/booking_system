<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/db.php";


requireLogin();


if (!isAdmin()) {

    echo json_encode([
        "error" => "Ingen behörighet"
    ]);

    exit;
}


header("Content-Type: application/json");



$sql = "

SELECT

    b.id,

    u.name,

    u.username,

    a.appointment_date,

    a.appointment_time


FROM bookings b


JOIN users u

ON b.user_id = u.id


JOIN appointments a

ON b.appointment_id = a.id


ORDER BY

    a.appointment_date,

    a.appointment_time

";



$stmt = $pdo->query($sql);



echo json_encode(
    $stmt->fetchAll()
);
