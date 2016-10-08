#! /usr/bin/env bash

TAG=$(git describe --tags $(git rev-list --tags --max-count=1))
git checkout $TAG
SYMFONY_ENV=prod composer install --no-dev -o --prefer-dist
php bin/console wallabag:install --env=prod
