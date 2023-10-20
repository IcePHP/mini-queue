<?php

use IcePHP\MiniQueue\MiniQueue;
use IcePHP\MiniQueue\ORM\Job;

/**
 * @var MiniQueue
 */
$miniQueue = require_once __DIR__ . "/queue.php";
const type = "TaskStart";
$emailJob = $miniQueue->queue(data: ['email' => 'test@email.com'], type: type, options: [
  'removeOnCompleted' => true,
  'retries' => 1
]);
$nJob = $miniQueue->queue(data: ['name' => 'test'], type: type);
$job = $miniQueue->getJob($nJob->getId());
$job->log('found with $miniQueue->getJob(' . $nJob->getId() . ')');

$miniQueue->getCompletedJobs(function (
    Job  $job
) use ($nJob) {
    if ($job->getId() !== $nJob->getId()) {
        $job->log('found and destroyed');
        $job->remove();
    } else {
        $job->log('found');
    }
}, type: type);

$miniQueue->getJobs(function (
    Job  $job
) use ($nJob) {
    if ($job->getId() == $nJob->getId()) {
        $job->log('found and destroyed');
        $job->remove();
    } else {
        $job->log('found');
    }
}, type: type);
$miniQueue->process(type: type, callback: function (Job $job, callable $done) {
    $emailData = $job->getData();
    // Handle the /send-email route
    $job->log(type . " " . json_encode($emailData));
    // throw new \Exception("Error". $job->getId() . "");
    $done();
});
