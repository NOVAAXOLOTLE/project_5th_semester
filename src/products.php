<?php
require 'config.php';
require 'helpers.php';

$page  = max(1,intval($_GET['page'] ?? 1));
$limit = max(1, min(100, intval($_GET['limit'] ?? 20)));
$skip  = ($page - 1) * $limit;

/** @var $productsColl */
$cursor = $productsColl->find([], ['skip'=>$skip, 'limit'=>$limit]);
$items = [];
foreach ($cursor as $doc) {
    $doc['_id'] = (string)$doc['_id'];
    $items[] = $doc;
}

jsonResponse(['page'=>$page, 'limit'=>$limit, 'data'=>$items]);