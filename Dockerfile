FROM php:8.2-apache

# Disable conflicting MPMs (safety)
RUN a2dismod mpm_event || true \
 && a2dismod mpm_worker || true \
 && a2enmod mpm_prefork

# Install MySQL extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_mysql mysqli \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable rewrite
RUN a2enmod rewrite

# Copy project
WORKDIR /var/www/html
COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html

# Railway PORT fix (CRITICAL)
RUN sed -i 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf \
 && sed -i 's/:80/:${PORT}/' /etc/apache2/sites-enabled/000-default.conf

# Start Apache in foreground
CMD ["bash", "-c", "apache2-foreground"]
