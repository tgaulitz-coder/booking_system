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

$availableStmt = $pdo->query("
    SELECT COUNT(*)
    FROM appointments a
    LEFT JOIN bookings b ON a.id = b.appointment_id
    WHERE b.id IS NULL
      AND TIMESTAMP(a.appointment_date, a.appointment_time) > NOW()
");
$availableAppointments = (int)$availableStmt->fetchColumn();

$bookingsStmt = $pdo->query("SELECT COUNT(*) FROM bookings");
$totalBookings = (int)$bookingsStmt->fetchColumn();

echo json_encode([
    "availableAppointments" => $availableAppointments,
    "totalBookings" => $totalBookings
], JSON_UNESCAPED_UNICODE);
