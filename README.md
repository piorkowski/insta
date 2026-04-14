## Architektura

Ten projekt składa się z dwóch oddzielnych aplikacji z własnymi bazami danych:

- **Symfony App** (port 8000): Główna aplikacja internetowa
  - Baza danych: `symfony-db` (PostgreSQL, port 5432)
  - Nazwa bazy danych: `symfony_app`
  - Redis (cache, port 6379)

- **Phoenix API** (port 4000): Mikroserwis REST API
  - Baza danych: `phoenix-db` (PostgreSQL, port 5433)
  - Nazwa bazy danych: `phoenix_api`

## Szybki start

```bash
make start
```

Aplikacja będzie dostępna pod:
- Symfony App: http://127.0.0.1:8000
- Phoenix API: http://127.0.0.1:4000

### Inne komendy

```bash
make stop    # Zatrzymanie kontenerów
make reset   # Pełny reset (usunięcie danych + ponowne postawienie)
```

## Uruchamianie testów

### Symfony (PHP)
```bash
docker compose exec symfony php bin/phpunit
```

### PHPStan
```bash
docker compose exec symfony vendor/bin/phpstan analyse --memory-limit=512M
```

### CS-Fixer
```bash
docker compose exec symfony vendor/bin/php-cs-fixer fix --dry-run --diff
```

### Phoenix (Elixir)
```bash
docker compose exec phoenix mix test
```

## Dokumentacja

Szczegółowy opis decyzji architektonicznych, wprowadzonych zmian i napotkanych problemów znajduje się w [docs/NOTES.md](docs/NOTES.md).
