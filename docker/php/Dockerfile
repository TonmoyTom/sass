FROM php:8.3-fpm

# System dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libwebp-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp

RUN docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache \
    intl

# Redis
RUN pecl install redis \
    && docker-php-ext-enable redis

# Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# DEV: host theke entire project mount hobe via docker-compose volume.
# Build time e code COPY korar dorkar nai — eta image bloat kore
# ar mount overlay korle anyway hide hoye jay.

# www-data user er UID host user er shathe match korar jonno (Linux file permission fix)
ARG UID=1000
ARG GID=1000
RUN usermod -u ${UID} www-data && groupmod -g ${GID} www-data || true

EXPOSE 9000
CMD ["php-fpm"]
