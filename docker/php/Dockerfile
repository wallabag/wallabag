FROM php:8.2-fpm AS rootless

ARG DEBIAN_FRONTEND=noninteractive
ARG NODE_VERSION=20

RUN apt-get update \
    && apt-get install -y \
      ca-certificates \
      curl \
      gnupg \
      lsb-release \
      openssl \
      software-properties-common

RUN curl -fsSL https://deb.nodesource.com/setup_${NODE_VERSION}.x | bash -

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

RUN pecl install redis-6.1.0 \
    && pecl install imagick-3.7.0 \
    && pecl install xdebug-3.4.1 \
    && docker-php-ext-enable \
        redis \
        imagick \
        xdebug

RUN version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") \
    && architecture=$(uname -m) \
    && curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/linux/$architecture/$version \
    && mkdir -p /tmp/blackfire \
    && tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp/blackfire \
    && mv /tmp/blackfire/blackfire-*.so $(php -r "echo ini_get ('extension_dir');")/blackfire.so \
    && printf "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8307\n" > $PHP_INI_DIR/conf.d/blackfire.ini \
    && rm -rf /tmp/blackfire /tmp/blackfire-probe.tar.gz

RUN mkdir -p /tmp/blackfire \
    && architecture=$(uname -m) \
    && curl -A "Docker" -L https://blackfire.io/api/v1/releases/cli/linux/$architecture | tar zxp -C /tmp/blackfire \
    && mv /tmp/blackfire/blackfire /usr/bin/blackfire \
    && rm -Rf /tmp/blackfire

RUN npm install -g yarn

RUN curl -L -o /usr/local/bin/envsubst https://github.com/a8m/envsubst/releases/download/v1.1.0/envsubst-`uname -s`-`uname -m`; \
    chmod +x /usr/local/bin/envsubst

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

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
