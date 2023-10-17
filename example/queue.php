<?php
require_once __DIR__."/../mini-queue.php";
use IcePHP\MiniQueue\MiniQueue;
$miniQueue = new MiniQueue([
    'driver' => 'pdo_sqlite',
    'path' => __DIR__ . '/../db.sqlite',
]);
$miniQueue->bootstrap();
return $miniQueue;