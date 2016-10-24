#! /usr/bin/env bash
# You can execute this file to install wallabag dev environmnet
# eg: `sh install.sh prod`

composer install
php bin/console wallabag:install
php bin/console server:run
