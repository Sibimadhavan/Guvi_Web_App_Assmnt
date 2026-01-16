<?php
$conn = new mysqli(
    "127.0.0.1",
    "guvi_user",
    "guvi_pass123",
    "guvi_users"
);

if ($conn->connect_error) {
    die(json_encode([
        "status" => "error",
        "message" => "Database connection failed"
    ]));
}

