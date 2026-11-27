#! /usr/bin/env bash
# You can execute this file to create a new package for wallabag
# eg: `sh release.sh 2.3.3 /tmp wllbg-release prod`

set -e

VERSION=wallabag-$1
TMP_FOLDER=$2
RELEASE_FOLDER=$3
ENV=$4

rm -rf "${TMP_FOLDER:?}"/"$RELEASE_FOLDER"
mkdir "$TMP_FOLDER"/"$RELEASE_FOLDER"
git clone https://github.com/wallabag/wallabag.git --single-branch --depth 1 --branch $1 "$TMP_FOLDER"/"$RELEASE_FOLDER"/"$VERSION"
cd "$TMP_FOLDER"/"$RELEASE_FOLDER"/"$VERSION" && yarn install --non-interactive
cd "$TMP_FOLDER"/"$RELEASE_FOLDER"/"$VERSION" && yarn run --non-interactive build:prod
cd "$TMP_FOLDER"/"$RELEASE_FOLDER"/"$VERSION" && APP_ENV="$ENV" COMPOSER_MEMORY_LIMIT=-1 composer install -n --no-dev
cd "$TMP_FOLDER"/"$RELEASE_FOLDER"/"$VERSION" && APP_ENV="$ENV" php bin/console assets:install --env="$ENV" --symlink --relative
cd "$TMP_FOLDER"/"$RELEASE_FOLDER" && tar czf "$VERSION".tar.gz \
    --exclude="$VERSION/var/cache/*" --exclude="$VERSION/var/logs/*" --exclude="$VERSION/var/sessions/*" \
    --exclude="$VERSION/data/db/wallabag.sqlite" --exclude=".git" --exclude="$VERSION/.github" --exclude="$VERSION/node_modules" \
    --exclude="$VERSION/assets" --exclude="$VERSION/doc" --exclude="$VERSION/docker" --exclude="$VERSION/fixtures" --exclude="$VERSION/tests" \
    --exclude="$VERSION/.env.dev.example" --exclude="$VERSION/.env.test" \
    "$VERSION"
echo "SHA-256 checksum of the package for wallabag $VERSION"
sha256sum "$VERSION".tar.gz
echo "Package to upload to the release server:"
echo "$TMP_FOLDER"/"$RELEASE_FOLDER"/"$VERSION".tar.gz
