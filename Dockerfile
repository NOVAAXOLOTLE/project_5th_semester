FROM php:8.2-apache

# Evade interactive
ARG DEBIAN_FRONTEND=noninteractive

# Install necessary dependencies and mongodb extension (PECL)
RUN apt-get update \
  && apt-get install -y --no-install-recommends \
       git unzip libssl-dev pkg-config zlib1g-dev libzip-dev \
  && pecl install mongodb-1.21.2 \
  && docker-php-ext-enable mongodb \
  && a2enmod rewrite \
  && rm -rf /var/lib/apt/lists/*

# Copy composer from official Composer's image (binary)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Work directory
WORKDIR /var/www/html

# Copy composer.json to install dependencies (if exists)
COPY composer.json composer.lock* /var/www/html/

# Install PHP dependencies by Composer (if composer.json requires)
RUN composer install --no-dev --no-interaction --working-dir=/var/www/html || true

# Copy Apache's configuration
COPY apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Copy the app (ONLY if no mounted volumes in docker-compose)
# COPY src/ /var/www/html/

# Permissions (make sure that apache can rewrite if any images uploaded)
RUN chown -R www-data:www-data /var/www/html \
  && chmod -R 755 /var/www/html

# Expose Apache's port
EXPOSE 80

# Run Apache in main thread
CMD ["apache2-foreground"]
