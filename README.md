# IcePHP/MiniQueue

**Author**: Michael Piper
**Contact**: icephp@pipermichael.com

IcePHP/MiniQueue is a lightweight PHP library for managing job queues and processing tasks asynchronously. This library provides a simple and efficient way to handle tasks in the background, allowing your application to perform time-consuming operations without blocking the main execution flow.

## Files

### `queue.php`

```php
<?php
require_once __DIR__."/../mini-queue.php";
use IcePHP\MiniQueue\MiniQueue;
$miniQueue = new MiniQueue([
    'driver' => 'pdo_sqlite',
    'path' => __DIR__ . '/../db.sqlite',
]);
$miniQueue->bootstrap();
return $miniQueue;
```

### `index.php`

```php
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
```

### `worker.php`

```php
<?php
use IcePHP\MiniQueue\MiniQueue, IcePHP\MiniQueue\ORM\Job;
/**
 * @var MiniQueue
 */
$miniQueue = require_once __DIR__."/queue.php";
require __DIR__ . '/vendor/autoload.php';

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
        // ...
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
```

## Usage

- Include the `MiniQueue` library in your project.
- Use the provided classes and methods for managing job queues and processing tasks.
- See examples in the code for sending scheduled emails and processing jobs.

## Getting Started

### Installation

To get started, include the MiniQueue library in your project:

```php
$miniQueue = require_once __DIR__."/queue.php";
require __DIR__ . '/vendor/autoload.php';
use IcePHP\MiniQueue\MiniQueue, IcePHP\MiniQueue\ORM\Job;
```

### Examples

#### Sending Scheduled Emails

```php
$emailSender = new EmailSender();

$emailData = [
    // ...
];

$emailSender->sendScheduledEmail($emailData);
```
### Running the Worker
#### You can run the worker using a cron job:

```cron
* * * * * /usr/local/bin/ea-php81 /home/****/public_html/****/worker.php
```
Make sure to adjust the path to the PHP executable (/usr/local/bin/ea-php81) and the path to your worker.php script accordingly.

## Contributing

If you'd like to contribute to this project, please follow the standard GitHub fork and pull request workflow. Additionally, make sure to adhere to the code of conduct.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

- Special thanks to the contributors of IcePHP/MiniQueue for their valuable contributions.

## Support

For any inquiries or support, please contact [icephp@pipermichael.com](mailto:icephp@pipermichael.com).