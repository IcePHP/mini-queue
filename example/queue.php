<?php
require_once __DIR__."/../vendor/autoload.php";
use IcePHP\MiniQueue\MiniQueue;
$miniQueue = new MiniQueue([
    'driver' => 'pdo_sqlite',
    'path' => __DIR__ . '/db.sqlite',
]);
$miniQueue->bootstrap();
return $miniQueue;