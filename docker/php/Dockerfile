FROM php:fpm

RUN apt-get update && apt-get install -y \
        libmcrypt-dev libicu-dev libpq-dev libxml2-dev \
    && docker-php-ext-install \
        iconv mcrypt mbstring intl pdo pdo_mysql pdo_pgsql

RUN usermod -u 1000 www-data

CMD ["php-fpm"]
