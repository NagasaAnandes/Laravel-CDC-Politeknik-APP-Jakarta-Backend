FROM php:8.2-fpm

ARG USER=deploy
ARG UID=1000

ENV TZ=Asia/Jakarta \
    COMPOSER_ALLOW_SUPERUSER=1 \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0

# Install system deps
RUN apt-get update && apt-get install -y \
    tzdata \
    git \
    unzip \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libzip-dev \
    zip \
    && ln -snf /usr/share/zoneinfo/$TZ /etc/localtime \
    && echo $TZ > /etc/timezone \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    zip \
    opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# PHP-FPM listen all interfaces
RUN sed -i 's|listen = 127.0.0.1:9000|listen = 9000|' /usr/local/etc/php-fpm.d/www.conf

# Opcache tuning
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=8" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.revalidate_freq=0" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini

# Composer LOCKED version
COPY --from=composer:2.9.3 /usr/bin/composer /usr/bin/composer

# Create non-root user
RUN useradd -u ${UID} -ms /bin/bash ${USER}

WORKDIR /var/www

# Copy project into image
COPY . /var/www

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# Optimize Laravel
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Permissions
RUN chown -R ${USER}:${USER} /var/www

USER ${USER}

EXPOSE 9000

CMD ["php-fpm"]