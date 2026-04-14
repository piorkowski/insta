.PHONY: start stop reset tests check-quality

start:
	docker compose up -d --build
	@echo "Waiting for databases to be ready..."
	@until docker compose exec symfony-db pg_isready -U postgres > /dev/null 2>&1; do sleep 1; done
	@until docker compose exec phoenix-db pg_isready -U postgres > /dev/null 2>&1; do sleep 1; done
	docker compose exec symfony php bin/console doctrine:migrations:migrate --no-interaction
	docker compose exec symfony php bin/console app:seed || true
	docker compose exec symfony php bin/console assets:install public
	docker compose exec symfony php bin/console cache:clear
	docker compose exec phoenix mix ecto.migrate
	docker compose exec phoenix mix run priv/repo/seeds.exs || true
	@echo ""
	@echo "Application is ready:"
	@echo "  Symfony App: http://127.0.0.1:8000"
	@echo "  Phoenix API: http://127.0.0.1:4000"

stop:
	docker compose down

reset:
	docker compose down -v
	$(MAKE) start

tests:
	@echo "=== Preparing test database ==="
	docker compose exec symfony-db psql -U postgres -tc "SELECT 1 FROM pg_database WHERE datname = 'symfony_app_test'" | grep -q 1 || docker compose exec symfony-db psql -U postgres -c "CREATE DATABASE symfony_app_test"
	docker compose exec symfony php bin/console doctrine:migrations:migrate --no-interaction --env=test
	@echo ""
	@echo "=== PHPUnit ==="
	docker compose exec symfony vendor/bin/phpunit
	@echo ""
	@echo "=== Phoenix tests ==="
	docker compose exec -e MIX_ENV=test -e DB_HOST=phoenix-db phoenix mix ecto.create --quiet || true
	docker compose exec -e MIX_ENV=test -e DB_HOST=phoenix-db phoenix mix ecto.migrate --quiet
	docker compose exec -e MIX_ENV=test -e DB_HOST=phoenix-db phoenix mix test

check-quality:
	@echo "=== PHPStan ==="
	docker compose exec symfony vendor/bin/phpstan analyse --memory-limit=512M
	@echo ""
	@echo "=== CS-Fixer ==="
	docker compose exec symfony vendor/bin/php-cs-fixer fix --dry-run --diff
