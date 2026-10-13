# Lexora Legal API

Placeholder name for the Laravel backend that will provide APIs and administration for the sibling `99lawyers` frontend. Replace “Lexora Legal” when the firm name is chosen.

This is an API-first Laravel 12 project. The starter endpoint is `GET /api/health`; add application endpoints and authentication as the backend is developed.

## Local setup

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
curl http://localhost:8000/api/health
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

## Project contents

- `app/`, `routes/`, `database/`, and `config/` are the active Laravel backend.
- `extras/legacy-php/` holds the former standalone PHP site and its assets for later cleanup.
- `extras/restaurant-laravel/` holds the archived restaurant-specific Laravel controllers, models, migrations, views, and routes.
- The sibling `99lawyers/` frontend has not been changed.

## Public attorney profiles and case studies

Run `php artisan migrate` to create the content tables. These endpoints return
database content and remain under the mandatory HTTP audit middleware:

| Endpoint | Filters / response |
| --- | --- |
| `GET /api/attorneys` | `q` (literal name/biography search), `practiceArea` (slug or name), `page`, `perPage` |
| `GET /api/attorneys/{slug}` | Published profile or 404 |
| `GET /api/practice-areas` | Ordered areas used by published attorneys |
| `GET /api/cases` | `category` (slug or name), `featured=1` or `0`, `page`, `perPage` |
| `GET /api/cases/{slug}` | Published, non-confidential study or 404 |
| `GET /api/case-categories` | Ordered categories used by public studies, including Corporate Governance |

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

Maintain content through a trusted CLI import until protected administration is
implemented. Review all biographies, qualifications, contact details, and case
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

Both frontend inquiry forms use `POST /api/consultations`. Configure the sibling
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
