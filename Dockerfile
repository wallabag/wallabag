FROM    debian:jessie


RUN     apt-get -qq update && \
        DEBIAN_FRONTEND=noninteractive apt-get install -y curl apache2 php5 php5-xdebug php5-sqlite php5-pgsql php5-mysql php5-gd php5-curl php5-tidy&& \
        apt-get clean -y && \
        echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
        rm -rf /var/www/html


WORKDIR /var/www/html

# Making sure we run through the composer installation only if our requirements
# have changed
ADD     composer.* /var/www/html/

RUN     curl -s http://getcomposer.org/installer | php && \
        php composer.phar install


ADD     .  /var/www/html


RUN     chown www-data: -R assets cache db inc/poche install

EXPOSE  80

CMD     apache2ctl -DFOREGROUND
