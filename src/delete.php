<?php
require 'config.php';
require 'helpers.php';

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST' && isset($_POST["_method"])) {
    $method = strtoupper($_POST["_method"]);
} elseif ($method === 'POST') {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    if (!is_array($json) && isset($json["_method"])) {
        $method = strtoupper($json["_method"]);
    }
}

$id = $_GET['id'] ?? null;

if (($method === 'DELETE' || $method === 'POST') && empty($id)) {
    $raw = file_get_contents('php://input');
    $body = json_decode($raw, true);
    if (is_array($body) && !empty($body['id'])) {
        $id = $body['id'];
    }
}

if (empty($id)) {
    if ($method === 'GET') {
        header('Content-type: text/html; charset=utf-8');
        echo "<h1>Eliminar (API)</h1>";
        echo "<p>Usa DELETE /eliminar/ID o DELETE /eliminar?id=ID o POST /eliminar with _method=DELETE</p>";
        exit;
    }
    jsonResponse(['error'=>'ID requerido'], 400);
}

if (!isObjectId($id)) jsonResponse(['error' => 'ID inválido'], 400);

$oid = new MongoDB\BSON\ObjectId($id);

/** @var $productsColl */
if ($method === 'DELETE') {
    try {
        $result = $productsColl->deleteOne(['_id' => $oid]);
        if ($result->getDeletedCount() === 0) {
            jsonResponse(['error' => 'No encontrado'], 404);
        }
        jsonResponse(['status' => 'success', 'deleted_id' => $id], 204);
    } catch (Exception $e) {
        jsonResponse(['error' => 'Error al eliminar', 'msg' => $e->getMessage()], 500);
    }
} elseif ($method === 'POST') {
    // si el cliente no soporta DELETE, aceptamos POST con _method
    if (isset($_POST['_method']) && strtoupper($_POST['_method']) === 'DELETE') {
        // delegar a la misma acción delete
        $result = $productsColl->deleteOne(['_id' => $oid]);
        if ($result->getDeletedCount() === 0) jsonResponse(['error'=>'No encontrado'],404);
        jsonResponse(['status'=>'success','deleted_id'=>$id]);
    }
    jsonResponse(['error'=>'Método no permitido'], 405);
} else {
    jsonResponse(['error' => 'Método no permitido'], 405);
}