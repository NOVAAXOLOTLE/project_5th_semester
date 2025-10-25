<?php
require_once __DIR__ . '/vendor/autoload.php';

$mongoUri = getenv('MONGO_URI') ?: 'mongodb://monguito:27017';
$dbName   = getenv('MONGO_DB') ?: 'marketplace_db';

$client   = new MongoDB\Client($mongoUri);
$db       = $client->selectDatabase($dbName);

$productsColl = $db->products;