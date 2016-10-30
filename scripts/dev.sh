#!/usr/bin/env bash
# You can execute this file to install wallabag dev environment
# eg: `sh dev.sh`

COMPOSER_COMMAND='composer'

DIR="${BASH_SOURCE}"
if [ ! -d "$DIR" ]; then DIR="$PWD/scripts"; fi
. "$DIR/require.sh"

$COMPOSER_COMMAND install
php bin/console wallabag:install
php bin/console server:run
