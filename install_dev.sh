#! /usr/bin/env bash

echo "Installing PHP dependencies (including dev) through Composer..."
composer install

echo "Downloading javascript librairies through npm..."
npm install

echo "Downloading fonts librairies through bower..."
bower install

echo "Concat, minify and installing assets..."
grunt

echo "Installing wallabag..."
php bin/console wallabag:install
