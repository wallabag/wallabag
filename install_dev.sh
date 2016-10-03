#! /usr/bin/env bash

echo " > Installing PHP dependencies (including dev) through Composer..."
composer install -o  --no-interaction --no-progress --prefer-dist

if [[ $ASSETS == 'build' || $TRAVIS_BUILD_DIR == '' ]]; then
    echo " > Downloading librairies through npm..."
    npm install

    echo " > Concat, minify and installing assets..."
    grunt
fi

echo " > Install finished"
