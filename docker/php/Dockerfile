FROM php:8.3.4-fpm-alpine3.19

# Timezone
ENV TZ=Europe/Moscow
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Системные зависимости (только необходимое)
RUN apk add --no-cache \
    bash curl zip unzip tzdata \
    icu-libs libpng libwebp libxslt libzip libxml2 oniguruma \
    freetype libjpeg-turbo # для GD

# Установка зависимостей для сборки и PHP-расширений
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    icu-dev \
    libpng-dev \
    libwebp-dev \
    libxslt-dev \
    libzip-dev \
    libxml2-dev \
    freetype-dev \
    libjpeg-turbo-dev && \
    # Настройка и установка GD
    docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype && \
    # Установка основных расширений
    docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        xml \
        zip \
        intl \
        bcmath \
        sockets \
        opcache \
        gd && \
    # Установка PECL расширений
    pecl install redis apcu && \
    # Включение PECL расширений (правильный синтаксис)
    docker-php-ext-enable redis apcu && \
    # Очистка
    apk del .build-deps && \
    rm -rf /tmp/pear

RUN set -eux \
  && apk add --no-cache logrotate    

# Install composer
COPY --from=composer:2.7.2 /usr/bin/composer /usr/local/bin/composer

# Copy php settings
COPY ./php.ini ${PHP_INI_DIR}/conf.d/99-php.ini

# install cron
COPY crontab.txt /etc/crontab.txt

# install logrotate
COPY logrotate-cron.txt /etc/logrotate.d/cron
COPY logrotate-menu.txt /etc/logrotate.d/menu
COPY logrotate-iiko-hook.txt /etc/logrotate.d/iiko-hook

# Copy entrypoint
COPY ./entrypoint /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

# Workdir
ENV WORKDIR=/var/www/app
RUN mkdir -p ${WORKDIR}
WORKDIR ${WORKDIR}

# Run entrypoint
CMD ["docker-entrypoint"]
