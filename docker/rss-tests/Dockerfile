# Release-matched CLI runtime. No entrypoint initialization, network, server or production mounts.
FROM wallabag/wallabag:2.6.14@sha256:4a527e027e0d59e87c14225ef11e005af3d4890374202ad319ce5e63dfc66709
COPY docker/rss-tests/cache/php81-xmlwriter.apk /tmp/php81-xmlwriter.apk
RUN apk add --no-network /tmp/php81-xmlwriter.apk && rm /tmp/php81-xmlwriter.apk
WORKDIR /work
COPY . /work
ENTRYPOINT ["/bin/sh"]
