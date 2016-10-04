#! /usr/bin/env bash
set -e

echo " > Installing PHP dependencies through Composer..."
SYMFONY_ENV=prod composer install --no-interaction --no-progress --prefer-dist -o --no-dev

chmod ugo+x vendor/mouf/nodejs-installer/bin/local/npm
echo " > Downloading librairies through npm..."
vendor/mouf/nodejs-installer/bin/local/npm install

echo " > Concat, minify and installing assets..."
node_modules/grunt/bin/grunt

echo " > Install finished"
