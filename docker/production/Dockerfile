ARG PHP_VERSION=8.2.35

FROM node:24-bookworm-slim@sha256:2dd9a4f758e173b65fee15388f7706af6fb01ef50cf7a2c0538711d67a6981ae AS frontend-assets
WORKDIR /var/www/wallabag
COPY package.json yarn.lock ./
RUN corepack prepare yarn@1.22.22 --activate \
    && yarn install --frozen-lockfile --non-interactive
COPY . .
RUN yarn build:prod \
    && test -s web/build/manifest.json \
    && test -s web/build/entrypoints.json

FROM php:${PHP_VERSION}-apache-trixie@sha256:fa8966fbaaa06981e549e7a33eb8a68f4cf677d6a82e1d6350adc5d85fe8f740

ARG PHP_VERSION
ARG VCS_REF
LABEL org.opencontainers.image.title="Wallabag with RSS and special grabbers" \
      org.opencontainers.image.revision="${VCS_REF}" \
      org.opencontainers.image.base.name="docker.io/library/php:${PHP_VERSION}-apache-trixie@sha256:fa8966fbaaa06981e549e7a33eb8a68f4cf677d6a82e1d6350adc5d85fe8f740" \
      com.jonniesweb.wallabag.php.version="${PHP_VERSION}"

ENV APP_ENV=prod \
    APP_DEBUG=0 \
    WALLABAG_USER_AGENT="Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/15.0.874.92 Safari/535.2" \
    APACHE_DOCUMENT_ROOT=/var/www/wallabag/web

WORKDIR /var/www/wallabag

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libtidy-dev \
        libwebp-dev \
        libzip-dev \
        zlib1g-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j2 \
        gd \
        intl \
        opcache \
        pdo_mysql \
        sockets \
        tidy \
        zip \
    && a2enmod headers rewrite \
    && sed -ri 's!/var/www/html!/var/www/wallabag/web!g' /etc/apache2/sites-available/000-default.conf \
    && printf '\n<Directory /var/www/wallabag/web>\n    AllowOverride All\n    Require all granted\n</Directory>\n' >> /etc/apache2/apache2.conf \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2@sha256:d0c32820c62cc860fddd22adceed4eeda7698b093a7a88d503414172ce400626 /usr/bin/composer /usr/local/bin/composer
COPY . /var/www/wallabag
COPY --from=frontend-assets /var/www/wallabag/web/build /var/www/wallabag/web/build
COPY docker/production/php-production.ini /usr/local/etc/php/conf.d/99-wallabag-production.ini
COPY docker/production/entrypoint.sh /usr/local/bin/wallabag-source-entrypoint

RUN set -eux; \
    test -n "$VCS_REF"; \
    test "$(php -r 'echo PHP_VERSION;')" = "$PHP_VERSION"; \
    php -r '$loaded = array_map("strtolower", get_loaded_extensions()); $required = ["curl", "dom", "gd", "intl", "mbstring", "pdo_mysql", "simplexml", "tidy", "xml", "xmlreader", "xmlwriter", "zip"]; $missing = array_diff($required, $loaded); if ($missing) { fwrite(STDERR, "Missing PHP extensions: ".implode(", ", $missing)."\n"); exit(1); }'; \
    composer install --no-dev --no-interaction --prefer-dist --no-progress --no-scripts --classmap-authoritative; \
    test -s vendor/autoload_runtime.php; \
    composer check-platform-reqs --no-dev; \
    mkdir -p data var/cache var/logs var/sessions web/assets/images; \
    test -s web/build/manifest.json; \
    test -s web/build/entrypoints.json; \
    APP_ENV=prod APP_DEBUG=0 APP_SECRET=build-only-secret DEFAULT_LOCALE=en WALLABAG_BASE_URL=https://build.invalid MAILER_DSN=null://null WALLABAG_REGISTRATION_ENABLED=0 WALLABAG_CAPTCHA_ENABLED=0 WALLABAG_CONFIRMATION_ENABLED=0 WALLABAG_FROM_EMAIL=wallabag@example.invalid WALLABAG_SERVER_NAME=wallabag-build WALLABAG_TWOFACTOR_SENDER=no-reply@example.invalid WALLABAG_LOG_LEVEL=error WALLABAG_RABBITMQ_PREFETCH_COUNT=10 REDIS_URL=redis://127.0.0.1 RABBITMQ_URL=amqp://guest:guest@127.0.0.1:5672/%2f WALLABAG_USER_AGENT=wallabag-build WALLABAG_OAUTH_ACCESS_TOKEN_LIFETIME=3600 WALLABAG_OAUTH_REFRESH_TOKEN_LIFETIME=1209600 WALLABAG_TABLE_PREFIX=wallabag_ WALLABAG_ARTICLE_REPORTING_URL= DATABASE_URL=sqlite:////tmp/wallabag-image-build.sqlite bin/console assets:install web --symlink --relative --no-interaction --env=prod --no-debug; \
    rm -rf var/cache/* /tmp/wallabag-image-build.sqlite; \
    usermod --non-unique --uid 65534 --gid nogroup www-data; \
    chown -R www-data:nogroup var data web/assets/images; \
    chmod 755 /usr/local/bin/wallabag-source-entrypoint; \
    printf '%s\n' "$VCS_REF" > .source-revision; \
    chmod 644 .source-revision

ENTRYPOINT ["/usr/local/bin/wallabag-source-entrypoint"]
CMD ["wallabag"]
