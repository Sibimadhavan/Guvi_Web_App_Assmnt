<?php
require __DIR__ . '/vendor/autoload.php';

try {
    $client = new MongoDB\Client("mongodb://127.0.0.1:27017");

    $db = $client->guvi_test;
    $collection = $db->profiles;

    $result = $collection->insertOne([
        "name" => "Mongo Working",
        "time" => date("Y-m-d H:i:s")
    ]);

    echo "MongoDB connected. Inserted ID: " . $result->getInsertedId();
} catch (Exception $e) {
    echo "MongoDB Error: " . $e->getMessage();
}

