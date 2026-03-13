# =========================================================
# BASE IMAGE
# PHP 8.2 FPM untuk Laravel Production
# =========================================================
FROM php:8.2-fpm

# =========================================================
# ENVIRONMENT
# =========================================================
ENV TZ=Asia/Jakarta \
    COMPOSER_ALLOW_SUPERUSER=1

# =========================================================
# SYSTEM DEPENDENCIES
# =========================================================
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
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*


# =========================================================
# PHP-FPM TUNING
# =========================================================
RUN sed -i 's|listen = 127.0.0.1:9000|listen = 9000|' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/^pm.max_children.*/pm.max_children = 20/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/^pm.start_servers.*/pm.start_servers = 4/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/^pm.min_spare_servers.*/pm.min_spare_servers = 2/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/^pm.max_spare_servers.*/pm.max_spare_servers = 6/' /usr/local/etc/php-fpm.d/www.conf \
    && echo "pm.max_requests = 500" >> /usr/local/etc/php-fpm.d/www.conf


# =========================================================
# PHP PRODUCTION SETTINGS
# =========================================================
RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/production.ini \
    && echo "upload_max_filesize=50M" >> /usr/local/etc/php/conf.d/production.ini \
    && echo "post_max_size=50M" >> /usr/local/etc/php/conf.d/production.ini \
    && echo "max_execution_time=60" >> /usr/local/etc/php/conf.d/production.ini


# =========================================================
# OPCACHE CONFIGURATION
# =========================================================
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=16" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini


# =========================================================
# COMPOSER
# =========================================================
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer


# =========================================================
# WORKDIR
# =========================================================
WORKDIR /var/www


# =========================================================
# COPY APPLICATION
# =========================================================
COPY src/ .


# =========================================================
# INSTALL DEPENDENCIES
# =========================================================
RUN composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --classmap-authoritative \
    --no-interaction \
    --no-progress \
    --no-scripts


# =========================================================
# PERMISSIONS
# =========================================================
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache


# =========================================================
# PORT
# =========================================================
EXPOSE 9000


# =========================================================
# HEALTHCHECK
# =========================================================
HEALTHCHECK --interval=30s --timeout=10s --retries=3 \
    CMD php-fpm -t || exit 1


# =========================================================
# START PHP-FPM
# =========================================================
CMD ["php-fpm"]