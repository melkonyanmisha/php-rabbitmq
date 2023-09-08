# Use an official PHP with Apache image as the base image
FROM php:7.4-apache

# Enable Apache modules and set document root
RUN a2enmod rewrite
RUN chown -R www-data:www-data /var/www/html
WORKDIR /var/www/html

# Install the required system packages
RUN apt-get update && apt-get install -y \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install sockets mysqli pdo pdo_mysql zip

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Allow Composer to run as superuser and skip platform requirements checks
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME /tmp

# Copy composer.json and composer.lock into the container
COPY composer.json composer.lock ./

# Install PHP dependencies using Composer
RUN composer install --no-plugins --no-scripts --ignore-platform-reqs

# Copy the entire src directory into the container
COPY . .

# Expose port 80 for Apache
EXPOSE 80

# Start the Apache web server
CMD ["apache2-foreground"]
