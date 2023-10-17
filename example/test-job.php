<?php
use IcePHP\MiniQueue\MiniQueue;
/**
 * @var MiniQueue
 */
$miniQueue = require_once __DIR__."/queue.php";
$payload = [ 
    "admin" => [
        ["email" => "olalekanray18@gmail.com", "name"=> "Admin 1"],
        ["email" => "delakwebtech20@gmail.com", "name"=> "Admin 2"]
    ],
    "recipient" => [
        ["email" => "deetsrealm@gmail.com", "name"=> "Deets Realm"],
        ["email" => "nou221068012@noun.edu.ng", "name"=> "Abdullah Abbas"]
    ],
    "taskName" => "Sample Task",
    "subject" => "Trial Prolific Reminder",
    "dueDateTime" => "2023-10-12T14:30:00",
    "hourstoDelivery" => "4",
    "interval" => "0.01",
    "startDate" => "2023-10-17T22:45:00",
    "endDate" => "2023-10-17T22:50:00"
];
// $delay = new DateTime($payload['startDate']);
// // $delay->setTimestamp($delay->getTimestamp() + ($payload['interval'] * 60 * 60 * Job::MILLISECOND_TO_SEC));

// Get the start and end timestamps based on $startDate and $endDate
$startDate = $payload['startDate'];
$endDate = $payload['endDate'];
$interval = $payload['interval'];
$timezone = $payload['timezone']?? 'Africa/Lagos';
$startDateTimestamp = strtotime($startDate);
$endDateTimestamp = strtotime($endDate);

// Get the interval in hours and convert it to seconds
$intervalInHours = $interval  * 60 * 60;
$schedules =[];
// Loop from start date to end date with the specified interval
for ($timestamp = $startDateTimestamp; $timestamp <= $endDateTimestamp; $timestamp += $intervalInHours) {
    // Convert the timestamp back to the desired format (Y-m-d H:i:s)
    $scheduledDateTime = date('Y-m-d H:i:s', $timestamp);

    // Set the emailData dueDateTime to the scheduled date and time
    $payload['dueDateTime'] = $scheduledDateTime;

    // Wait for the specified interval before sending the next email
    $delay = new DateTime($scheduledDateTime, new DateTimeZone($timezone));
    $job = $miniQueue->queue([
        'delay' => $delay, 
        'type' => 'TaskStart',
        'data' => $payload
    ]);
    $schedules[] = ['jobId'=> $job->getId(),'date'=>$scheduledDateTime];
}
echo json_encode(['message'=>'Emails scheduled successfully', 'timezone'=> $timezone, 'schedules' => $schedules]);



