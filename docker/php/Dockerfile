FROM php:fpm

# Default timezone. To change it, use the argument in the docker-compose.yml file
ARG timezone='Europe/Paris'

RUN apt-get update && apt-get install -y \
        libmcrypt-dev libicu-dev libpq-dev libxml2-dev libpng12-dev libjpeg-dev \
    && /usr/local/bin/docker-php-ext-configure gd --with-jpeg-dir=/usr/include \
    && docker-php-ext-install \
        iconv mcrypt mbstring intl pdo pdo_mysql pdo_pgsql gd opcache

# Enable and configure xdebug
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

RUN echo "date.timezone="$timezone > /usr/local/etc/php/conf.d/date_timezone.ini

RUN usermod -u 1000 www-data

CMD ["php-fpm"]
