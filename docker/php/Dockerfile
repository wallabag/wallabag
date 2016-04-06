FROM php:fpm

# Default timezone. To change it, use the argument in the docker-compose.yml file
ARG timezone='Europe/Paris'

RUN apt-get update && \
    apt-get install -y zip libmcrypt-dev libicu-dev libpq-dev libxml2-dev libpng12-dev && \ 
    docker-php-ext-install iconv mcrypt mbstring intl gd pdo pdo_mysql pdo_pgsql

RUN echo "date.timezone="$timezone > /usr/local/etc/php/conf.d/date_timezone.ini

RUN usermod -u 1000 www-data

ADD ./bin /usr/local/bin

CMD ["run"]
