<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool
{
    return isset($_SESSION["user"]);
}

function isAdmin(): bool
{
    return isset($_SESSION["user"])
        && $_SESSION["user"]["role"] === "admin";
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

function requireAdmin()
{
    if (!isAdmin()) {
        header("Location: ../index.php");
        exit;
    }
}
