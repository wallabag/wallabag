#!/bin/sh

envsubst < /opt/wallabag/config/wallabag-php.ini > /usr/local/etc/php/conf.d/wallabag-php.ini

exec "$@"
