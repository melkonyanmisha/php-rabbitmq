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

    // SQL statement to create the urls table
    $sql = "CREATE TABLE IF NOT EXISTS urls (
        id INT AUTO_INCREMENT PRIMARY KEY,
        url VARCHAR(255) NOT NULL,
        content_length INT NOT NULL,
        created_at TIMESTAMP NOT NULL
    )";

    // Use query() to execute the SQL statement
    if ($mysqli->query($sql) === true) {
        echo "Table 'urls' created successfully\n";
    } else {
        echo "Error creating table: " . $mysqli->error;
    }

    // Close the database connection
    $mysqli->close();
} catch (Exception $e) {
    echo "Error creating table: " . $e->getMessage();
}
