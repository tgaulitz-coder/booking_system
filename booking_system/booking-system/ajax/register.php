<?php

require "../includes/db.php";

header("Content-Type: application/json");


// Hämta JSON från Fetch

$data = json_decode(
    file_get_contents("php://input"),
    true
);


$name = trim($data["name"]);
$username = trim($data["username"]);
$email = trim($data["email"]);
$password = $data["password"];



// Enkel kontroll

if (
    empty($name) ||
    empty($username) ||
    empty($email) ||
    empty($password)
) {

    echo json_encode([
        "success" => false,
        "message" => "Alla fält måste fyllas i"
    ]);

    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    echo json_encode([
        "success" => false,
        "message" => "Ange en giltig e-postadress"
    ]);

    exit;
}

if (strlen($password) < 8) {

    echo json_encode([
        "success" => false,
        "message" => "Lösenordet måste vara minst 8 tecken"
    ]);

    exit;
}
// Kryptera lösenord

$passwordHash = password_hash(
    $password,
    PASSWORD_DEFAULT
);



try {


    $sql = "

    INSERT INTO users
    (
        name,
        username,
        email,
        password
    )

    VALUES
    (
        ?,
        ?,
        ?,
        ?
    )

    ";


    $stmt = $pdo->prepare($sql);


    $stmt->execute([

        $name,
        $username,
        $email,
        $passwordHash

    ]);



    echo json_encode([

        "success" => true,

        "message" => "Kontot skapades"

    ]);
} catch (PDOException $e) {


    echo json_encode([

        "success" => false,

        "message" => "Användarnamn eller e-post finns redan"

    ]);
}
