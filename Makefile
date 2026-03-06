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

DOCKER_COMPOSE_RUNNING := $(shell docker compose ps -q | grep -q . && echo 1 || echo 0)

ifeq ($(DOCKER_COMPOSE_RUNNING), 1)
  PHP := docker compose run --rm php php
  PHP_NO_XDEBUG := docker compose run -e XDEBUG_MODE=off --rm php php
  YARN := docker compose run --rm php yarn
else
  PHP := php
  PHP_NO_XDEBUG := XDEBUG_MODE=off php
  YARN := yarn
endif

help: ## Display this help menu
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

install: ## Install wallabag with the latest version
	@./scripts/install.sh $(ENV)

update: ## Update the wallabag installation to the latest version
	@./scripts/update.sh $(ENV)

dev: ENV=dev
dev: build ## Install the latest dev version
	@./scripts/dev.sh

run: ## Run the wallabag built-in server
	@$(PHP) bin/console server:run --env=dev

build: ## Run webpack
	@$(YARN) install
	@$(YARN) build:$(ENV)

test: ## Launch wallabag testsuite
	@$(PHP_NO_XDEBUG) -dmemory_limit=-1 bin/phpunit -v

fix-cs: ## Run PHP-CS-Fixer
	@$(PHP_NO_XDEBUG) bin/php-cs-fixer fix

phpstan: ## Run PHPStan
	@$(PHP_NO_XDEBUG) bin/phpstan analyse

phpstan-baseline: ## Generate PHPStan baseline
	@$(PHP_NO_XDEBUG) bin/phpstan analyse --generate-baseline

lint-js: ## Run ESLint
	@$(YARN) lint:js

lint-scss: ## Run Stylelint
	@$(YARN) lint:scss

release: ## Create a package. Need a VERSION parameter (eg: `make release VERSION=master`).
ifndef VERSION
	$(error VERSION is not set)
endif
	@./scripts/release.sh $(VERSION) $(TMP_FOLDER) $(RELEASE_FOLDER) $(ENV)

deploy: ## Deploy wallabag
	@bundle exec cap staging deploy

.PHONY: help install update build test release deploy run dev fix-cs phpstan

.DEFAULT_GOAL := install
