# Lexora Legal API

Laravel backend for the `99lawyers` frontend. API URLs use `/api/v1`.
For API contracts, OAuth2 flows, permissions, and architecture, see the
[implementation documentation](docs/implementation.md).

## Local setup

Requirements: PHP 8.4.1 or newer, Composer, and a supported database. The supplied
Docker setup uses PHP 8.4 and SQLite.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve --host=127.0.0.1 --port=8000
```

If `.env` already exists, preserve it and its `APP_KEY` and database settings.
Configure your database before migrating. Set `CORS_ALLOWED_ORIGINS` to the exact
frontend origins, comma-separated without trailing slashes; the default is
`http://localhost:3000`. Keep environment files and credentials out of Git.

Verify the service:

```bash
curl http://localhost:8000/api/v1/health
```

Complete [OAuth2 setup](#oauth2-setup) before using authenticated endpoints.

## Docker setup

Install Docker Engine or Docker Desktop with Docker Compose. Create the container
environment and generate an application key:

```bash
cp .env.docker.example .env.docker
docker compose run --rm --no-deps --entrypoint php app -r 'echo "base64:" . base64_encode(random_bytes(32)) . PHP_EOL;'
```

Paste the generated value into `APP_KEY` in `.env.docker`. For an existing
database, retain its existing key. Back up this key with the database; changing it
can make encrypted consultation records unreadable. Configure CORS and any
notification providers in `.env.docker`.

```bash
docker compose up --build -d
curl http://localhost:8000/api/v1/health
docker compose ps
docker compose logs -f app queue scheduler
```

Compose automatically applies pending migrations before starting the API, queue
worker, and scheduler, then prints migration status. Already applied migrations
are skipped. After adding migration files, run `docker compose up --build -d`
(or the ngrok command below); no manual migration command is needed. Inspect the
result with `docker compose logs migrate`. Container data is
separate from the host database and persists in the `app-storage` volume. The API
is available at `http://localhost:8000`. To change the port, set `APP_PORT` and
`APP_URL` in `.env.docker`, then run `APP_PORT=8080 docker compose up -d`.

Rebuild after code or dependency changes with `docker compose up --build -d`.
Run Artisan commands inside the container with:

```bash
docker compose exec --user www-data app php artisan <command>
```

Stop the stack with `docker compose down`. Data is retained unless you add
`--volumes`, which deletes the database and stored files. Complete the OAuth2
setup below inside the app container for Docker deployments.

## OAuth2 setup

After migrating, generate signing keys once, create the first administrator, and
register the frontend's public OAuth2 client:

```bash
php artisan passport:keys
php artisan users:create admin@example.com --name="Administrator" --role=admin
php artisan passport:client --public --name="99lawyers" --redirect_uri="http://localhost:3000/auth/callback"
```

The account command prompts for a password. Use your frontend's actual callback
URL; it must match exactly. Store the printed client ID in frontend configuration
such as `VITE_OAUTH_CLIENT_ID`. Public clients have no client secret.

For Docker, use the same commands through Compose, for example:

```bash
docker compose exec --user www-data app php artisan passport:keys
docker compose exec --user www-data app php artisan users:create admin@example.com --name="Administrator" --role=admin
docker compose exec --user www-data app php artisan passport:client --public --name="99lawyers" --redirect_uri="http://localhost:3000/auth/callback"
```

Keep `storage/oauth-private.key` and `storage/oauth-public.key` persistent and
shared across API replicas. Do not regenerate them at each startup. The Docker
storage volume retains them. Alternatively configure `PASSPORT_PRIVATE_KEY` and
`PASSPORT_PUBLIC_KEY` securely as multiline environment values.

See the [OAuth2 integration reference](docs/implementation.md#oauth2-authentication-roles-and-permissions)
for login, consent, token exchange, refresh, scopes, and role assignment.

## Notification workers

Local defaults log email and have blank provider credentials. To deliver real
notifications, configure SMTP and Twilio in the environment used by the service.
Container SMTP hosts must be reachable from containers; `127.0.0.1` refers to the
container itself. Use a reachable SMTP hostname, or configure a
`host.docker.internal:host-gateway` mapping for host-based SMTP on Linux.

Verify configuration before starting delivery:

```bash
php artisan consultations:check
```

For host development, run these in separate terminals:

```bash
php artisan schedule:work
php artisan queue:work --tries=3 --timeout=30
```

Compose already runs both processes. For a production host, configure a process
supervisor and scheduler using [the notification operations reference](docs/implementation.md#notification-delivery)
and [Supervisor example](deploy/supervisor/lexora-notifications.conf).
Use `CACHE_STORE=database` and `QUEUE_CONNECTION=database` for the supplied setup.

## Remote access with ngrok

### Docker

Complete Docker setup first. Copy the ngrok environment example:

```bash
cp .env.ngrok.example .env.ngrokenv
```

Set `NGROK_AUTHTOKEN` and `NGROK_URL` to your full assigned HTTPS URL. In
`.env.docker`, set `CORS_ALLOWED_ORIGINS` to the frontend origins, keep
`APP_DEBUG=false`, and set `SESSION_SECURE_COOKIE=true` when using public HTTPS.

```bash
docker compose --env-file .env.ngrok -f compose.yaml -f compose.ngrok.yaml up --build -d
docker compose --env-file .env.ngrok -f compose.yaml -f compose.ngrok.yaml logs -f ngrok
```

Use the same Compose options for later commands. To stop only the tunnel:

```bash
docker compose --env-file .env.ngrok -f compose.yaml -f compose.ngrok.yaml stop ngrok
```

### Host PHP

Install ngrok and register its authtoken with
`ngrok config add-authtoken YOUR_TOKEN`. Update the host `.env`:

```dotenv
APP_URL=https://your-assigned-domain.ngrok-free.app
APP_DEBUG=false
TRUSTED_PROXIES=127.0.0.1,::1
CORS_ALLOWED_ORIGINS=http://localhost:3000,https://your-frontend.example.com
SESSION_SECURE_COOKIE=true
```

Run the API and tunnel in separate terminals:

```bash
php artisan config:clear
php artisan serve --host=127.0.0.1 --port=8000
```

```bash
ngrok http http://127.0.0.1:8000 --url=https://your-assigned-domain.ngrok-free.app --inspect=false
```

Verify using your assigned public URL:

```bash
curl -H 'Accept: application/json' https://your-assigned-domain.ngrok-free.app/api/v1/health
```

Set the frontend's `VITE_API_BASE_URL` to the backend origin followed by `/api/v1`,
then restart/rebuild the frontend. Register its callback URL during OAuth2 setup.
See [tunnel networking](docs/implementation.md#tunnel-networking) for proxy details
and ngrok request headers.

## Maintenance and verification

```bash
php artisan test
php artisan consultations:dispatch
php artisan queue:failed
php artisan passport:purge --revoked --expired
```

After host configuration changes, run `php artisan config:clear` and restart
long-running workers. For Docker, rebuild/recreate the services. Configure HTTPS,
`APP_ENV=production`, `APP_DEBUG=false`, and secure session cookies for public hosting.

If the health endpoint fails, check logs and migration status first. For ngrok
upstream errors, verify local health before the public URL. For CORS errors, check
the exact frontend origin. For notification failures and safe retry procedures,
see [notification delivery](docs/implementation.md#notification-delivery).

For optional development content and reviewed imports, see the
[content reference](docs/implementation.md#directory-and-case-study-apis).
Future implementation details belong in [docs/implementation.md](docs/implementation.md);
keep this README focused on setup, running, and operational commands.
