FROM php:fpm

<<<<<<< HEAD
# Default timezone. To change it, use the argument in the docker-compose.yml file
=======
>>>>>>> 2c3759a... Add the timezone as an argument in the docker-compose. For that, need to use v2 of docker-compose (with version >= 1.6.0)
ARG timezone='Europe/Paris'

RUN apt-get update && apt-get install -y \
        libmcrypt-dev libicu-dev libpq-dev libxml2-dev \
    && docker-php-ext-install \
        iconv mcrypt mbstring intl pdo pdo_mysql pdo_pgsql

<<<<<<< d58f7652c78bfb4110bcebe671ac805a1e86c38d
<<<<<<< bd561aeb66e1b67a8db188042fbe6d556564341d
<<<<<<< 2757e220c7cc7ac861c26e82c5152fa06407ab65
RUN echo "date.timezone="$timezone > /usr/local/etc/php/conf.d/date_timezone.ini
=======
RUN echo "date.timezone=Europe/Paris" > /usr/local/etc/php/conf.d/date_timezone.ini
>>>>>>> Add the timezone for PHP docker container
=======
RUN echo "date.timezone="$timezone > /usr/local/etc/php/conf.d/date_timezone.ini
>>>>>>> Add the timezone as an argument in the docker-compose. For that, need to use v2 of docker-compose (with version >= 1.6.0)
=======
<<<<<<< HEAD
RUN echo "date.timezone="$timezone > /usr/local/etc/php/conf.d/date_timezone.ini
=======
RUN echo "date.timezone=Europe/Paris" > /usr/local/etc/php/conf.d/date_timezone.ini
>>>>>>> b543935... Add the timezone for PHP docker container
>>>>>>> Add the timezone for PHP docker container

RUN usermod -u 1000 www-data

CMD ["php-fpm"]
