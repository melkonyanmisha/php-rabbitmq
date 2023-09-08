<p align="center">
    <h1 align="center">PHP RabbitMQ and MariaDB Integration</h1>
    <br>
</p>

This is a PHP application that demonstrates the integration of RabbitMQ and MariaDB (MySQL) for message processing and database storage. The application sends URLs to RabbitMQ with a delay, processes them, and stores the data in a MariaDB database. It also includes a script to analyze and display statistics based on the stored data.

DIRECTORY STRUCTURE
-------------------

```
src
    db/                        Directory containing database initialization scripts.
    send_to_rabbitmq.php       Script for sending URLs to RabbitMQ with a delay.
    process_url_queue.php      Script for processing messages from RabbitMQ and storing data in MariaDB.
    get_data_from_db.php       Script to analyze and display statistics from the database.
Dockerfile                     Docker configuration for the PHP container.
docker-compose.yml             Docker Compose configuration for setting up the project.
.env                           Configuration file

```


RUN PROJECT
-------------------

```
docker-compose up -d
```