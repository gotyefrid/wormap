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
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    shadow \
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
    sqlite3 pdo_sqlite \
    gd

# Устанавливаем XDebug
RUN pecl install xdebug-3.3.1 && \
    docker-php-ext-enable xdebug

# Создаем пользователя и группу с переданными UID/GID
ARG USER_ID
ARG GROUP_ID

RUN addgroup -g $GROUP_ID appgroup && \
    adduser -D -u $USER_ID -G appgroup appuser

# Даем владельцу права на директорию проекта
WORKDIR /var/www/app
RUN chown -R appuser:appgroup /var/www/app

# Устанавливаем пользователя по умолчанию
USER appuser
