#!/usr/bin/env bash
# You can execute this file to update wallabag
# eg: `sh update.sh prod`

IGNORE_ROOT_ARG="--ignore-root-warning"
IGNORE_ROOT=0

while :; do
    case $1 in
        $IGNORE_ROOT_ARG) IGNORE_ROOT=1
        ;;
        *[a-zA-Z]) ENV=$1
        ;;
        *) break
        ;;
    esac
    shift
done

# Abort running this script if root
if [ "$IGNORE_ROOT" -eq 0 ] && [ "$EUID" == "0" ]; then
    echo "Do not run this script as root!" >&2
    echo "Use $IGNORE_ROOT_ARG to ignore this error." >&2
    exit 1
fi

set -e
set -u

COMPOSER_COMMAND='composer'
REQUIRE_FILE='scripts/require.sh'

if [ ! -f "$REQUIRE_FILE" ]; then
  echo "Cannot find $REQUIRE_FILE"
  exit 1
fi

. "$REQUIRE_FILE"

# Check for wallabag .git folder
if [ ! -d .git ]; then
    echo "Can not update because wallabag wasn't installed using git (see https://doc.wallabag.org/en/admin/upgrade.html#upgrade-on-a-shared-hosting). Aborting.";
    exit 2;
fi

rm -rf var/cache/*
git fetch origin
git fetch --tags
TAG=$(git describe --tags $(git rev-list --tags --max-count=1))
git checkout $TAG --force
SYMFONY_ENV=$ENV $COMPOSER_COMMAND install --no-dev -o --prefer-dist
php bin/console doctrine:migrations:migrate --no-interaction --env=$ENV
php bin/console cache:clear --env=$ENV
