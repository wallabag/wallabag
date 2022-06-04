FROM php:7.4-fpm AS rootless

ARG DEBIAN_FRONTEND=noninteractive
ARG NODE_VERSION=16

RUN apt-get update \
    && apt-get install -y \
      ca-certificates \
      curl \
      gnupg \
      lsb-release \
      openssl \
      software-properties-common

RUN curl 'https://deb.nodesource.com/gpgkey/nodesource.gpg.key' | apt-key add - \
    && echo "deb https://deb.nodesource.com/node_${NODE_VERSION}.x $(lsb_release -cs) main" > /etc/apt/sources.list.d/nodesource.list

RUN apt-get update && apt-get install -y \
        libmcrypt-dev \
        libicu-dev \
        libpq-dev \
        libxml2-dev \
        libpng-dev \
        libjpeg-dev \
        libwebp-dev \
        libsqlite3-dev \
        imagemagick \
        libmagickwand-dev \
        libtidy-dev \
        libonig-dev \
        libzip-dev \
        libfreetype6-dev \
        zlib1g-dev \
        git \
        build-essential \
        nodejs
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
RUN docker-php-ext-install -j "$(nproc)" \
        bcmath \
        gd \
        gettext \
        iconv \
        intl \
        mbstring \
        opcache \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        pdo_sqlite \
        sockets \
        tidy \
        zip

RUN pecl install redis; \
    pecl install imagick; \
    pecl install xdebug; \
    docker-php-ext-enable \
        redis \
        imagick \
        xdebug \
    ;

RUN npm install -g yarn

RUN curl -L -o /usr/local/bin/envsubst https://github.com/a8m/envsubst/releases/download/v1.1.0/envsubst-`uname -s`-`uname -m`; \
    chmod +x /usr/local/bin/envsubst

COPY --from=composer:2.2.12 /usr/bin/composer /usr/local/bin/composer

COPY entrypoint.sh /entrypoint.sh
COPY config/ /opt/wallabag/config/

RUN mkdir -p \
        /var/www/html/app/config/ \
        /var/www/html/var/cache \
        /var/www/html/web/assets \
        /var/www/html/data \
        /var/www/html/data/db \
        /var/www/.cache

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php", "bin/console", "--env=dev", "server:run", "0.0.0.0:8000"]

FROM rootless AS default

ARG USER_UID=1000
ARG USER_GID=1000

RUN groupmod -g 1000 www-data ; \
    usermod -u ${USER_UID} -g www-data www-data ; \
    touch /usr/local/etc/php/conf.d/wallabag-php.ini \
        /var/www/.yarnrc ; \
    chown -R www-data: /var/www/html \
        /usr/local/etc/php/conf.d/wallabag-php.ini \
        /var/www/.cache \
        /var/www/.yarnrc

USER www-data
