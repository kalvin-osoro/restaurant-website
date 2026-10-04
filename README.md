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

## Project contents

- `app/`, `routes/`, `database/`, and `config/` are the active Laravel backend.
- `extras/legacy-php/` holds the former standalone PHP site and its assets for later cleanup.
- `extras/restaurant-laravel/` holds the archived restaurant-specific Laravel controllers, models, migrations, views, and routes.
- The sibling `99lawyers/` frontend has not been changed.

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
