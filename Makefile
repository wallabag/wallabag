TMP_FOLDER=/tmp
RELEASE_FOLDER=wllbg-release

SSH_USER=framasoft_bag
SSH_HOST=78.46.248.87
SSH_PATH=/var/www/framabag.org/web

ENV=prod

help: ## Display this help menu
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

clean: ## Clear the application cache
	@rm -rf var/cache/*

install: ## Install wallabag with the latest version
	TAG=$(git describe --tags $(git rev-list --tags --max-count=1))
	@git checkout $(TAG)
	@SYMFONY_ENV=$(ENV) composer install --no-dev -o --prefer-dist
	@php bin/console wallabag:install --env=$(ENV)

update: ## Update the wallabag installation to the latest version
	@rm -rf var/cache/*
	@git fetch origin
	@git fetch --tags
	TAG=$(git describe --tags $(git rev-list --tags --max-count=1))
	@git checkout $(TAG)
	@SYMFONY_ENV=prod composer install --no-dev -o --prefer-dist
	@php bin/console cache:clear --env=prod

run: ## Run the wallabag server
	php bin/console server:run --env=$(ENV)

build: ## Run grunt
	@grunt

test: ## Launch wallabag testsuite
	@ant prepare && phpunit -v

release: ## Create a package. Need a VERSION parameter (eg: `make release VERSION=master`).
ifndef VERSION
	$(error VERSION is not set)
endif
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

travis: ## Make some stuff for Travis-CI

deploy: ## Deploy wallabag
	@bundle exec cap staging deploy

.PHONY: help clean install update build test release travis deploy
