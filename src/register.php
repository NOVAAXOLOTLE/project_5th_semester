<?php
require 'config.php';
require 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error'=>'Only POST requests are allowed'], 405);
$body = getJsonBody();
if (empty($body['name']) || !isset($body['price'])) jsonResponse(['error'=>'Campos requeridos'], 400);

$doc = [
    'name' => $body['name'],
    'description' => $body['description'] ?? '',
    'price' => (float)$body['price'],
    'stock' => (int)($body['stock'] ?? 0),
    'category' => $body['category'] ?? 'general',
    'images' => $body['images'] ?? [],
    'creationDate' => new MongoDB\BSON\UTCDateTime()
];

$res = $productsColl->insertOne($doc);
jsonResponse(['inserted_id' => (int)$res->getInsertedId()], 201);