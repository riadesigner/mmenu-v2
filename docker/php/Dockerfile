# From
FROM php:8.3.4-fpm-alpine3.19

# Set timezone
ENV TZ=UTC
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Install runtime dependencies
RUN set -eux \
    && apk add --no-cache \
    git vim zip unzip bash curl tzdata icu-libs \
    c-client make ca-certificates gmp gettext libssh2 yaml \
    libintl libxslt libpng libwebp libjpeg-turbo freetype \
    linux-headers oniguruma libpq vips \
    libzip libxml2 freetds jq logrotate

# Install build dependencies
RUN set -eux \
    && apk add --no-cache --virtual .build_deps \
    libpng-dev libwebp-dev imap-dev libjpeg-turbo-dev freetype-dev \
    linux-headers oniguruma-dev libxslt-dev postgresql-dev vips-dev \
    libssh2-dev gmp-dev libzip-dev libxml2-dev freetds-dev yaml-dev \
    zlib-dev \
    $PHPIZE_DEPS

# Configure and install GD (ДО очистки!)
RUN set -eux \
    && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install -j$(nproc) gd

# Install PHP extensions
RUN set -eux \
    && docker-php-ext-install \
    mysqli \
    pdo_mysql \
    pdo_pgsql \
    pgsql \
    bcmath \
    mbstring \
    xml \
    exif \
    zip \
    soap \
    intl \
    xsl \
    pcntl \
    sockets \
    sysvmsg \
    sysvsem \
    sysvshm \
    imap \
    gmp \
    opcache

# Install PECL extensions
RUN set -eux \
    && pecl install -o -f ssh2 \
    && docker-php-ext-enable ssh2

# Cleanup
RUN set -eux \
    && apk del --purge .build_deps \
    && rm -rf /tmp/pear /var/cache/apk/* \
    && docker-php-source delete

# Install composer
COPY --from=composer:2.7.2 /usr/bin/composer /usr/local/bin/composer

# Copy configurations
COPY ./php.ini ${PHP_INI_DIR}/conf.d/custom.ini
COPY crontab.txt /etc/crontab.txt
COPY logrotate-*.txt /etc/logrotate.d/

# Copy entrypoint
COPY ./entrypoint /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

# Workdir
ENV WORKDIR=/var/www/app
RUN mkdir -p ${WORKDIR}
WORKDIR ${WORKDIR}

CMD ["docker-entrypoint"]