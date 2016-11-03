#!/usr/bin/env bash
# You can execute this file to install wallabag
# eg: `sh install.sh prod`

COMPOSER_COMMAND='composer'

DIR="${BASH_SOURCE}"
if [ ! -d "$DIR" ]; then DIR="$PWD/scripts"; fi
. "$DIR/require.sh"

ENV=$1
TAG=$(git describe --tags $(git rev-list --tags --max-count=1))

git checkout $TAG
SYMFONY_ENV=$ENV $COMPOSER_COMMAND install --no-dev -o --prefer-dist
php bin/console wallabag:install --env=$ENV
