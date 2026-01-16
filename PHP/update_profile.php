<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* ---------------- HEADERS ---------------- */
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Content-Type");

/* ---------------- REDIS AUTH ---------------- */
require "redis.php";   // provides $redis

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
$token = str_replace("Bearer ", "", $authHeader);

if (!$token) {
    http_response_code(401);
    echo json_encode(["status" => "unauthorized"]);
    exit;
}

$session = $redis->get("session:$token");

if (!$session) {
    http_response_code(401);
    echo json_encode(["status" => "unauthorized"]);
    exit;
}

$sessionData = json_decode($session, true);
$userId = (int)$sessionData['user_id'];

/* ---------------- READ JSON ---------------- */
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
    exit;
}

$dob = $data['dob'] ?? null;
$age = isset($data['age']) ? (int)$data['age'] : null;
$contact = $data['contact'] ?? null;

/* ---------------- MONGODB UPDATE ---------------- */
require "db_mongo.php";   // provides $collection

try {
    $result = $collection->updateOne(
        ["user_id" => $userId],
        [
            '$set' => [
                "dob" => $dob,
                "age" => $age,
                "contact" => $contact,
                "updated_at" => new MongoDB\BSON\UTCDateTime()
            ]
        ]
    );

    echo json_encode(["status" => "success"]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
    exit;
}

