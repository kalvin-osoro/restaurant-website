# Lexora Legal API

Placeholder name for the Laravel backend that will provide APIs and administration for the sibling `99lawyers` frontend. Replace “Lexora Legal” when the firm name is chosen.

This is an API-first Laravel 12 project. The starter endpoint is `GET /api/v1/health`; add application endpoints and authentication as the backend is developed.

## Local setup

Use PHP 8.4.1 or newer for the current `composer.lock`; its locked Symfony 8
packages cannot run on PHP 8.2/8.3. The Docker image uses PHP 8.4 to match.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Set `CORS_ALLOWED_ORIGINS` in `.env` to the frontend origin when it differs from `http://localhost:3000`. Keep `.env` local and never commit credentials. If you already have a `.env`, preserve its database settings and update only the values needed for this project.

## Docker setup

Install Docker Engine (or Docker Desktop) with Docker Compose, then create a
separate container environment file:

```bash
cp .env.docker.example .env.docker
docker compose run --rm --no-deps --entrypoint php app -r 'echo "base64:" . base64_encode(random_bytes(32)) . PHP_EOL;'
```

Paste the generated value into `APP_KEY` in `.env.docker`. Keep this key stable
and backed up: consultation records are encrypted with it. To use an existing
database, use its existing key instead. Configure CORS and notification providers
in this file as needed. SMTP hosts must be reachable from the containers;
`127.0.0.1` refers to the container itself. On Linux, use a reachable SMTP hostname
or add a `host.docker.internal:host-gateway` mapping to the services when using
host-based SMTP.

```bash
docker compose up --build -d
curl http://localhost:8000/api/v1/health
docker compose ps
docker compose logs -f app queue scheduler
```

Compose starts Apache/PHP, a queue worker, and the notification scheduler. A
one-shot migration service must succeed before they start. SQLite, uploaded files,
Laravel logs, and framework storage persist in the `app-storage` named volume.
The local `.env`, local SQLite database, and installed `vendor` directory are not
copied into the image. This starts a separate database for the container setup.

The API binds to localhost by default. To change its port, run
`APP_PORT=8080 docker compose up -d` and update `APP_URL` in `.env.docker`.
For remote hosting, adjust the port binding and put HTTPS in front of the service;
set `APP_ENV=production` and configure real mail/SMS providers.

Useful maintenance commands:

```bash
docker compose exec --user www-data app php artisan consultations:dispatch
docker compose exec --user www-data app php artisan queue:retry <failed-job-uuid>
docker compose down
```

`docker compose down` preserves data. Adding `--volumes` deletes the database and
stored files. Back up the volume and `APP_KEY` together. Application source is
baked into the image; after code changes, rebuild with `docker compose up --build -d`.
Dependencies are installed from `composer.lock` without development packages.
Run the test suite locally using `php artisan test`.

## Remote API access with ngrok

