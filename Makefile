.PHONY: help up down down-v build shell shell-root migrate migrate-fresh seed test pint pint-dry logs ps restart cache-clear optimize composer-install npm-install npm-build dev prod

COMPOSE ?= docker compose

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-18s\033[0m %s\n", $$1, $$2}'

up: ## Build and start all services
	$(COMPOSE) up -d --build

down: ## Stop all services
	$(COMPOSE) down

down-v: ## Stop all services and remove volumes
	$(COMPOSE) down -v

build: ## Build the app image
	$(COMPOSE) build

shell: ## Open a shell in the app container
	$(COMPOSE) exec app sh

shell-root: ## Open a root shell in the app container
	$(COMPOSE) exec -u root app sh

migrate: ## Run database migrations
	$(COMPOSE) exec app php artisan migrate --force

migrate-fresh: ## Drop all tables and re-run migrations
	$(COMPOSE) exec app php artisan migrate:fresh --force

seed: ## Seed the database
	$(COMPOSE) exec app php artisan db:seed --force

test: ## Run the test suite
	$(COMPOSE) exec app php artisan test

pint: ## Run Pint code formatter
	$(COMPOSE) exec app ./vendor/bin/pint

pint-dry: ## Run Pint in dry-run mode
	$(COMPOSE) exec app ./vendor/bin/pint --test

logs: ## Tail logs from all services
	$(COMPOSE) logs -f

ps: ## List running services
	$(COMPOSE) ps

restart: ## Restart all services
	$(COMPOSE) restart

cache-clear: ## Clear application caches
	$(COMPOSE) exec app php artisan optimize:clear

optimize: ## Cache config, routes and views
	$(COMPOSE) exec app php artisan optimize

composer-install: ## Install PHP dependencies
	$(COMPOSE) exec app composer install

npm-install: ## Install Node dependencies
	$(COMPOSE) exec app npm ci

npm-build: ## Build front-end assets
	$(COMPOSE) exec app npm run build

dev: ## Start services for development
	$(COMPOSE) up -d

prod: ## Build and start services for production
	$(COMPOSE) up -d --build
