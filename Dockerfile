FROM php:8.2-apache

# Install required system libraries
RUN apt-get update && apt-get install -y \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html

# Fix permissions
RUN chown -R www-data:www-data /var/www/html
