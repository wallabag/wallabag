#! /usr/bin/env sh

if [ "$BUILD_ASSETS" == "no" ]; then
    echo "Not installing assets"
else
    echo "Downloading javascript librairies through npm..."
    npm install
    echo "Downloading fonts librairies through bower..."
    bower install
    echo "Concat, minify and installing assets..."
    grunt
fi
