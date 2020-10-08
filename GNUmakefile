SHELL=bash
TMP_FOLDER=/tmp
RELEASE_FOLDER=wllbg-release

# ensure the ENV variable is well defined
AVAILABLE_ENV := prod dev test
ifneq ($(filter $(ENV),$(AVAILABLE_ENV)),)
	# all good
else
	# not good, force it to "prod"
	override ENV = prod
endif

help: ## Display this help menu
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

clean: ## Clear the application cache
	rm -rf var/cache/*

install: ## Install wallabag with the latest version
	@./scripts/install.sh $(ENV)

update: ## Update the wallabag installation to the latest version
	@./scripts/update.sh $(ENV)

dev: ENV=dev
dev: build ## Install the latest dev version
	@./scripts/dev.sh

run: ## Run the wallabag built-in server
	@php bin/console server:run --env=dev

build: ## Run webpack
	@yarn install
	@yarn build:$(ENV)

prepare: clean ## Prepare database for testsuite
ifdef DB
	cp app/config/tests/parameters_test.$(DB).yml app/config/parameters_test.yml
endif
	-php bin/console doctrine:database:drop --force --env=test
	php bin/console doctrine:database:create --env=test
	php bin/console doctrine:migrations:migrate --no-interaction --env=test

fixtures: ## Load fixtures into database
	php bin/console doctrine:fixtures:load --no-interaction --env=test

test: prepare fixtures ## Launch wallabag testsuite
	bin/simple-phpunit -v

release: ## Create a package. Need a VERSION parameter (eg: `make release VERSION=master`).
ifndef VERSION
	$(error VERSION is not set)
endif
	@./scripts/release.sh $(VERSION) $(TMP_FOLDER) $(RELEASE_FOLDER) $(ENV)

deploy: ## Deploy wallabag
	@bundle exec cap staging deploy

.PHONY: help clean prepare install fixtures update build test release deploy run dev

.DEFAULT_GOAL := install
