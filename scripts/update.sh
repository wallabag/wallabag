#! /usr/bin/env bash

ENV=$1
TAG=$(git describe --tags $(git rev-list --tags --max-count=1))

rm -rf var/cache/*
git fetch origin
git fetch --tags
git checkout $TAG --force
SYMFONY_ENV=$ENV composer install --no-dev -o --prefer-dist
php bin/console cache:clear --env=$ENV
