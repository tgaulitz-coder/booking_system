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

$startDate = trim($data["startDate"] ?? "");
$endDate = trim($data["endDate"] ?? $startDate);
$startTime = trim($data["startTime"] ?? "");
$endTime = trim($data["endTime"] ?? "");
$intervalMinutes = (int)($data["intervalMinutes"] ?? 0);
$skipWeekends = !empty($data["skipWeekends"]);
$information = trim($data["information"] ?? "");
$breakStartTime = trim($data["breakStartTime"] ?? "");
$breakEndTime = trim($data["breakEndTime"] ?? "");

if ($startDate === "" || $endDate === "" || $startTime === "" || $endTime === "") {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Datum och tider krävs."], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($intervalMinutes < 5 || $intervalMinutes > 480) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Intervallet måste vara mellan 5 och 480 minuter."], JSON_UNESCAPED_UNICODE);
    exit;
}

if (mb_strlen($information) > 500) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Informationen får vara max 500 tecken."], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $rangeStart = new DateTime($startDate);
    $rangeEnd = new DateTime($endDate);
} catch (Exception $e) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Ogiltigt datum."], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($rangeEnd < $rangeStart) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Slutdatumet kan inte ligga före startdatumet."], JSON_UNESCAPED_UNICODE);
    exit;
}

// Begränsa till max 31 dagar per körning så man inte råkar skapa
// tusentals rader av misstag.
$dayDiff = (int)$rangeStart->diff($rangeEnd)->format("%a");
if ($dayDiff > 31) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Datumintervallet får vara max 31 dagar."], JSON_UNESCAPED_UNICODE);
    exit;
}

[$startHour, $startMinute] = array_map("intval", explode(":", $startTime));
[$endHour, $endMinute] = array_map("intval", explode(":", $endTime));
$startMinutesOfDay = $startHour * 60 + $startMinute;
$endMinutesOfDay = $endHour * 60 + $endMinute;

if ($endMinutesOfDay <= $startMinutesOfDay) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Sluttiden måste vara efter starttiden."], JSON_UNESCAPED_UNICODE);
    exit;
}

// Paus-period (t.ex. lunch) är valfri - inga tider skapas som börjar inom detta intervall.
$breakStartMinutesOfDay = null;
$breakEndMinutesOfDay = null;

if ($breakStartTime !== "" || $breakEndTime !== "") {
    if ($breakStartTime === "" || $breakEndTime === "") {
        http_response_code(422);
        echo json_encode(["success" => false, "message" => "Både start- och sluttid för pausen krävs."], JSON_UNESCAPED_UNICODE);
        exit;
    }

    [$breakStartHour, $breakStartMinute] = array_map("intval", explode(":", $breakStartTime));
    [$breakEndHour, $breakEndMinute] = array_map("intval", explode(":", $breakEndTime));
    $breakStartMinutesOfDay = $breakStartHour * 60 + $breakStartMinute;
    $breakEndMinutesOfDay = $breakEndHour * 60 + $breakEndMinute;

    if ($breakEndMinutesOfDay <= $breakStartMinutesOfDay) {
        http_response_code(422);
        echo json_encode(["success" => false, "message" => "Pausens sluttid måste vara efter dess starttid."], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Bygg listan med kandidat-tider (datum + tid) utifrån intervallet.
$slots = [];
$date = clone $rangeStart;

while ($date <= $rangeEnd) {
    $weekday = (int)$date->format("N"); // 1 = måndag ... 7 = söndag

    if (!$skipWeekends || $weekday < 6) {
        for ($minutes = $startMinutesOfDay; $minutes < $endMinutesOfDay; $minutes += $intervalMinutes) {
            // Hoppa över tider som börjar inom paus-perioden (t.ex. lunch).
            if ($breakStartMinutesOfDay !== null && $minutes >= $breakStartMinutesOfDay && $minutes < $breakEndMinutesOfDay) {
                continue;
            }

            $slots[] = [
                "date" => $date->format("Y-m-d"),
                "time" => sprintf("%02d:%02d:00", intdiv($minutes, 60), $minutes % 60)
            ];
        }
    }

    $date->modify("+1 day");
}

if (count($slots) === 0) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Inga tider att skapa utifrån dina val."], JSON_UNESCAPED_UNICODE);
    exit;
}

if (count($slots) > 200) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Det skulle skapa " . count($slots) . " tider, vilket är fler än max 200 åt gången. Minska intervallet eller datumspannet."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Hämta befintliga tider inom datumspannet i en enda fråga, så vi slipper
// en separat databasfråga per kandidat-tid.
$stmt = $pdo->prepare("
    SELECT appointment_date, appointment_time
    FROM appointments
    WHERE appointment_date BETWEEN ? AND ?
");
$stmt->execute([$rangeStart->format("Y-m-d"), $rangeEnd->format("Y-m-d")]);

$existing = [];
foreach ($stmt->fetchAll() as $row) {
    $existing[$row["appointment_date"] . " " . $row["appointment_time"]] = true;
}

$now = new DateTime();
$created = [];
$skippedPast = 0;
$skippedDuplicate = 0;

try {
    $pdo->beginTransaction();

    $insertStmt = $pdo->prepare("
        INSERT INTO appointments (appointment_date, appointment_time, information)
        VALUES (?, ?, ?)
    ");

    foreach ($slots as $slot) {
        $key = $slot["date"] . " " . $slot["time"];

        if (isset($existing[$key])) {
            $skippedDuplicate++;
            continue;
        }

        $slotDateTime = new DateTime($key);
        if ($slotDateTime <= $now) {
            $skippedPast++;
            continue;
        }

        $insertStmt->execute([$slot["date"], $slot["time"], $information !== "" ? $information : null]);

        $created[] = [
            "id" => (int)$pdo->lastInsertId(),
            "appointment_date" => $slot["date"],
            "appointment_time" => $slot["time"],
            "information" => $information,
            "booking_id" => null,
            "status" => "Ledig"
        ];

        // Håll koll på nya tider i samma körning så vi inte skapar dubbletter
        // av misstag om samma datum+tid skulle förekomma två gånger.
        $existing[$key] = true;
    }

    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Tiderna kunde inte skapas."], JSON_UNESCAPED_UNICODE);
    exit;
}

$parts = [count($created) . " tider skapades"];
if ($skippedDuplicate > 0) $parts[] = "$skippedDuplicate hoppades över (fanns redan)";
if ($skippedPast > 0) $parts[] = "$skippedPast hoppades över (redan passerade)";

echo json_encode([
    "success" => true,
    "message" => implode(", ", $parts) . ".",
    "created" => $created,
    "createdCount" => count($created),
    "skippedDuplicate" => $skippedDuplicate,
    "skippedPast" => $skippedPast
], JSON_UNESCAPED_UNICODE);
