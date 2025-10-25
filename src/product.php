<?php
echo "hello";
require 'config.php';
require 'helpers.php';

$id = $_GET["id"] ?? '';
if (!isObjectId($id)) jsonResponse(['error'=>'ID inválido'], 400);

$prod = $productsColl->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
if (!$prod) jsonResponse(['error'=>'No encontrado'], 404);

$prod['_id'] = (string)$prod['_id'];
jsonResponse($prod);