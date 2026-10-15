# A CLI-only test tool. No web server, exposed ports, application credentials or production mounts.
FROM alpine:3.21
RUN apk add --no-cache composer git php83 php83-ctype php83-curl php83-dom php83-fileinfo php83-gd php83-iconv php83-intl php83-mbstring php83-openssl php83-pdo_sqlite php83-phar php83-session php83-simplexml php83-tidy php83-tokenizer php83-xml php83-xmlreader php83-xmlwriter php83-zip
WORKDIR /work
