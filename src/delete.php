<?php
require 'config.php';
require 'helpers.php';

$id = $_GET['id'] ?? '';
if (!isObjectId($id)) jsonResponse(['error' => 'ID inválido'], 400);

$result = $productColl->deleteOne(['_id' => new MongoDB\BSON\ObjectID($id)]);
if ($result->getDeletedCount() === 0) jsonResponse(['error'=>'No encontrado'], 404);

jsonResponse(['deleted_id' => $id]);