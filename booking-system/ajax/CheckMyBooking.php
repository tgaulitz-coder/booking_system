<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/db.php";

requireLogin();

header("Content-Type: application/json");


$userId = $_SESSION["user"]["id"];


$stmt = $pdo->prepare("

    SELECT
        b.id,
        a.appointment_date,
        a.appointment_time

    FROM bookings b

    JOIN appointments a

    ON b.appointment_id = a.id

    WHERE b.user_id = ?

");


$stmt->execute([$userId]);


$booking = $stmt->fetch();


if ($booking) {

    echo json_encode([

        "hasBooking" => true,

        "booking" => $booking

    ]);
} else {

    echo json_encode([

        "hasBooking" => false

    ]);
}
