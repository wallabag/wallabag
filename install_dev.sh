#! /usr/bin/env bash

echo "Installing PHP dependencies (including dev) through Composer..."
composer install

echo "Downloading librairies through npm..."
npm install

echo "Concat, minify and installing assets..."
grunt

echo "Installing wallabag..."
php bin/console wallabag:install
