#!/usr/bin/env bash
# You can execute this file to install wallabag
# eg: `sh install.sh prod`

IGNORE_ROOT_ARG="--ignore-root-warning"
IGNORE_ROOT=0

if [ "$1" == "$IGNORE_ROOT_ARG" ]; then
    IGNORE_ROOT=1
fi

# Abort running this script if root
if [ "$IGNORE_ROOT" -eq 0 ] && [ "$EUID" == "0" ]; then
    echo "Do not run this script as root!" >&2
    echo "Use $IGNORE_ROOT_ARG to ignore this error." >&2
    exit 1
fi

COMPOSER_COMMAND='composer'

DIR="${BASH_SOURCE}"
if [ ! -d "$DIR" ]; then DIR="$PWD/scripts"; fi
. "$DIR/require.sh"

ENV=$1
TAG=$(git describe --tags $(git rev-list --tags --max-count=1))

git checkout $TAG
SYMFONY_ENV=$ENV $COMPOSER_COMMAND install --no-dev -o --prefer-dist
php bin/console wallabag:install --env=$ENV
