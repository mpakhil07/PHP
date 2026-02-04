FROM php:8.2-cli

# Install PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Set working directory
WORKDIR /app

# Copy project files
COPY . /app

# Expose Railway port
EXPOSE ${PORT}

# Start PHP built-in server (Railway compatible)
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT} -t /app"]
