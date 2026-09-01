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
$id = (int)($data["id"] ?? 0);
$information = trim($data["information"] ?? "");

if ($id <= 0) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Ogiltig tid."], JSON_UNESCAPED_UNICODE);
    exit;
}

if (mb_strlen($information) > 500) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Informationen får vara max 500 tecken."], JSON_UNESCAPED_UNICODE);
    exit;
}

// Hämta tidens nuvarande status (samma logik som adminGetAppointments.php)
// för att avgöra vad som får ändras.
$stmt = $pdo->prepare("
    SELECT
        a.id,
        a.appointment_date,
        a.appointment_time,
        b.id AS booking_id,
        CASE
            WHEN TIMESTAMP(a.appointment_date, a.appointment_time) < NOW() THEN
                CASE WHEN b.id IS NULL THEN 'Passerad' ELSE 'Passerad bokad' END
            WHEN b.id IS NULL THEN 'Ledig'
            ELSE 'Bokad'
        END AS status
    FROM appointments a
    LEFT JOIN bookings b ON a.id = b.appointment_id
    WHERE a.id = ?
");
$stmt->execute([$id]);
$current = $stmt->fetch();

if (!$current) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Tiden hittades inte."], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($current["status"] === "Passerad" || $current["status"] === "Passerad bokad") {
    http_response_code(409);
    echo json_encode(["success" => false, "message" => "En passerad tid kan inte redigeras."], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($current["status"] === "Bokad") {
    // En bokad tid: bara informationstexten får ändras. Datum/tid ligger fast
    // eftersom en användare redan bokat den specifika tiden.
    try {
        $stmt = $pdo->prepare("UPDATE appointments SET information = ? WHERE id = ?");
        $stmt->execute([$information !== "" ? $information : null, $id]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Tiden kunde inte uppdateras."], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode([
        "success" => true,
        "message" => "Informationen uppdaterades.",
        "appointment" => [
            "id" => $id,
            "appointment_date" => $current["appointment_date"],
            "appointment_time" => $current["appointment_time"],
            "information" => $information,
            "booking_id" => (int)$current["booking_id"],
            "status" => "Bokad"
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Status är "Ledig" - datum, tid och information får alla ändras.
$date = trim($data["date"] ?? $current["appointment_date"]);
$time = trim($data["time"] ?? $current["appointment_time"]);

if ($date === "" || $time === "") {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Datum och tid krävs."], JSON_UNESCAPED_UNICODE);
    exit;
}

$appointmentTimestamp = strtotime($date . " " . $time);
if ($appointmentTimestamp === false || $appointmentTimestamp <= time()) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Du kan inte sätta en tid som redan passerat."], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = $pdo->prepare("
    SELECT 1 FROM appointments
    WHERE appointment_date = ? AND appointment_time = ? AND id != ?
");
$stmt->execute([$date, $time, $id]);
if ($stmt->fetchColumn()) {
    http_response_code(409);
    echo json_encode(["success" => false, "message" => "Det finns redan en tid vid det valda datumet och klockslaget."], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE appointments
        SET appointment_date = ?, appointment_time = ?, information = ?
        WHERE id = ?
    ");
    $stmt->execute([$date, $time, $information !== "" ? $information : null, $id]);
} catch (PDOException $e) {
    $mysqlError = $e->errorInfo[1] ?? null;
    if ($mysqlError === 1062) {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Det finns redan en tid vid det valda datumet och klockslaget."], JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Tiden kunde inte uppdateras."], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    "success" => true,
    "message" => "Tiden uppdaterades.",
    "appointment" => [
        "id" => $id,
        "appointment_date" => $date,
        "appointment_time" => strlen($time) === 5 ? $time . ":00" : $time,
        "information" => $information,
        "booking_id" => null,
        "status" => "Ledig"
    ]
], JSON_UNESCAPED_UNICODE);
