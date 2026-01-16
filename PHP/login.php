<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

/* ---------- MYSQL ---------- */
require "db_mysql.php";   // MUST use guvi_user + guvi_users

/* ---------- REDIS ---------- */
require "redis.php";      // gives $redis

/* ---------- READ JSON ---------- */
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
    exit;
}

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(["status" => "invalid"]);
    exit;
}

/* ---------- CHECK USER ---------- */
$stmt = $conn->prepare(
    "SELECT id, password FROM users WHERE email = ?"
);

if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Prepare failed"]);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "invalid"]);
    exit;
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    echo json_encode(["status" => "invalid"]);
    exit;
}

/* ---------- CREATE SESSION ---------- */
$token = bin2hex(random_bytes(32));

$payload = [
    "user_id" => (int)$user['id'],
    "email" => $email,
    "login_time" => time()
];

$redis->setex(
    "session:$token",
    3600,
    json_encode($payload)
);

/* ---------- RESPONSE ---------- */
echo json_encode([
    "status" => "success",
    "token" => $token
]);
exit;

