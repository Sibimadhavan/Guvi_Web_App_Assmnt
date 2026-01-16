<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* ---------------- HEADERS ---------------- */
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Content-Type");

/* ---------------- REDIS AUTH ---------------- */
require "redis.php";   // gives $redis

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

/* ---------------- MYSQL ---------------- */
require "db_mysql.php";   // gives $conn

$stmt = $conn->prepare(
    "SELECT username, email FROM users WHERE id = ?"
);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "User not found"]);
    exit;
}

$user = $result->fetch_assoc();

/* ---------------- MONGODB ---------------- */
require "db_mongo.php";   // gives $collection

$profile = $collection->findOne(["user_id" => $userId]);

/* ---------------- RESPONSE ---------------- */
echo json_encode([
    "status"   => "success",
    "username" => $user['username'],
    "email"    => $user['email'],
    "age"      => $profile['age'] ?? "",
    "dob"      => $profile['dob'] ?? "",
    "contact"  => $profile['contact'] ?? ""
]);
exit;

