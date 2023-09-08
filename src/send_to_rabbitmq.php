<?php

require_once '/var/www/html/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

try {
    // Establish a connection to RabbitMQ
    $connection = new AMQPStreamConnection(
        'rabbitmq',
        5672,
        getenv('RABBITMQ_DEFAULT_USER'),
        getenv('RABBITMQ_DEFAULT_PASS')
    );
    $channel    = $connection->channel();

    // Declare the queue in RabbitMQ
    $queueName = 'url_queue';
    $channel->queue_declare($queueName, false, true, false, false);

    // Get the current message count
    $messageInfo  = $channel->queue_declare($queueName, true);
    $messageCount = $messageInfo[1];
    $maxMessages = getenv('TOTAL_MESSAGES_COUNT'); //todo@@@need to change

    if ($messageCount >= $maxMessages) {
        echo "Queue has reached the limit of $maxMessages messages. No more messages will be added.\n";
    } else {
        for ($i = 0; $i < $maxMessages - $messageCount; $i++) {
            $randomTextLength = rand(1, 15);
            $randomText       = generateRandomString($randomTextLength);

            $url = 'https://example.com/' . $randomText;

            // Include a timestamp in the message
            $created_at  = date('Y-m-d H:i:s');
            $messageBody = json_encode(['url' => $url, 'created_at' => $created_at]);

            // Randomly choose a delay between 5 and 20 seconds
            $delay = rand(5, 20);
            sleep($delay);

            // Create a message with the URL and timestamp
            $message = new AMQPMessage($messageBody);

            // Publish the message to the queue
            $channel->basic_publish($message, '', $queueName);
            echo "Sent URL: $url\n";
        }
    }

    // Close the RabbitMQ connection
    $channel->close();
    $connection->close();;

    require_once 'process_url_queue.php';
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

function generateRandomString($length)
{
    $characters   = '0123456789abcdefghijabcde789';
    $randomString = '';
    $charLength   = strlen($characters);

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charLength - 1)];
    }

    return $randomString;
}
