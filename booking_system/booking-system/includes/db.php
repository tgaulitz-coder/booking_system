<?php

$configPath = dirname(__DIR__, 2) . "/config.php";

if (!file_exists($configPath)) {
    die("Konfigurationsfil saknas.");
}

$config = require $configPath;

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
];

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
        $config['user'],
        $config['pass'],
        $options
    );
} catch (PDOException $e) {
    die("Databasfel: " . $e->getMessage());
}
