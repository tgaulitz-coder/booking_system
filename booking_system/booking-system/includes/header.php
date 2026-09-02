<?php

require_once __DIR__ . "/auth.php";

// Skapa projektets webbsökväg dynamiskt. Då fungerar menyn även om
// projektmappen byter namn eller ligger i en annan undermapp i WAMP.
$projectRoot = realpath(dirname(__DIR__));
$documentRoot = isset($_SERVER["DOCUMENT_ROOT"]) ? realpath($_SERVER["DOCUMENT_ROOT"]) : false;

if ($projectRoot !== false && $documentRoot !== false && str_starts_with($projectRoot, $documentRoot)) {
    $baseUrl = str_replace(DIRECTORY_SEPARATOR, "/", substr($projectRoot, strlen($documentRoot)));
} else {
    $baseUrl = "";
}

$baseUrl = rtrim($baseUrl, "/");

?>

<!DOCTYPE html>
<html lang="sv">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

    <title>
        Bokningssystem
    </title>

    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/css/style.css">

</head>

<body>


    <header>

        <div class="headerBrand">
            <img class="logo"
                src="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/images/logo.png"
                alt="Bokningssystem logga">

            <h1>
                Bokningssystem
            </h1>
        </div>




        <?php if (isLoggedIn()): ?>


            <nav>

                <a class="navLink" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/dashboard.php">
                    Hem
                </a>


                <a class="navLink" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/appointments.php">
                    Boka tid
                </a>


                <a class="navLink" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/mybookings.php">
                    Mina bokningar
                </a>



                <?php if (isAdmin()): ?>

                    <a class="navLink" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/index.php">
                        Adminpanel
                    </a>

                <?php endif; ?>



                <a class="navLink navLink-logout" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/logout.php">
                    Logga ut
                </a>


            </nav>


        <?php endif; ?>


    </header>


    <main>