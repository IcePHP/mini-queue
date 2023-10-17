<?php
use IcePHP\MiniQueue\MiniQueue;
/**
 * @var MiniQueue
 */
$miniQueue = require_once __DIR__."/queue.php";
// Handle the /send-email route
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] === '/emailreminder/index.php/send-email') {
    $requestBody = file_get_contents('php://input');
    $payload = json_decode($requestBody, true);
    $timezone = $payload['timezone']?? 'Africa/Lagos';
     $delay = new DateTime($scheduledDateTime, new DateTimeZone($timezone));
    $job = $miniQueue->queue([
            'delay' => $delay, 
            'type' => 'TaskStart',
            'data' => $payload
        ]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Route not found', 'resp' => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '']);
}
?>