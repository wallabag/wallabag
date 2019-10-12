#! /usr/bin/env bash

# Check for composer
if [ ! -f composer.phar ]; then
    echo "composer.phar not found, we'll see if composer is installed globally."
    command -v composer >/dev/null 2>&1 || { echo >&2 "wallabag requires composer but it's not installed (see http://doc.wallabag.org/en/master/user/installation.html). Aborting."; exit 1; }
else
    COMPOSER_COMMAND='./composer.phar'
fi
