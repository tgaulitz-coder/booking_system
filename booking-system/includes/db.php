<?php

$host = "localhost";
$db = "booking_system";
$user = "gaulitz";
$pass = "password";

$options = [

    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

    PDO::ATTR_EMULATE_PREPARES => false

];

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        $options
    );
} catch (PDOException $e) {

    die("Databasfel: " . $e->getMessage());
}
