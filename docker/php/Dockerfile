FROM php:8.3-fpm-alpine3.17

# Устанавливаем зависимости, в том числе необходимые для сборки расширений
RUN apk update && apk add --no-cache \
    autoconf \
    g++ \
    make \
    gcc \
    linux-headers \
    sqlite \
    sqlite-dev \
    $PHPIZE_DEPS

# Скачиваем и устанавливаем скрипт для установки расширений PHP
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions

# Устанавливаем PHP-расширения
RUN install-php-extensions \
    @composer \
    curl \
    mbstring \
    dom \
    xml \
    xmlwriter \
    sqlite3 pdo_sqlite

# Устанавливаем XDebug
RUN pecl install xdebug-3.3.1 && \
    docker-php-ext-enable xdebug

# Копируем конфигурацию XDebug
COPY xdebug.ini /usr/local/etc/php/conf.d

WORKDIR /var/www/app/
