<?php
//Ansluter till mySQL databasen via PDO och gör anslutningen tillgänglig för filer som gör require på denna fil.
//Alla filer som pratar med databasen gör require_once __DIR__ . "/../includes/db.php och kan därefeter använda variablen $pdo direkt.

//config.php ligger två mappar upp, utanför webbroten av säkerhetsskäl.
$configPath = dirname(__DIR__, 2) . "/config.php";

//Felmeddelande om config inte hittas.
if (!file_exists($configPath)) {
    die("Konfigurationsfil saknas.");
}

$config = require $configPath;

//Inställningar för PDO anslutning.
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
