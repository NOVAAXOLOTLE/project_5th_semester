<?php
function jsonResponse($data, $status = 200)
{
    header('Content-type: application/json', true, $status);
    echo json_encode($data);
    exit;
}

function getJsonBody()
{
    $raw = file_get_contents('php://input');
    return json_decode($raw, true);
}

function isObjectId($id)
{
    return preg_match('/^[0-9a-fA-F]{24}$/', $id);
}