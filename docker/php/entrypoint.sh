#!/bin/sh

envsubst < /opt/wallabag/config/wallabag-php.ini > /usr/local/etc/php/conf.d/wallabag-php.ini
envsubst < /opt/wallabag/config/parameters.yml > /var/www/html/app/config/parameters.yml

exec "$@"
