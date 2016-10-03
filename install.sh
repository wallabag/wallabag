#! /usr/bin/env bash

if [[ $ASSETS == 'build' ]]; then
    echo "Installing PHP dependencies through Composer..."
    composer install --no-interaction --no-progress --prefer-dist -o

    chmod ugo+x vendor/mouf/nodejs-installer/bin/local/npm
    echo "Downloading librairies through npm..."
    vendor/mouf/nodejs-installer/bin/local/npm install

    echo "Concat, minify and installing assets..."
    node_modules/grunt/bin/grunt
else
    composer install --no-interaction --no-progress --prefer-dist -o
fi
