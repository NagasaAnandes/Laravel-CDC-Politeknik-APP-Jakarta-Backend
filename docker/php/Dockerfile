FROM php:8.2-fpm

ENV TZ=Asia/Jakarta \
    COMPOSER_ALLOW_SUPERUSER=1

# System deps
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
    && rm -rf /var/lib/apt/lists/*

# FPM listen all interfaces
RUN sed -i 's|listen = 127.0.0.1:9000|listen = 9000|' /usr/local/etc/php-fpm.d/www.conf

# FPM production tuning
RUN echo "pm = dynamic" >> /usr/local/etc/php-fpm.d/www.conf \
    && echo "pm.max_children = 20" >> /usr/local/etc/php-fpm.d/www.conf \
    && echo "pm.start_servers = 4" >> /usr/local/etc/php-fpm.d/www.conf \
    && echo "pm.min_spare_servers = 2" >> /usr/local/etc/php-fpm.d/www.conf \
    && echo "pm.max_spare_servers = 6" >> /usr/local/etc/php-fpm.d/www.conf

# Opcache
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=192" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini

# Composer
COPY --from=composer:2.9.3 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy only composer first (cache optimization)
COPY src/composer.json src/composer.lock ./

RUN composer install --no-dev --optimize-autoloader --no-scripts

# Now copy full source
COPY src/ .

# Permissions (granular)
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]