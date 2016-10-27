#! /usr/bin/env bash
# You can execute this file to install wallabag dev environment
# eg: `sh dev.sh`

DIR="${BASH_SOURCE%/*}"
if [[ ! -d "$DIR" ]]; then DIR="$PWD"; fi
. "$DIR/require.sh"

composer install
php bin/console wallabag:install
php bin/console server:run
