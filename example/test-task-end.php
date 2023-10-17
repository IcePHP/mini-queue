<?php 
use IcePHP\MiniQueue\MiniQueue, IcePHP\MiniQueue\ORM\Job;
/**
 * @var MiniQueue
 */
$miniQueue = require_once __DIR__."/queue.php";
$miniQueue->log("Worker tick ". (new DateTime("now", new DateTimeZone(date_default_timezone_get())))->format('D, M, Y H:i:s'));
$miniQueue->process(type:'TaskEnd', callback: function(Job $job, callable $done) {
  echo "test TaskEnd".$job->getId();
  $data = $job->getData();
  echo "test TaskEnd data ".json_encode($data);
  // implement your function here
  foreach ($data['recipient'] as $recipient){
    mail($recipient['email'],$data['subject'], "Task End \n".$data['taskName']);
  }
  foreach ($data['admin'] as $recipient){
    mail($recipient['email'],$data['subject'], "Task End \n".$data['taskName']);
  }
  $done();
});

