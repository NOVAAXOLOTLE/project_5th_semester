<?php
require 'config.php';
require 'helpers.php';

$id = $_GET['id'] ?? '';

if (empty($id)) {
    jsonResponse(['error' => 'ID requerido'], 400);
}

if (!isObjectId($id)) {
    jsonResponse(['error' => 'ID inválido'], 400);
}

/** @var $productsColl */
try {
    $oid = new MongoDB\BSON\ObjectId($id);
    $doc = $productsColl->findOne(['_id' => $oid]);

    if (!$doc) {
        jsonResponse(['error' => 'No encontrado'], 404);
    }

    $doc['_id'] = (string)$doc['_id'];
    if (isset($doc['creationDate']) && $doc['creationDate'] instanceof MongoDB\BSON\UTCDateTime) {
        $doc['creationDate'] = $doc['creationDate']->toDateTime()->format(DATE_ATOM);
    }

    jsonResponse($doc, 200);
} catch (Throwable $e) {
    jsonResponse(['error' => 'Error interno', 'msg' => $e->getMessage()], 500);
}