#! /usr/bin/env bash
# You can execute this file to install wallabag
# eg: `sh install.sh prod`

command -v composer >/dev/null 2>&1 || { echo >&2 "wallabag requires composer but it's not installed (see http://doc.wallabag.org/en/master/user/installation.html). Aborting."; exit 1; }

ENV=$1
TAG=$(git describe --tags $(git rev-list --tags --max-count=1))

git checkout $TAG
SYMFONY_ENV=$ENV composer install --no-dev -o --prefer-dist
php bin/console wallabag:install --env=$ENV
