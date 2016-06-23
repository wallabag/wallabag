#! /usr/bin/env bash

if [[ $ASSETS == 'nobuild' ]]; then
    composer install --no-interaction --no-progress --prefer-dist -o
else

    echo "Installing PHP dependencies through Composer..."
    if [[ $ASSETS == 'build' ]]; then
        composer install --no-interaction --no-progress --prefer-dist -o
    else
        SYMFONY_ENV=prod composer install --no-dev -o --prefer-dist
    fi

    chmod ugo+x vendor/mouf/nodejs-installer/bin/local/npm
    echo "Downloading javascript librairies through npm..."
    vendor/mouf/nodejs-installer/bin/local/npm install

    echo "Downloading fonts librairies through bower..."
    node_modules/bower/bin/bower install

    echo "Concat, minify and installing assets..."
    node_modules/grunt/bin/grunt

    if [[ $ASSETS != 'build' ]]; then
        echo "Installing wallabag..."
        php bin/console wallabag:install --env=prod
    fi

fi
