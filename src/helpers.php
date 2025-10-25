<?php
function jsonResponse($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
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