#!/bin/sh
set -eu
umask 077
cd /var/www/wallabag

# Host-specific values and the existing encryption key are supplied at runtime.
test -s /run/wallabag/parameters.yml || { echo 'Mount the existing parameters.yml at /run/wallabag/parameters.yml.' >&2; exit 1; }
install -o www-data -g nogroup -m 600 /run/wallabag/parameters.yml app/config/parameters.yml
test -s /run/wallabag/site-credentials-key || { echo 'Mount the existing site credential encryption key.' >&2; exit 1; }
install -o www-data -g nogroup -m 600 /run/wallabag/site-credentials-key data/site-credentials-secret-key.txt

if test -s /run/wallabag/php.ini; then
    install -o root -g root -m 644 /run/wallabag/php.ini "$PHP_INI_DIR/conf.d/50-wallabag.ini"
fi

if test "${1:-}" = wallabag; then
    runuser -u www-data -- php bin/console cache:warmup --env=prod --no-debug
    exec apache2-foreground
fi
if test "${1:-}" = console; then
    shift
    exec runuser -u www-data -- php bin/console "$@"
fi
if test "${1:-}" = rss-worker; then
    trap 'exit 0' INT TERM
    while :; do
        runuser -u www-data -- php bin/console wallabag:rss:fetch --env=prod --no-debug || echo 'RSS import reported errors; inspect subscription status.' >&2
        sleep 60
    done
fi
exec "$@"
