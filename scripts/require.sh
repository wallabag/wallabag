#! /usr/bin/env bash

# Check for composer
if [ ! -f composer.phar ]; then
    echo "composer.phar not found, we'll see if composer is installed globally."
    command -v composer >/dev/null 2>&1 || { echo >&2 "wallabag requires composer but it's not installed (see https://doc.wallabag.org/en/admin/installation/requirements.html). Aborting."; exit 1; }
else
    COMPOSER_COMMAND='./composer.phar'
fi

# Check for git
command -v git >/dev/null 2>&1 ||
{ echo >&2 "git is not installed. We can't install wallabag";
  exit 1
}
