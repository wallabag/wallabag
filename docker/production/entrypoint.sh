#!/bin/sh
set -eu
cd /var/www/wallabag

# Host-specific values and existing secrets are supplied at runtime, never built.
test -s /run/wallabag/parameters.yml || { echo 'Mount the existing parameters.yml at /run/wallabag/parameters.yml.' >&2; exit 1; }
cp /run/wallabag/parameters.yml app/config/parameters.yml
chown nobody:nobody app/config/parameters.yml
chmod 600 app/config/parameters.yml
test -s /run/wallabag/site-credentials-key || { echo 'Mount the existing site credential encryption key.' >&2; exit 1; }
cp /run/wallabag/site-credentials-key data/site-credentials-secret-key.txt
chown nobody:nobody data/site-credentials-secret-key.txt
chmod 600 data/site-credentials-secret-key.txt
if test -s /run/wallabag/php.ini; then
    cp /run/wallabag/php.ini /etc/php81/conf.d/50_wallabag.ini
fi

if test "${1:-}" = wallabag; then
    su -s /bin/sh -c 'exec "$@"' -- nobody wallabag-cli php bin/console cache:warmup --env=prod --no-debug
    exec s6-svscan /etc/s6/
fi
if test "${1:-}" = console; then
    shift
    exec su -s /bin/sh -c 'exec "$@"' -- nobody wallabag-cli php bin/console "$@"
fi
if test "${1:-}" = rss-worker; then
    exec su -s /bin/sh -c 'while :; do php bin/console wallabag:rss:fetch --env=prod --no-debug || echo "RSS import reported errors; inspect subscription status." >&2; sleep 60; done' -- nobody
fi
exec "$@"
