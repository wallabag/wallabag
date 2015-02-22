FROM    php:apache



RUN     apt-get -qq update && \
        DEBIAN_FRONTEND=noninteractive apt-get install -y git libpq-dev libsqlite3-dev 	libpng12-dev libcurl4-gnutls-dev libtidy-dev && \
        apt-get clean -y && \
        echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
        rm -rf /var/www/html



RUN     docker-php-ext-install gettext mbstring pdo_mysql pdo_pgsql pdo_sqlite gd curl tidy zip && php -m


WORKDIR  /var/www/html


# Making sure we run through the composer installation only if our requirements
# have changed
ADD     composer.* /var/www/html/

RUN     curl -s http://getcomposer.org/installer | php && \
        php composer.phar install


ADD     .  /var/www/html


RUN     chown www-data: -R assets cache db inc/poche install

EXPOSE  80

VOLUME  /var/log/apache2

CMD     apache2ctl -DFOREGROUND