The optional [compose.ngrok.yaml](compose.ngrok.yaml) overlay runs the official
ngrok agent beside the API. You need an ngrok account, its authtoken, and the
assigned development domain from the [ngrok dashboard](https://dashboard.ngrok.com/).
The image and authentication mechanism follow [ngrok's Docker setup](https://ngrok.com/download/docker).

### Docker setup

1. Complete the Docker setup above, including `.env.docker` and a stable `APP_KEY`.
2. Run `cp .env.ngrok.example .env.ngrok`. Set `NGROK_AUTHTOKEN` and set `NGROK_URL`
   to your full assigned HTTPS URL, for example `https://your-name.ngrok-free.app`.
   `.env.ngrok` is ignored by Git and excluded from Docker build context.
3. Set `CORS_ALLOWED_ORIGINS` in `.env.docker` to the exact frontend origins that
   will call the API, comma-separated, without trailing slashes. This is the
   frontend's origin, not the API's ngrok URL. Keep `APP_DEBUG=false` when sharing.
4. Start the stack with the overlay:

```bash
docker compose --env-file .env.ngrok -f compose.yaml -f compose.ngrok.yaml up --build -d
docker compose --env-file .env.ngrok -f compose.yaml -f compose.ngrok.yaml logs -f ngrok
```

The overlay requires nonempty URL/token settings, sets the API's `APP_URL` to the
public URL, disables debug output, and forwards to Apache on `app:80`. The host
port remains bound to localhost. An isolated tunnel network gives the ngrok agent
the fixed IP `172.30.79.2` and the API `172.30.79.3`, preventing address collisions;
only the agent's address is trusted for forwarded client/HTTPS
headers. If `172.30.79.0/24` conflicts with another local network, change the subnet,
agent IP, and `TRUSTED_PROXIES` together in the overlay. The local inspection
feature is disabled (`--inspect=false`) and no inspection port is published.

Use the same Compose options for subsequent lifecycle commands. Stop remote
access while keeping the API running with:

```bash
docker compose --env-file .env.ngrok -f compose.yaml -f compose.ngrok.yaml stop ngrok
```

### Host PHP setup (alternative to Docker)

Install ngrok using its [Linux installation instructions](https://ngrok.com/download/linux).
Register the authtoken with `ngrok config add-authtoken YOUR_TOKEN`. Update your
existing backend `.env` (preserve its database settings and key):

```dotenv
APP_URL=https://your-assigned-domain.ngrok-free.app
APP_DEBUG=false
TRUSTED_PROXIES=127.0.0.1,::1
CORS_ALLOWED_ORIGINS=http://localhost:3000,https://your-frontend.example.com
```

Run the API and the tunnel in separate terminals:

```bash
php artisan config:clear
php artisan serve --host=127.0.0.1 --port=8000
```

```bash
ngrok http http://127.0.0.1:8000 --url=https://your-assigned-domain.ngrok-free.app --inspect=false
```

Stop ngrok with Ctrl+C. `TRUSTED_PROXIES` is empty by default; configure only the
agent's address, not arbitrary internet clients. Proxy resolution runs before
request auditing, so audit hashes and rate limits use the remote client's IP.

### Connect and verify

Use your actual ngrok URL in these requests:

```bash
curl -H 'Accept: application/json' https://your-assigned-domain.ngrok-free.app/api/v1/health
curl -H 'Accept: application/json' https://your-assigned-domain.ngrok-free.app/api/v1/attorneys
```

Set the sibling `99lawyers` frontend's `.env` to:

```dotenv
VITE_API_BASE_URL=https://your-assigned-domain.ngrok-free.app/api/v1
```

Restart its Vite server after changing this value. The `/api/v1` suffix is required
by its existing inquiry adapter. Its other in-memory repositories still need API
adapters before those pages use remote data. Browser requests can include
`ngrok-skip-browser-warning: 1` if they encounter ngrok's free-tier interstitial;
this is a client request header, as described in [ngrok's documentation](https://ngrok.com/docs/pricing-limits/free-plan-limits).
The backend's existing CORS configuration accepts it. For example:

```javascript
const response = await fetch('https://your-assigned-domain.ngrok-free.app/api/v1/attorneys', {
  headers: { Accept: 'application/json', 'ngrok-skip-browser-warning': '1' },
});
const page = await response.json();
```

Troubleshooting: an ngrok upstream error means the local API is unavailable; check
local `/api/v1/health` first. HTTP 500 can indicate missing migrations because request
auditing requires the database. For CORS errors, check the frontend origin and clear
Laravel's configuration cache/recreate the container. HTTPS pagination links require
the trusted proxy settings above. The tunnel exposes the running application;
normal endpoint authorization and rate limits still apply. Use test inquiry data
while sharing, since consultation requests can enqueue real notifications.

## Project contents

- `app/`, `routes/`, `database/`, and `config/` are the active Laravel backend.
- `extras/legacy-php/` holds the former standalone PHP site and its assets for later cleanup.
- `extras/restaurant-laravel/` holds the archived restaurant-specific Laravel controllers, models, migrations, views, and routes.
- The sibling `99lawyers/` frontend has not been changed.

## Public attorney profiles and case studies

### Our Team page endpoint

`GET /api/v1/team` serves the published profiles needed by `99lawyers`' Team cards
and `PartnerModal`. `GET /api/v1/team/{slug}` serves a single published profile;
missing, draft, and archived profiles return 404. Both reuse the attorney
repository, application action, and public Resource used by `/api/v1/attorneys`.

```bash
curl 'http://localhost:8000/api/v1/team?perPage=20'
curl 'http://localhost:8000/api/v1/team?role=Senior%20Partner'
curl 'http://localhost:8000/api/v1/team/demo-partner'
```

The detail response has this shape (fictional example):

```json
{
  "data": {
    "id": "demo-partner",
    "name": "Demo Partner — Fictional Profile",
    "role": "Partner",
    "bio": "Reviewed public biography.",
    "practiceAreas": ["Corporate Law"],
    "imageUrl": "https://example.com/partners/demo.jpg",
    "email": "demo@example.com",
    "phone": "+1 202 555 0100",
    "education": [],
    "admissions": [],
    "notableCases": []
  }
}
```

The list returns the same profile fields in `data`, plus pagination `links` and
`meta`. Follow `links.next` until null to render every profile. Optional filters
are `q`, `practiceArea`, and `role`; valid roles are `Managing Partner`,
`Senior Partner`, `Partner`, and `Of Counsel`. Role filters also work on
`/api/v1/attorneys`. Invalid filters return 422. Profiles sort by display order then
slug and are read fresh from the database. The shared public-read rate limit and
mandatory request auditing apply to these routes too.

Images are served by the URL stored in `image_url` and exposed as `imageUrl`;
the API does not embed image bytes. Use a reachable HTTPS image host or a public
storage URL. For Laravel-managed public files, run `php artisan storage:link`
and configure `APP_URL` to the backend's public origin. Maintain profiles through
the reviewed JSON importer below; no new migration is needed for the Team routes.
The sibling frontend can consume this endpoint through its attorney repository;
its existing in-memory adapter has not been changed by this backend endpoint work.

### Directory and case-study APIs

Run `php artisan migrate` to create the content tables. These endpoints return
database content and remain under the mandatory HTTP audit middleware:

| Endpoint | Filters / response |
| --- | --- |
| `GET /api/v1/attorneys` | `q` (literal name/biography search), `practiceArea` (slug or name), `page`, `perPage` |
| `GET /api/v1/attorneys/{slug}` | Published profile or 404 |
| `GET /api/v1/practice-areas` | Ordered areas used by published attorneys |
| `GET /api/v1/cases` | `category` (slug or name), `featured=1` or `0`, `page`, `perPage` |
| `GET /api/v1/cases/{slug}` | Published, non-confidential study or 404 |
| `GET /api/v1/case-categories` | Ordered categories used by public studies, including Corporate Governance |

Lists return `{"data": [...], "links": {...}, "meta": {...}}`; detail endpoints
return `{"data": {...}}`. Taxonomy lists return `{"data": [{"id": "slug", "name": "Display name"}]}`.
Pages default to 20 records and allow up to 100. Unknown valid filters return an
empty collection; malformed filters return 422. Read endpoints share a limit of
60 requests/minute per IP. Clients must unwrap `data` and follow `links.next` to
load all records. There is no fallback to frontend fixtures in these endpoints.

Profiles expose the `Attorney` fields from the frontend in camelCase; `id` is the
public slug. Studies expose `CaseResolution` fields plus nullable `featuredPosition`.
Absent optional study fields are omitted. Attorneys sort by display order then
slug. Studies sort by featured position (featured first), display order, then slug.
`featured=1` returns only featured studies, without assuming four records exist.
Internal numeric IDs, publication states, and timestamps are not serialized.
All public attorney queries require `published`; all public case queries also
require `confidential=false`. Hidden records return 404 even by direct slug.

Maintain content through the protected POST endpoints below or the trusted CLI import. Review all biographies, qualifications, contact details, and case
summaries before setting `status` to `published`. This module stores marketing
summaries, not client files or internal legal matter notes.

```bash
php artisan website-content:import /path/to/reviewed-content.json
# Local/testing environments only; fictional examples, never automatic:
php artisan website-content:demo
# Container equivalent:
docker compose exec --user www-data app php artisan website-content:demo
```

Use [database/demo/website-content.json](database/demo/website-content.json) as the
JSON format reference. Top-level sections are `practiceAreas`, `caseCategories`,
`attorneys`, and `cases`; sections may be omitted. Category names match the current
frontend category union; category and practice-area relationships use slugs.
Lists such as education, admissions, notableCases, and keyDetails must be ordered
arrays of strings. The importer validates the entire file, then upserts records
by slug in one transaction and synchronizes attorney practice areas. Referenced
taxonomies must already exist or be supplied in the file. Reimports update the
same records; omitted records are retained. Each supplied profile/study replaces
its editable fields. Omitted status defaults to `draft`, omitted case
confidentiality defaults to `true`, and omitted featured position clears it.
To withdraw content, reimport it as `archived` (or confidential for a study).

The backend is ready for frontend API adapters. The sibling frontend still uses
`InMemoryAttorneyRepository` and `InMemoryCaseRepository`: switching those adapters,
adding loading/error/retry states, and replacing fixed featured IDs/category tabs
remain frontend integration work. Do not ship confidential frontend case fixtures.

## Consultation inquiries

Both frontend inquiry forms use `POST /api/v1/consultations`. Configure the sibling
frontend's `VITE_API_BASE_URL` (see its `.env.example`), and allow its origin through
`CORS_ALLOWED_ORIGINS`. The JSON fields are `fullName`, optional `organization`,
`email`, `phone` (E.164 international format such as `+254712345678`),
`natureOfInquiry`, `details`, and optional `preferredOffice`. A successful request
returns HTTP 201 with `id`, `referenceNumber`, `success`, and `message`. Validation
errors use HTTP 422 and Laravel's field error arrays. The endpoint allows five
requests per minute per IP; excessive requests return 429. An inquiry is received,
not a confirmed appointment: staff must arrange the consultation time.

Run `php artisan migrate` before serving requests. Names, organizations, emails,
phone numbers, and matter details are encrypted at rest using `APP_KEY`. Preserve
that key securely with backups; changing it without key rotation makes existing
records unreadable. There is no public endpoint to list inquiries.

Every HTTP request passes through `LogRequests::handle`, which inserts a record
in `tbl_request_logs`; termination records the route template, status, and duration,
including validation failures and unmatched routes. Only metadata is stored:
no request bodies, headers, raw IPs, query strings, or response bodies. IPs use a
keyed hash for audit correlation. Request logging is mandatory: a database outage
prevents normal request processing. Restrict database access and configure retention
for inquiry and audit records according to the firm's requirements.

### Notification delivery

Check the environment before starting delivery processes:

```bash
php artisan migrate
php artisan consultations:check
# In Docker, after rebuilding and migrating:
docker compose exec --user www-data app php artisan consultations:check
```

`consultations:check` checks configuration and required database tables without
sending messages or printing secrets. It fails for log-only email, missing Twilio
settings, and missing database prerequisites. A successful check does not verify
SMTP authentication, provider connectivity, running processes, or message receipt.
Authenticated SMTP needs both `MAIL_USERNAME` and `MAIL_PASSWORD`; unauthenticated
trusted relays can leave both empty. Use the provider's approved sender and TLS
settings (`MAIL_SCHEME=smtp` with STARTTLS, or `smtps` for implicit TLS).

The `cache_locks` migration is required for scheduler and worker overlap locks.
Use `CACHE_STORE=database` and `QUEUE_CONNECTION=database` for the supplied setup.
Local defaults use `MAIL_MAILER=log` and blank provider credentials; real email/SMS
requires environment-specific provider configuration.

Booking atomically creates one `notification_logs` outbox record for each enabled
channel. Start these processes to deliver notifications:

```bash
php artisan schedule:work
php artisan queue:work --tries=3 --timeout=30
```

For production, run the queue worker under a process supervisor and run Laravel's
`schedule:run` every minute via cron. Use `QUEUE_CONNECTION=database`; keep the
queue database on the same connection as the outbox so enqueue and status changes
are atomic. `php artisan consultations:dispatch` also queues pending records manually.
A provider outage does not roll back the accepted inquiry. Logs record channel,
status (`pending`, `queued`, `retrying`, `sent`, `failed`), attempt count, provider
reference, sanitized error class, and timestamps. `sent` means the provider accepted
the message, not that the recipient received it. Delivery receipts are not implemented.

Email and SMS are enabled by default. For real email, set `MAIL_MAILER=smtp` and
SMTP credentials; the default `log` mailer only writes a local confirmation. SMS
uses Twilio: configure account SID, auth token, and SMS sender. Missing credentials
produce a logged delivery failure rather than a false success. Optional WhatsApp
uses a `whatsapp:+...` sender and an approved Twilio Content template with variable
`1` set to the inquiry reference. Enable WhatsApp only for recipients whose opt-in
you have obtained; this public form does not currently collect WhatsApp consent.
Messages contain only the reference and acknowledgment, never the matter overview.

For a host deployment, [deploy/supervisor/lexora-notifications.conf](deploy/supervisor/lexora-notifications.conf)
provides queue and scheduler process definitions. Adjust the installation path,
PHP binary, and process user, then install it in your Supervisor configuration
directory. The process user needs write access to `storage` and `bootstrap/cache`.
Run `supervisorctl reread`, `supervisorctl update`, and
`supervisorctl status lexora-queue lexora-scheduler` on that host. This example
uses `schedule:work`; choose either this scheduler or the cron setup above.
Docker Compose already manages both processes, so host Supervisor is unnecessary
for a Docker deployment. After configuration changes, rebuild/recreate Docker
services, or refresh host configuration caches and restart both Supervisor
processes so long-running processes load the new settings.

Controlled delivery verification (requires approved test recipients and provider
credentials in the intended environment):

1. Configure SMTP and Twilio in that environment's secret `.env` file. Clear or
   rebuild cached Laravel configuration, then run `consultations:check` there.
2. Review pending/queued deliveries before starting a worker: it will process
   existing work as well as the test. Use an isolated development database for
   controlled verification when existing recipients must not be contacted.
3. Start the queue worker and one scheduler using Compose or Supervisor. Confirm
   both processes remain running with `docker compose ps` or `supervisorctl status`.
4. Submit one inquiry with a controlled inbox and E.164 phone number through
   `POST /api/v1/consultations`. Use harmless test details. The regular scheduler
   should queue its outbox records within a minute.
5. Use the returned reference to locate its consultation and notification logs
   through trusted database access. Confirm each enabled channel transitions to
   `sent`, with attempts and timestamps recorded; investigate sanitized error
   codes and `php artisan queue:failed` if it does not. Never publish these records.
6. Confirm receipt in the controlled inbox/device separately. `sent` proves
   provider acceptance only. Record the reference, channel outcomes, and process
   status in the deployment record; exclude recipient details and credentials.

Routine `php artisan test` verification uses mocked mail and Twilio requests.
It now also exercises the database queue worker and scheduler mutex with shared
database locks, rather than only calling the job's `handle()` method directly.

To add a platform, implement `App\Domain\Consultations\Contracts\ConsultationChannel` and register its
class and enabled flag under `config/consultations.php`'s `channels`. Laravel resolves
the driver through its container; no controller or job changes are necessary. The
contract receives an idempotency key for providers that support deduplication.
Overlapping jobs are locked and completed records are skipped. SMTP/Twilio do not
provide end-to-end deduplication here: a crash after provider acceptance but before
recording success can cause a duplicate delivery on retry.

After fixing a provider problem, use `php artisan queue:retry <failed-job-uuid>`.
First reset that delivery's `notification_logs.status` from `failed` to `retrying`
through trusted administration/Tinker; completed notifications must not be reset.
Do not reset queued records while workers are active. Protect notification config
and never expose an administration action through the public booking route.

Verification: `php artisan test` covers encrypted persistence, validation, request
auditing, rate limits, durable dispatch, mocked email/SMS, and sanitized failures.
Run `npm run lint` and `npm run build` in the sibling frontend.

## Architecture and file placement

The consultation feature follows Laravel conventions at the HTTP boundary and
separates application behavior from storage and provider implementations:

```text
app/
├── Application/Consultations/Actions/   # BookConsultation use case
├── Domain/Consultations/
│   ├── Contracts/                      # Persistence and notification interfaces
│   ├── Data/                           # Immutable inquiry, receipt, recipient data
│   └── Enums/                          # Supported inquiry categories
├── Http/
│   ├── Controllers/                    # Map validated input to actions and responses
│   ├── Requests/                       # HTTP validation and authorization
│   └── Middleware/                     # Request lifecycle and audit hooks
├── Infrastructure/
│   ├── Auditing/                       # Database request logger
│   ├── Persistence/Eloquent/
│   │   ├── Models/                     # Consultation and NotificationLog
│   │   └── Repositories/               # Atomic inquiry and outbox persistence
│   └── Notifications/
│       ├── Channels/                   # Email and Twilio adapters
│       ├── Jobs/                       # Queue delivery, retry, and logging lifecycle
│       └── Mail/                       # Laravel mailables
├── Console/Commands/                   # Outbox dispatch command
├── Models/                             # Laravel framework User/authentication model
└── Providers/                          # Bind domain contracts to infrastructure
```

Domain classes do not import Laravel or infrastructure. Application actions depend
on domain contracts; `AppServiceProvider` binds those contracts to Eloquent.
Notification drivers receive a domain recipient object, never an Eloquent model or
confidential matter overview. The job and console command orchestrate Laravel's
queue and database integration within the infrastructure boundary. HTTP audit
middleware delegates database writes to `DatabaseRequestLogger`.

Routes, configuration, database migrations, Blade mail templates, and feature tests
remain in their standard Laravel folders. Register application providers in
`bootstrap/providers.php`. The HTTP API and database table names are unchanged.

**Todo**


## Persisting website content over HTTP

All application API routes use `/api/v1`; the previous `/api` URLs are retired.
Laravel's infrastructure health probe remains `/up`. Health checks have no
persisted content and remain GET-only. Consultation submissions remain POST-only.
Update the frontend `VITE_API_BASE_URL` to `http://localhost:8000/api/v1` or your
public HTTPS origin followed by `/api/v1`, then restart/rebuild the frontend.

| POST path (relative to `/api/v1`) | Operation |
| --- | --- |
| `/team` or `/attorneys` | Create an attorney and sync practice-area relationships |
| `/team/{slug}` or `/attorneys/{slug}` | Replace an existing attorney's complete content |
| `/practice-areas` | Create a practice area |
| `/cases` | Create a case study linked to an existing category |
| `/cases/{slug}` | Replace an existing case study's complete content |
| `/case-categories` | Create a case category |

Writes accept an admin/manager OAuth2 access token or the legacy server-only
`CONTENT_WRITE_TOKEN` as a bearer credential and are limited to 30 requests per
minute per client. For the legacy integration credential: Configure a cryptographically random token of
at least 32 characters in `.env`, or `.env.docker` for Compose; generate one with
`openssl rand -hex 32`. Keep this credential on a trusted server or in an operator's
API client, never in `VITE_*` variables or browser code. Public GET requests need
no token. An unset/short configured service token disables that authentication
path with 503; missing/invalid service credentials return 401. Admin/manager
access tokens work independently of this service-token setting. Run `php artisan config:clear` after host configuration
changes, or rebuild/recreate the Compose app to pick up code and environment changes.

Request JSON uses the same camelCase record fields as the reviewed import format
above, but send **one record** directly, without the top-level import arrays.
`practiceAreas` on an attorney contains existing practice-area **slugs**;
`category` on a case contains an existing category **slug**. Create those taxonomy
records first. A profile `imageUrl` is an HTTP(S) URL to a separately hosted image;
these endpoints do not accept multipart image uploads.

```bash
curl -X POST "$API_BASE_URL/practice-areas" \
  -H "Authorization: Bearer $CONTENT_WRITE_TOKEN" \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  --data '{"slug":"corporate-law","name":"Corporate Law"}'

# partner.json contains a complete attorney record in the documented import format.
curl -X POST "$API_BASE_URL/team" \
  -H "Authorization: Bearer $CONTENT_WRITE_TOKEN" \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  --data-binary @partner.json
```

Set `API_BASE_URL` to your origin plus `/api/v1`. Successful creation returns 201
with `{"data":{"id":"the-slug"}}`; replacement returns 200 in the same shape.
Collection POSTs reject duplicate slugs/names with 422. Detail POSTs require an
existing slug matching the body and a complete record; they are not partial patches.
Invalid fields/references return 422 without persisting the record. Each write and
its relationships are transactional. Unrecognized input fields are not persisted.
Omitted `status` defaults to `draft`; cases also default to `confidential=true`.
To expose a case publicly, explicitly send both `status: "published"` and
`confidential: false`. Taxonomy GETs show only records used by public content.

Each content POST route is declared explicitly in `routes/api.php` and uses a
resource-specific controller and FormRequest: Team, Attorney, CaseStudy,
PracticeArea, or CaseCategory. Every request accepts one JSON object for that
resource; bulk arrays/import envelopes are rejected. Team and attorneys use the
same attorney-profile records. For example, `POST /api/v1/team` creates one team
profile, `POST /api/v1/attorneys` creates one attorney profile, and
`POST /api/v1/cases` creates one case study. Published records are then available
to the frontend through their corresponding GET endpoints. Shared persistence
and CLI imports still use the existing application action and repository contract.

## OAuth2 authentication, roles, and permissions

Authentication uses Laravel Passport's OAuth2 Authorization Code flow with PKCE
(S256) for browser/mobile clients. Sanctum has been removed. Previous Sanctum
access tokens no longer authenticate; users must authorize the OAuth2 client.
The former JSON `POST /api/v1/auth/login` token endpoint has been retired.
Registration creates an account only; tokens are issued by the OAuth2 server.
The historical `personal_access_tokens` migration is retained for safe upgrades,
but its tokens are never accepted. Password and implicit grants stay disabled.

| Role | Permissions |
| --- | --- |
| `user` (normal user) | `account.view` |
| `manager` | `account.view`, `content.write` |
| `admin` | `account.view`, `content.write`, `users.assign-roles` |

OAuth2 scopes use the same names, but they do not grant roles: protected operations
require both the token scope and the account permission. Role changes take effect
on existing access tokens. Public content reads and consultation submissions stay
public. The server-only `CONTENT_WRITE_TOKEN` still grants content writes only,
not account access or role assignment; remove it from deployment configuration if
all integrations have switched to OAuth2.

### Deployment

```bash
composer install
php artisan migrate
php artisan passport:keys
php artisan users:create admin@example.com --name="Administrator" --role=admin
php artisan passport:client --public --name="99lawyers" --redirect_uri="http://localhost:3000/auth/callback"
```

Use your actual frontend callback URL; it must match exactly. Save the printed
client ID as public frontend configuration, for example `VITE_OAUTH_CLIENT_ID`.
Public PKCE clients have no client secret. Configure the frontend API base URL as
before, ending in `/api/v1`. Client registration is a trusted operator action.
No clients or accounts are seeded automatically. The account command prompts for
a password rather than accepting it on the command line. Existing `customer` and
unknown legacy roles migrate to `user`; admins and managers are preserved.

Signing keys are stored in `storage/oauth-private.key` and
`storage/oauth-public.key`; keep them private, persistent, and shared by all API
replicas. Do not regenerate keys on each startup. Alternatively provide
`PASSPORT_PRIVATE_KEY` and `PASSPORT_PUBLIC_KEY` securely as multiline environment
values. No keys are included in Git or the Docker image. For Compose, rebuild the
app then run the commands inside the app container as `www-data`, e.g.:

```bash
docker compose exec --user www-data app php artisan passport:keys
docker compose exec --user www-data app php artisan passport:client --public --name="99lawyers" --redirect_uri="https://frontend.example/auth/callback"
```

The existing `app-storage` volume persists the keys. Use HTTPS and set
`SESSION_SECURE_COOKIE=true` on public deployments, along with the correct
`APP_URL`, trusted proxies, and allowed frontend CORS origin. Browser login and
consent use a same-site session with CSRF protection; API access uses bearer tokens.

### Endpoints and frontend flow

| Endpoint | Purpose |
| --- | --- |
| `POST /api/v1/auth/register` | Public; `name`, lowercase `email`, `password` (12–128 chars), `password_confirmation`; creates `user`, rejects role/permission input |
| `GET /login`, `POST /login` | Browser login form and CSRF-protected session login; not an access-token endpoint |
| `POST /logout` | CSRF-protected browser-session logout |
| `GET /api/v1/oauth/authorize` | OAuth2 authorization and user consent |
| `POST /api/v1/oauth/authorize` | CSRF-protected consent approval; handled by Passport |
| `DELETE /api/v1/oauth/authorize` | CSRF-protected consent denial; handled by Passport |
| `POST /api/v1/oauth/token` | Exchange authorization codes or refresh tokens; public PKCE client requires no client secret |
| `GET /api/v1/me` | OAuth2 bearer token with `account.view` scope |
| `POST /api/v1/auth/logout` | Revoke current OAuth2 access token and its associated refresh token |
| `POST /api/v1/users/{id}/role` | Admin with `users.assign-roles` scope; body `role`: `admin`, `manager`, or `user` |

1. Generate a cryptographically random 43–128-character `code_verifier` and a
   separate random `state`. Store both temporarily in the frontend session.
2. Compute `code_challenge = BASE64URL(SHA256(code_verifier))`, without padding.
3. Navigate the browser to the backend `/api/v1/oauth/authorize` with query
   parameters `response_type=code`, `client_id`, `redirect_uri`, `state`,
   `code_challenge`, `code_challenge_method=S256`, and space-separated `scope`.
   The default scope is `account.view`; content management also requires
   `content.write`, and the admin role screen also requires `users.assign-roles`.
4. The backend prompts for login and consent. On the frontend callback, validate
   `state` against the saved value before accepting any code. Handle denial via
   the OAuth2 `error` parameter and clear temporary state/verifier afterwards.
5. Exchange the code using the original verifier:

```bash
curl -X POST "$API_BASE_URL/oauth/token" \
  -H 'Accept: application/json' \
  --data-urlencode 'grant_type=authorization_code' \
  --data-urlencode "client_id=$OAUTH_CLIENT_ID" \
  --data-urlencode 'redirect_uri=https://frontend.example/auth/callback' \
  --data-urlencode "code=$AUTHORIZATION_CODE" \
  --data-urlencode "code_verifier=$CODE_VERIFIER"
```

The standard OAuth2 response contains `access_token`, `refresh_token`, `token_type`
and `expires_in`. Access tokens last one hour; refresh tokens last 30 days. Send
`Authorization: Bearer <access_token>` on API requests. Store tokens securely;
never put passwords, signing keys, or tokens in `VITE_*` build configuration.
Refresh with `grant_type=refresh_token`, `client_id`, and `refresh_token` at the
same token endpoint. Replace the saved refresh token with the newly returned one.
Logout revokes the current access/refresh pair; other devices remain signed in.
To also end the authorization server's browser session, submit its `/logout` form.

Invalid/expired/revoked API tokens return 401, missing scopes or role permissions
return 403, invalid application input returns 422, and throttling returns 429.
OAuth2 errors follow Passport's standard OAuth2 response format. Login allows
five attempts per minute per IP; account/content routes allow 30; OAuth routes
allow 60. Admins cannot demote themselves. Account roles are excluded from mass
assignment, and attorney job titles are unrelated to login roles.

Run `php artisan passport:purge --revoked --expired` periodically to prune obsolete
OAuth2 records. See the official Laravel Passport documentation for the protocol:
https://laravel.com/docs/12.x/passport#authorization-code-grant-with-pkce
