FROM php:fpm

# Default timezone. To change it, use the argument in the docker-compose.yml file
ARG timezone='Europe/Paris'

RUN apt-get update && apt-get install -y \
        libmcrypt-dev libicu-dev libpq-dev libxml2-dev libpng-dev libjpeg-dev \
    && /usr/local/bin/docker-php-ext-configure gd --with-jpeg-dir=/usr/include \
    && docker-php-ext-install \
        iconv mbstring intl pdo pdo_mysql pdo_pgsql gd

RUN echo "date.timezone="$timezone > /usr/local/etc/php/conf.d/date_timezone.ini

RUN usermod -u 1000 www-data

CMD ["php-fpm"]
