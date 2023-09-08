<?php

try {
    // Connect to MariaDB
    $mysqli = new mysqli(
        'mariadb', getenv('MARIADB_USER'), getenv('MARIADB_ROOT_PASSWORD'), getenv('MARIADB_DATABASE')
    );

    // Check connection
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    // SQL statement to group data by minute
    $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') AS minute,
            COUNT(*) AS row_count,
            AVG(content_length) AS avg_content_length,
            MIN(created_at) AS first_message_time,
            MAX(created_at) AS last_message_time
            FROM urls
            GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d %H:%i')";

    $result = $mysqli->query($sql);

    if ($result->num_rows > 0) {
        // Output the results
        while ($row = $result->fetch_assoc()) {
            echo "Minute: " . $row['minute'] . "\n";
            echo "Row Count: " . $row['row_count'] . "\n";
            echo "Average Content Length: " . $row['avg_content_length'] . "\n";
            echo "First Message Time: " . $row['first_message_time'] . "\n";
            echo "Last Message Time: " . $row['last_message_time'] . "\n\n";
        }
    } else {
        echo "No results found\n";
    }

    // Close the database connection
    $mysqli->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
