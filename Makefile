TMP_FOLDER=/tmp
RELEASE_FOLDER=wllbg-release

SSH_USER=framasoft_bag
SSH_HOST=78.46.248.87
SSH_PATH=/var/www/framabag.org/web

ENV=prod

help:
	@echo 'Makefile for wallabag                                                      '
	@echo '                                                                           '
	@echo 'Usage:                                                                     '
	@echo '   make clean                  clear cache folder                          '
	@echo '   make install                install latest stable wallabag version      '
	@echo '   make update                 update to the latest stable wallabag version'
	@echo '   make run                    run the wallabag server                     '
	@echo '   make build                  run grunt                                   '
	@echo '   make test                   execute wallabag testsuite                  '
	@echo '   make release                produce a wallabag release                  '
	@echo '   make travis                 make things for travis                      '
	@echo '   make deploy                 deploy wallabag                             '
	@echo '                                                                           '

clean:
	@rm -rf var/cache/*

install:
	TAG=$(git describe --tags $(git rev-list --tags --max-count=1))
	@git checkout $(TAG)
	@SYMFONY_ENV=$(ENV) composer install --no-dev -o --prefer-dist
	@php bin/console wallabag:install --env=$(ENV)

update:
	@rm -rf var/cache/*
	@git fetch origin
	@git fetch --tags
	TAG=$(git describe --tags $(git rev-list --tags --max-count=1))
	@git checkout $(TAG)
	@SYMFONY_ENV=prod composer install --no-dev -o --prefer-dist
	@php bin/console cache:clear --env=prod

run:
	php bin/console server:run --env=$(ENV)

build:
	@grunt

test:
	@ant prepare && phpunit -v

release:
	version=$(VERSION)
	@rm -rf $(TMP_FOLDER)/$(RELEASE_FOLDER)
	@mkdir $(TMP_FOLDER)/$(RELEASE_FOLDER)
	@git clone git@github.com:wallabag/wallabag.git -b $(VERSION) $(TMP_FOLDER)/$(RELEASE_FOLDER)/$(VERSION)
	@cd $(TMP_FOLDER)/$(RELEASE_FOLDER)/$(VERSION) && SYMFONY_ENV=$(ENV) composer up -n --no-dev
	@cd $(TMP_FOLDER)/$(RELEASE_FOLDER)/$(VERSION) && php bin/console wallabag:install --env=$(ENV)
	@cd $(TMP_FOLDER)/$(RELEASE_FOLDER) && tar czf wallabag-$(VERSION).tar.gz --exclude="var/cache/*" --exclude="var/logs/*" --exclude="var/sessions/*" --exclude=".git" $(VERSION)
	@echo "MD5 checksum of the package for wallabag $(VERSION)"
	@md5 $(TMP_FOLDER)/$(RELEASE_FOLDER)/wallabag-$(VERSION).tar.gz
	@scp $(TMP_FOLDER)/$(RELEASE_FOLDER)/wallabag-$(VERSION).tar.gz $(SSH_USER)@$(SSH_HOST):$(SSH_PATH)
	@rm -rf $(TMP_FOLDER)/$(RELEASE_FOLDER)

travis:

deploy:
	@bundle exec cap staging deploy

.PHONY: help clean install update build test release travis deploy
