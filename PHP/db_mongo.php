<?php
require __DIR__ . '/../vendor/autoload.php';

$client = new MongoDB\Client("mongodb://127.0.0.1:27017");
$collection = $client->guvi_profiles->profiles;

