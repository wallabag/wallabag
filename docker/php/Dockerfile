FROM php:fpm

# Default timezone. To change it, use the argument in the docker-compose.yml file
ARG timezone='Europe/Paris'

RUN apt-get update && apt-get install -y \
        libmcrypt-dev libicu-dev libpq-dev libxml2-dev \
    && docker-php-ext-install \
        iconv mcrypt mbstring intl pdo pdo_mysql pdo_pgsql

<<<<<<< bd561aeb66e1b67a8db188042fbe6d556564341d
<<<<<<< 2757e220c7cc7ac861c26e82c5152fa06407ab65
RUN echo "date.timezone="$timezone > /usr/local/etc/php/conf.d/date_timezone.ini
=======
RUN echo "date.timezone=Europe/Paris" > /usr/local/etc/php/conf.d/date_timezone.ini
>>>>>>> Add the timezone for PHP docker container
=======
RUN echo "date.timezone="$timezone > /usr/local/etc/php/conf.d/date_timezone.ini
>>>>>>> Add the timezone as an argument in the docker-compose. For that, need to use v2 of docker-compose (with version >= 1.6.0)

RUN usermod -u 1000 www-data

CMD ["php-fpm"]
