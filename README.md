# Lexora Legal API

Laravel backend for the `99lawyers` frontend. API URLs use `/api/v1`.
For API contracts, architecture, and operational guides, see the
[implementation documentation](docs/implementation.md).

## Local setup

Requirements: PHP 8.4.1 or newer, Composer, and a supported database. The supplied
Docker setup uses PHP 8.4 and the database configured in `.env` (PostgreSQL by default).

```bash
composer install
```

For first-time setup, create `.env` and generate the application key:

```bash
cp .env.example .env
php artisan key:generate
```

If `.env` already exists, preserve it and its `APP_KEY` and database settings.
Configure your database before migrating. Set `CORS_ALLOWED_ORIGINS` to the exact
frontend origins, comma-separated without trailing slashes; the default is
`http://localhost:3000`. Keep environment files and credentials out of Git.

Run the migrations and start the server:

```bash
php artisan migrate
php artisan serve --host=127.0.0.1 --port=8000
```

Verify the service from another terminal:

```bash
curl http://localhost:8000/api/v1/health
```

For protected endpoints, complete [OAuth2 setup](docs/implementation.md#oauth2-setup).
For an existing database that still uses the old table names, follow the
[database upgrade instructions](docs/implementation.md#database-table-naming)
before starting the updated application.

## Docker setup

Install Docker Engine or Docker Desktop with Docker Compose. For first-time
setup, create `.env` and print an application key from the app container:

```bash
cp .env.example .env
docker compose run --rm --no-deps --build --entrypoint php app artisan key:generate --show
```

Copy the printed value into `APP_KEY` in `.env`. If `.env` already exists with
an application key, preserve that key and its database settings. Compose
uses the same `.env` as host PHP. Configure PostgreSQL and CORS in that file.
PostgreSQL must already be running and `DB_HOST` must be reachable from the
containers.

```bash
docker compose up --build -d
docker compose ps
curl http://localhost:8000/api/v1/health
```

Compose applies pending migrations before starting the API, queue worker, and
scheduler. To change the published port, set `APP_PORT` and `APP_URL` in `.env`
before starting the stack.

### Local PostgreSQL on Linux

If PostgreSQL listens only on the host loopback address, keep
`DB_CONNECTION=pgsql` and the existing credentials in `.env`. With Docker Compose
2.24.4 or newer, start the host-network overlay:

```bash
docker compose -f compose.yaml -f compose.postgres-host.yaml up --build -d
curl http://127.0.0.1:8000/api/v1/health
```
