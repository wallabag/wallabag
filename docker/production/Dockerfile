# Release-matched runtime and locked dependencies. Build on the deployment host.
FROM wallabag/wallabag:2.6.14@sha256:4a527e027e0d59e87c14225ef11e005af3d4890374202ad319ce5e63dfc66709
ARG VCS_REF
LABEL org.opencontainers.image.title="Wallabag with RSS and special grabbers" \
      org.opencontainers.image.revision="${VCS_REF}"
WORKDIR /var/www/wallabag
COPY composer.json composer.lock ./
COPY app ./app
COPY src ./src
COPY web ./web
COPY docker/production/entrypoint.sh /usr/local/bin/wallabag-source-entrypoint
RUN test -n "$VCS_REF" \
    && test "$(sha256sum composer.lock | cut -d ' ' -f1)" = "2e18ef62bab2f8577eeeecdbef786a24c331de85ac0778d6d3b5c3b0efce2a56" \
    && composer dump-autoload --no-dev --classmap-authoritative --no-scripts --no-plugins \
    && rm -f app/config/parameters.yml data/site-credentials-secret-key.txt \
    && rm -rf var/cache var/logs \
    && printf '%s\n' "$VCS_REF" > .source-revision \
    && mkdir -p var/cache var/logs \
    && chown -R nobody:nobody var \
    && chmod 755 /usr/local/bin/wallabag-source-entrypoint
ENTRYPOINT ["/usr/local/bin/wallabag-source-entrypoint"]
CMD ["wallabag"]
