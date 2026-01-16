<?php
/* -------------------- ERROR REPORTING (REMOVE IN PROD IF NEEDED) -------------------- */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* -------------------- HEADERS -------------------- */
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

/* -------------------- DB CONNECTIONS -------------------- */
require "db_mysql.php";   // MySQL connection ($conn)
require "db_mongo.php";   // MongoDB connection ($collection)

/* -------------------- READ JSON INPUT -------------------- */
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid JSON input"
    ]);
    exit;
}

/* -------------------- SANITIZE INPUT -------------------- */
$username = trim($data['username'] ?? '');
$email    = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$dob      = $data['dob'] ?? null;
$age      = isset($data['age']) ? (int)$data['age'] : null;
$contact  = $data['contact'] ?? null;

if (!$username || !$email || !$password) {
    echo json_encode([
        "status" => "error",
        "message" => "Required fields missing"
    ]);
    exit;
}

/* -------------------- CHECK EMAIL EXISTS -------------------- */
$checkEmail = $conn->prepare(
    "SELECT id FROM users WHERE email = ?"
);
$checkEmail->bind_param("s", $email);
$checkEmail->execute();
$checkEmail->store_result();

if ($checkEmail->num_rows > 0) {
    echo json_encode(["status" => "emailexists"]);
    exit;
}

/* -------------------- CHECK USERNAME EXISTS -------------------- */
$checkUser = $conn->prepare(
    "SELECT id FROM users WHERE username = ?"
);
$checkUser->bind_param("s", $username);
$checkUser->execute();
$checkUser->store_result();

if ($checkUser->num_rows > 0) {
    echo json_encode(["status" => "usernameexists"]);
    exit;
}

/* -------------------- INSERT USER (MYSQL) -------------------- */
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$insertUser = $conn->prepare(
    "INSERT INTO users (username, email, password) VALUES (?, ?, ?)"
);
$insertUser->bind_param("sss", $username, $email, $hashedPassword);

if (!$insertUser->execute()) {
    echo json_encode([
        "status" => "error",
        "message" => "MySQL insert failed"
    ]);
    exit;
}

$userId = $conn->insert_id;

/* -------------------- INSERT PROFILE (MONGODB) -------------------- */
try {
    $collection->insertOne([
        "user_id"  => $userId,
        "username" => $username,
        "email"    => $email,
        "dob"      => $dob,
        "age"      => $age,
        "contact"  => $contact,
        "created_at" => new MongoDB\BSON\UTCDateTime()
    ]);

    echo json_encode(["status" => "success"]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "MongoDB insert failed"
    ]);
    exit;
}

