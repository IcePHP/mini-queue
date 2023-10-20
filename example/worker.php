
<?php
use IcePHP\MiniQueue\MiniQueue, IcePHP\MiniQueue\ORM\Job;
/**
 * @var MiniQueue
 */
$miniQueue = require_once __DIR__."/queue.php";

class EmailSender
{
    public function __construct()
    {
       
    }

    private function sendEmail($payload)
    {
      // send mail here
      // mail()
    }

    public function sendScheduledEmail($emailData)
    {
        $adminEmails = $emailData['admin'];
        $recipientEmails = $emailData['recipient'];
        $subject = $emailData['subject'];
        $dueDateTime = $emailData['dueDateTime'];
        $hourstoDelivery = $emailData['hourstoDelivery'];
        $taskName = $emailData['taskName'];
        $interval =  $emailData['interval'];
        foreach ($adminEmails as $admin) {
            // Send email to admin using the admin template
            $adminTemplate = file_get_contents(__DIR__ . '/emailTemplate/adminEmail.html');
            $adminRecipients = implode(", ", array_column($recipientEmails, 'name'));
            $adminTemplate = str_replace('{{ allNames }}', $adminRecipients, $adminTemplate);
            $adminTemplate = str_replace('{{ taskName }}', $taskName, $adminTemplate);
            $adminTemplate = str_replace('{{ hoursOrDaysToDelivery }}', $hourstoDelivery, $adminTemplate);

            $this->sendEmail([
                'sender' => [
                    'name' => 'Prolificme Support',
                    'email' => 'reminder@prolificme.com',
                ],
                'to' => [['email' => $admin['email'], 'name' => $admin['name']]],
                'subject' => $subject,
                'htmlContent' => $this->generateEmailContent($adminTemplate, $admin['name'], $dueDateTime, $hourstoDelivery),
            ]);
        }


        // Calculate the delay based on the interval and hourstoDelivery
        // $delay = $hourstoDelivery % $interval;
        // $job->setScheduledAt(new \DateTime('@' . strtotime($dueDateTime) + $delay * 3600));

        // $jobQueue->add($job);


        foreach ($recipientEmails as $recipient) {

            // Send email to recipients using the recipient template
            $recipientTemplate = file_get_contents(__DIR__ . '/emailTemplate/recipientEmail.html');
            $recipientTemplate = str_replace('{{ name }}', $recipient['name'], $recipientTemplate);
            $recipientTemplate = str_replace('{{ taskName }}', $taskName, $recipientTemplate);
            $recipientTemplate = str_replace('{{ hoursOrDaysToDelivery }}', $hourstoDelivery, $recipientTemplate);

            $this->sendEmail([
                'sender' => [
                    'name' => 'Prolificme Support',
                    'email' => 'reminder@prolificme.com',
                ],
                'to' => [['email' => $recipient['email'], 'name' => $recipient['name']]],
                'subject' => $subject,
                'htmlContent' => $this->generateEmailContent($recipientTemplate, $recipient['name'], $dueDateTime, $hourstoDelivery),
            ]);
        }
        echo 'Emails scheduled successfully';
    }

    function generateEmailContent()
    {
        // implement email content here
    }
}

$miniQueue->log("Worker tick ". (new DateTime('now', new DateTimeZone(date_default_timezone_get()) ))->format('D, M, Y H:i:s'));
$miniQueue->process(type: 'TaskStart', callback:function (Job $job, callable $done) {
    $emailData = $job->getData();
    // Handle the /send-email route
    $emailSender = new EmailSender();
    $emailSender->sendScheduledEmail($emailData);
    $done();
});
