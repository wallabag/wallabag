#!/usr/bin/env bash
# You can execute this file to update wallabag
# eg: `sh update.sh prod`

set -e

COMPOSER_COMMAND='composer'

DIR="${BASH_SOURCE}"
if [ ! -d "$DIR" ]; then DIR="$PWD/scripts"; fi
. "$DIR/require.sh"

# starting from here, update shall abort if a variable is not set
set -u
ENV=$1

rm -rf var/cache/*
git fetch origin
git fetch --tags
TAG=$(git describe --tags $(git rev-list --tags --max-count=1))
git checkout $TAG --force
SYMFONY_ENV=$ENV $COMPOSER_COMMAND install --no-dev -o --prefer-dist
php bin/console doctrine:migrations:migrate --no-interaction --env=$ENV
php bin/console cache:clear --env=$ENV
