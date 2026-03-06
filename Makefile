# This file requires GNU make. Use gmake if your system's default make is not GNU make.
SHELL := bash
TMP_FOLDER := /tmp
RELEASE_FOLDER := wllbg-release

# ensure the ENV variable is well defined
AVAILABLE_ENV := prod dev test
override ENV := $(if $(filter $(ENV),$(AVAILABLE_ENV)),$(ENV),prod)

DOCKER_COMPOSE_RUNNING = $(shell docker compose ps -q 2>/dev/null | grep -q . && echo 1 || echo 0)
COMPOSER_LOCAL = $(shell if command -v composer >/dev/null 2>&1; then printf '%s' composer; elif [ -f composer.phar ]; then printf '%s' ./composer.phar; fi)
COMPOSER = $(if $(filter 1,$(DOCKER_COMPOSE_RUNNING)),docker compose run --rm php composer,$(COMPOSER_LOCAL))
PHP = $(if $(filter 1,$(DOCKER_COMPOSE_RUNNING)),docker compose run --rm php php,php)
PHP_NO_XDEBUG = $(if $(filter 1,$(DOCKER_COMPOSE_RUNNING)),docker compose run -e XDEBUG_MODE=off --rm php php,XDEBUG_MODE=off php)
YARN = $(if $(filter 1,$(DOCKER_COMPOSE_RUNNING)),docker compose run --rm php yarn,yarn)

help: ## Display this help menu
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

install: ## Install wallabag with the latest version
	@./scripts/install.sh $(ENV)

update: ## Update the wallabag installation to the latest version
	@./scripts/update.sh $(ENV)

dev-docker-up: ## Start the Docker dev stack
	@docker compose up -d

dev-docker-down: ## Stop the Docker dev stack
	@docker compose down --remove-orphans --volumes

dev-setup: ## Install wallabag for development
	@[ -n "$(COMPOSER)" ] || { echo "composer command not found and ./composer.phar is missing." >&2; exit 1; }
	@$(COMPOSER) install
	@$(YARN) install
	@$(YARN) build:dev
	@$(PHP) bin/console wallabag:install --env=dev

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

test-unit: ## Launch unit testsuite
	@$(PHP_NO_XDEBUG) -dmemory_limit=-1 bin/phpunit --testsuite unit -v

test-integration: ## Launch integration testsuite
	@$(PHP_NO_XDEBUG) -dmemory_limit=-1 bin/phpunit --testsuite integration -v

test-functional: ## Launch functional testsuite
	@$(PHP_NO_XDEBUG) -dmemory_limit=-1 bin/phpunit --testsuite functional -v

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

.PHONY: help install update build test test-unit test-integration test-functional release deploy run dev dev-docker-up dev-docker-down dev-setup fix-cs phpstan phpstan-baseline lint-js lint-scss

.DEFAULT_GOAL := install
