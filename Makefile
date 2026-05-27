# ─────────────────────────────────────────────────────────────────────────────
# Makefile — Laravel Tenancy Docker Helper
# Usage: make <command>
# ─────────────────────────────────────────────────────────────────────────────

DC = docker compose

.PHONY: help build up down restart logs logs-app logs-node logs-nginx logs-queue \
        shell shell-nginx shell-mysql shell-redis shell-node \
        artisan tinker migrate fresh seed seed-tenant migrate-tenant migrate-fresh-tenant \
        npm-install npm-dev npm-build npm-add composer-install composer-update composer-add \
        key-gen cache-clear route-list config-clear view-clear \
        queue-restart queue-work queue-failed queue-retry \
        test test-filter pint stan \
        ps stats prune setup status

# ─── Help ─────────────────────────────────────────────────────────────────────
help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-22s\033[0m %s\n", $$1, $$2}'

# ─── Docker ───────────────────────────────────────────────────────────────────
build: ## Build all containers (no cache)
	$(DC) build --no-cache

up: ## Start all containers (detached)
	$(DC) up -d

down: ## Stop and remove containers
	$(DC) down

restart: ## Restart all containers
	$(DC) restart

ps: ## Show running containers status
	$(DC) ps

stats: ## Show container resource usage
	$(DC) stats

status: ## Show all container health/status
	@$(DC) ps --format "table {{.Service}}\t{{.State}}\t{{.Status}}\t{{.Ports}}"

prune: ## Remove all stopped containers + unused images
	docker system prune -af --volumes

# ─── Logs ─────────────────────────────────────────────────────────────────────
logs: ## Follow logs (all services)
	$(DC) logs -f

logs-app: ## Follow app logs only
	$(DC) logs -f app

logs-node: ## Follow node/vite logs
	$(DC) logs -f node

logs-nginx: ## Follow nginx logs
	$(DC) logs -f nginx

logs-queue: ## Follow queue worker logs
	$(DC) logs -f queue

# ─── Shells ───────────────────────────────────────────────────────────────────
shell: ## Open bash inside app container
	$(DC) exec app bash

shell-nginx: ## Open shell in nginx
	$(DC) exec nginx sh

shell-mysql: ## Open MySQL CLI
	$(DC) exec mysql mysql -u laravel -psecret laravel

shell-redis: ## Open Redis CLI
	$(DC) exec redis redis-cli -a redissecret

shell-node: ## Open shell in node container
	$(DC) exec node sh

# ─── Laravel Artisan ──────────────────────────────────────────────────────────
artisan: ## Run artisan: make artisan CMD="route:list"
	$(DC) exec app php artisan $(CMD)

tinker: ## Open Laravel Tinker REPL
	$(DC) exec app php artisan tinker

key-gen: ## Generate APP_KEY
	$(DC) exec app php artisan key:generate

route-list: ## List all routes
	$(DC) exec app php artisan route:list

# ─── Migrations / Seed ────────────────────────────────────────────────────────
migrate: ## Run migrations
	$(DC) exec app php artisan migrate

migrate-tenant: ## Run tenant migrations
	$(DC) exec app php artisan tenants:migrate

fresh: ## Fresh migrate + seed (WARNING: drops all tables)
	$(DC) exec app php artisan migrate:fresh --seed

migrate-fresh-tenant: ## Fresh migrate tenants (WARNING: drops tenant tables)
	$(DC) exec app php artisan tenants:migrate-fresh --seed

seed: ## Run main seeders
	$(DC) exec app php artisan db:seed

seed-tenant: ## Run tenant seeders
	$(DC) exec app php artisan tenants:seed

# ─── Cache ────────────────────────────────────────────────────────────────────
cache-clear: ## Clear all caches
	$(DC) exec app php artisan optimize:clear

config-clear: ## Clear config cache only
	$(DC) exec app php artisan config:clear

view-clear: ## Clear compiled views only
	$(DC) exec app php artisan view:clear

optimize: ## Optimize for production
	$(DC) exec app php artisan optimize

# ─── Queue ────────────────────────────────────────────────────────────────────
queue-restart: ## Restart queue workers
	$(DC) exec app php artisan queue:restart

queue-work: ## Run queue worker manually (foreground)
	$(DC) exec app php artisan queue:work redis --verbose

queue-failed: ## List failed jobs
	$(DC) exec app php artisan queue:failed

queue-retry: ## Retry all failed jobs
	$(DC) exec app php artisan queue:retry all

# ─── Testing / Quality ────────────────────────────────────────────────────────
test: ## Run all tests
	$(DC) exec app php artisan test

test-filter: ## Run filtered test: make test-filter FILTER="BillingTest"
	$(DC) exec app php artisan test --filter=$(FILTER)

pint: ## Run Laravel Pint (code style fix)
	$(DC) exec app ./vendor/bin/pint

stan: ## Run PHPStan static analysis
	$(DC) exec app ./vendor/bin/phpstan analyse

# ─── Composer ─────────────────────────────────────────────────────────────────
composer-install: ## Run composer install
	$(DC) exec app composer install

composer-update: ## Run composer update
	$(DC) exec app composer update

composer-add: ## Add a composer package: make composer-add PKG="laravel/horizon"
	$(DC) exec app composer require $(PKG)

# ─── Node / Vite ──────────────────────────────────────────────────────────────
npm-install: ## Install node dependencies
	$(DC) exec node npm install

npm-dev: ## Start Vite dev server
	$(DC) exec node npm run dev

npm-build: ## Build assets for production
	$(DC) exec node npm run build

npm-add: ## Add a npm package: make npm-add PKG="apexcharts"
	$(DC) exec node npm install $(PKG)

# ─── First-time Setup ─────────────────────────────────────────────────────────
setup: ## Full first-time project setup
	@cp -n .env.example .env || true
	$(DC) build
	$(DC) up -d
	@echo "Waiting for MySQL to be ready..."
	@sleep 10
	$(DC) exec app composer install
	$(DC) exec app php artisan key:generate
	$(DC) exec app php artisan migrate
	$(DC) exec node npm install
	@echo ""
	@echo "✅ Setup complete!"
	@echo "   App        → http://localhost:8000"
	@echo "   phpMyAdmin → http://localhost:8080"
	@echo "   Vite HMR   → http://localhost:5173"
