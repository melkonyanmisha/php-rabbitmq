<?php

require_once '/var/www/html/vendor/autoload.php';
require_once 'db/create_urls_table.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

try {
    // Establish a connection to RabbitMQ
    $connection = new AMQPStreamConnection(
        'rabbitmq',
        5672,
        getenv('RABBITMQ_DEFAULT_USER'),
        getenv('RABBITMQ_DEFAULT_PASS')
    );
    $channel    = $connection->channel();

    // Declare a queue in RabbitMQ
    $channel->queue_declare('url_queue', false, true, false, false);

    // Connect to MariaDB
    $mysqli = new mysqli(
        'mariadb', getenv('MARIADB_USER'), getenv('MARIADB_ROOT_PASSWORD'), getenv('MARIADB_DATABASE')
    );

    if ($mysqli->connect_error) {
        die('Connection to MariaDB failed: ' . $mysqli->connect_error);
    }

    // Message processing callback
    $processedRows = 0;
    $totalRows     = getenv('TOTAL_MESSAGES_COUNT');

    $callback = function ($message) use ($mysqli, &$processedRows, $totalRows) {
        $data = json_decode($message->body, true);

        if ($data && isset($data['url'], $data['created_at'])) {
            $url        = $data['url'];
            $created_at = $data['created_at'];

            // Get the content length without fetching the content
            $contentLength = strlen($url);

            // Save URL, content length, and created_at to MariaDB
            $query = "INSERT INTO urls (url, content_length, created_at) VALUES (?, ?, ?)";
            $stmt  = $mysqli->prepare($query);

            if ($stmt) {
                $stmt->bind_param("sis", $url, $contentLength, $created_at);
                if ($stmt->execute()) {
                    echo "Processed URL: $url, Created At: $created_at, Content Length: $contentLength\n";
                    $processedRows++; // Increment the counter
                } else {
                    echo "Error: " . $stmt->error . "\n";
                }
                $stmt->close();
            } else {
                echo "Error: " . $mysqli->error . "\n";
            }

            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        } else {
            echo "Invalid message format: " . $message->body . "\n";
        }

        // Check if all rows have been processed
        if ($processedRows >= $totalRows) {
            $mysqli->close();

            require_once 'get_data_from_db.php';
        }
    };

    // Set up the message processing callback
    $channel->basic_consume('url_queue', '', false, false, false, false, $callback);

    // Wait for messages in the queue
    while (count($channel->callbacks)) {
        $channel->wait();
    }

    // Close connections
    $channel->close();
    $connection->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
