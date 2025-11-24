# Dockerfile
FROM php:8.2-fpm

ARG DEBIAN_FRONTEND=noninteractive

# install required packages and build tools
RUN apt-get update \
  && apt-get install -y --no-install-recommends \
      git zip unzip libssl-dev pkg-config zlib1g-dev libzip-dev libpng-dev libonig-dev \
      curl ca-certificates \
  && rm -rf /var/lib/apt/lists/*

# install specific ext-mongodb 1.x (compatible with jenssegers/mongodb)
RUN pecl install mongodb-1.21.2 \
  && docker-php-ext-enable mongodb

# common php extensions used by Laravel
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# copy composer files first (for caching)
COPY composer.json composer.lock* /var/www/html/

# install composer deps (if composer.json present in build context)
RUN composer install --no-dev --no-interaction --prefer-dist || true

# copy rest of the app
COPY . /var/www/html

# ensure permissions for storage & bootstrap cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true \
  && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true

EXPOSE 9000
CMD ["php-fpm"]