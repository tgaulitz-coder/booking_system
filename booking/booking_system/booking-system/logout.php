<?php
//Loggar ut användaren genom att förstöra sessionen och ta bort all sparad data.
//Skickar användaren till startsidan.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

session_destroy();

header("Location: index.php");

exit;
