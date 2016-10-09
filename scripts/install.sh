#! /usr/bin/env bash

ENV=$1
TAG=$(git describe --tags $(git rev-list --tags --max-count=1))

git checkout $TAG
SYMFONY_ENV=$ENV composer install --no-dev -o --prefer-dist
php bin/console wallabag:install --env=$ENV
