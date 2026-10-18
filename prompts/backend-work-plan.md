# Backend work plan for the 99lawyers frontend

Reviewed: 7 October 2026
Frontend: `99lawyers/`
Backend: `lexora-legal/`

## Recommended next implementation

Implement **database-backed public website content**, starting with the attorney directory and profile API. Connect the existing frontend attorney repository to that API before expanding to offices, case studies, and testimonials. This gives the next increment a complete database-to-screen outcome and establishes a reusable content pattern.

The consultation feature is already implemented; retain its API, encryption, notification outbox, request logging, and architecture. Treat its production configuration and integration gaps as follow-up work, rather than rebuilding it.

This document is an implementation plan, not authorization to execute every phase automatically.

## Findings from the current source

| Area | Current implementation | Backend gap / frontend dependency |
| --- | --- | --- |
| Consultation submissions | `ApiInquiryRepository` calls `POST /api/consultations` | Implemented; operational configuration and selected integration details remain |
| Attorneys | `InMemoryAttorneyRepository` uses `SEED_ATTORNEYS` | Team cards and partner modals need persisted profiles |
| Cases | `InMemoryCaseRepository` uses `SEED_CASES` | Home and Clients views, category filters, and case modals need publishable case studies |
| Testimonials | `InMemoryTestimonialRepository` uses `SEED_TESTIMONIALS` | Client testimonials need persisted, approved publication content |
| Offices | `InMemoryOfficeRepository` uses `SEED_OFFICES` | Contact page needs persisted locations and contact details |
| Client logos | `ClientsView` imports `CLIENT_LOGOS` directly | Add a content contract and repository; there is no current logo repository |
| Site copy | About, Home, Footer, and TrustBar contain inline content | A later content/settings phase can manage approved copy and public firm details |
| Content loading | `AppContext` loads four datasets through `Promise.all` | No loading/error state; one rejected API call would prevent every dataset from being assigned |
| Administration | A `User` model and `EnsureAdmin` middleware exist | No login or protected administration routes are currently defined |

Review references:

- `99lawyers/src/domain/models/Entities.ts`: current public data shapes.
- `99lawyers/src/domain/repositories/IRepositories.ts`: repository contracts.
- `99lawyers/src/infrastructure/container.ts`: only inquiries currently use an API repository.
- `99lawyers/src/infrastructure/data/seedData.ts`: fixture content, including cases marked `confidential: true`.
- `99lawyers/src/presentation/context/AppContext.tsx`: shared data-loading behavior.
- `99lawyers/src/presentation/components/views/ClientsView.tsx`: fixed case IDs, category tabs, and direct logo imports.
- `99lawyers/src/presentation/components/views/LeaveToView.tsx`: fixed office IDs and placeholder address fallbacks.
- `99lawyers/src/presentation/components/common/ConsultationModal.tsx`: unchecked inquiry-prefill casts.
- `lexora-legal/routes/api.php`: current health and consultation endpoints.

## Implementation status — 13 October 2026

The attorney/profile and public case-study backend is implemented: migrations,
publication-safe repositories, application actions, public Resources, paginated
read APIs, validated atomic JSON imports, and explicitly invoked local/testing
demo content. Public taxonomy endpoints show only categories/areas attached to
visible content. `website-content:demo` replaces the proposed demo seeder workflow;
it refuses production environments. Backend tests cover publication rules,
confidentiality, response contracts, filters, ordering, pagination, and imports.

Frontend integration remains open: the sibling `99lawyers` app still uses its
in-memory attorney/case adapters. The database-to-screen acceptance criteria below
are therefore pending. No live database migrations or production content imports
were performed as part of this implementation. See the backend README for setup
and import commands.

## Implementation status — 17 October 2026

Completed **Phase 0, item 1**: confirmed access to the configured local MySQL
database `99_lawyers` and applied the pending
`2026_10_13_000001_create_website_content_tables` migration (batch 2).
`php artisan migrate:status` now reports all four migrations as ran. The previous
database permission blocker did not recur. The regression suite passed with
17 tests and 186 assertions. No demo content was imported.

## Notification operations follow-up — 17 October 2026

Continued **Phase 0, item 2**. Review found that database-backed overlap locks
were required by the scheduler and queue jobs, but `cache_locks` had no migration.
Added and applied that migration to the configured local development database.
Added `consultations:check` (non-sending readiness diagnostics), a host Supervisor
example, and a controlled-delivery runbook in the README. The regression suite
passes 22 tests / 218 assertions, including real database worker execution and
scheduler mutexes with mocked providers.

Item 2 remains incomplete: local email uses `log`, and Twilio SID/token/SMS sender
are missing. The local readiness check reports those configuration gaps. The
intended deployment environment and controlled recipients must be supplied before
configuring real delivery and verifying receipt. No real messages were sent and
no delivery workers were started against the local database during this work.

## Phase 0 — Resolve existing integration and operational gaps

- [x] Confirm database access and run outstanding migrations. Completed 17 October 2026 against the configured local MySQL database; the subsequent cache-lock migration is also applied (five migrations total).
- [ ] Configure SMTP, SMS provider credentials, scheduler, and queue worker in the intended environment. Verify delivery with controlled test recipients; do not send real notifications during routine automated tests.
- [ ] Map attorney practice areas and case categories to valid inquiry categories. Examples: `M&A` → `Mergers & Acquisitions`, `Commercial Litigation` → `Commercial Dispute`. Fall back to `General Consultation` for unmapped values; remove unchecked type casts.
- [ ] Show the international phone format expected by the backend and align frontend validation with that contract.
- [ ] Correct consultation success wording: an inquiry was received, not an appointment confirmed or a message demonstrably delivered to an Executive Committee.
- [ ] Remove confidential case fixtures from the public production bundle. Merely hiding cards does not protect data shipped to a browser.

Acceptance: both inquiry entry points submit valid categories and phone numbers; failures remain visible; controlled notifications are logged correctly; production assets contain no confidential case fixtures.

## Phase 1 — Attorney directory: the next feature to build

### Persistence and publication

- [x] Create an `attorneys` table with internal primary key, unique public slug, name, role, biography, image URL, public email/phone, education, admissions, approved notable-case summaries, publication status, and display order.
- [x] Store ordered education/admission/notable-case lists as validated JSON arrays initially. Create reusable practice areas with unique slugs and an attorney/practice-area pivot, so filtering and later administration use one vocabulary.
- [x] Use `draft`, `published`, and `archived` publication states. Public list, search, and detail queries must share the same published-only rule.
- [x] Preserve existing slug IDs where appropriate. The frontend expects string IDs; expose the public slug as `id`, while keeping internal numeric IDs private.
- [x] Supply clearly labeled demo content for local/test environments via `website-content:demo` (the implemented alternative to seeders). It refuses production environments and runs only when explicitly invoked.

### Public API

| Method | Proposed endpoint | Behavior |
| --- | --- | --- |
| GET | `/api/attorneys` | Published profiles, ordered; optional validated `q` and `practiceArea` filters |
| GET | `/api/attorneys/{slug}` | Published profile; return 404 for absent, draft, or archived profiles |
| GET | `/api/practice-areas` | Public practice areas for filtering and display |

- [x] Use API Resources to explicitly expose frontend fields in camelCase, matching `Attorney` in `Entities.ts`.
- [x] Use `{ "data": [...] }` for lists and `{ "data": {...} }` for detail responses. Add `links` and `meta` when paginated; set a maximum page size and stable ordering.
- [ ] The existing frontend expects arrays: API adapters must unwrap resources and handle pagination explicitly. For the initial small directory, load all published profiles through bounded pages so the Team view does not silently show only page one.
- [x] Validate and bound search/filter inputs; use parameterized queries and escape SQL LIKE wildcards when treating search text literally.
- [x] Retain the global `tbl_request_logs` middleware and apply an appropriate public-read rate limit.

### Frontend integration

- [ ] Implement `ApiAttorneyRepository` against the existing interface and register it in `container.ts`.
- [ ] Reuse `VITE_API_BASE_URL` through a shared API client for URL handling, timeouts, and safe error mapping.
- [ ] Add loading, empty, error, and retry states for attorney content. Isolate failures so missing testimonials do not block attorney profiles.
- [ ] Confirm Team cards, Home attorney sections, partner modals, and consultation links work with API-provided IDs and arrays.

Acceptance: changing a published database profile updates the frontend; drafts never appear through list/search/detail; malformed filters are rejected; missing profiles return 404; API failure shows a retry state rather than demo data.

## Phase 2 — Offices and contact information

- [ ] Create offices with public slug, city, name, address lines, suite, postal code, public phone/email, hours, coordinates, publication state, headquarters flag, and display order.
- [ ] Add `GET /api/offices` and `GET /api/offices/{slug}`, with explicit Resources matching `OfficeLocation`.
- [ ] Implement `ApiOfficeRepository`; replace frontend assumptions about `new-york`/`london` and remove fictional address fallbacks. Use headquarters/display order and render available offices.
- [ ] If preferred office selection is introduced, persist a validated office relationship in consultations. Existing records need a migration strategy before converting the current optional string to a foreign key.

Acceptance: contact details come from published records; zero, one, or multiple offices render correctly; invalid office selection cannot create a consultation relationship.

## Phase 3 — Public case studies

- [x] Create case-study categories and case studies covering the existing `CaseResolution` fields, public slug, publication state, confidentiality flag, display order, and featured position.
- [x] Store only approved public summaries in this module. It is a marketing case-study system, not a repository for legal case files.
- [x] Add `GET /api/cases`, `GET /api/cases/{slug}`, and `GET /api/case-categories`. Validate category filters and apply stable ordering/pagination.
- [x] Public queries must require published status AND `confidential = false`, including direct detail lookups. Resources must not expose internal notes or unapproved material.
- [ ] Implement `ApiCaseRepository`; replace fixed featured case IDs and hardcoded category tabs with API content. The frontend currently omits `Corporate Governance` from its tabs even though the domain supports it.
- [ ] Add a server-defined featured order; render layouts safely when fewer than four featured studies exist. Do not silently omit additional records or categories.

Acceptance: approved public cases render on Home/Clients and in modals; confidential and draft studies are absent from all public endpoints and frontend bundles; filters and featured ordering are consistent.

## Phase 4 — Testimonials and client logos

- [ ] Persist testimonials matching `ClientTestimonial`, plus approval/publication state and display order.
- [ ] Persist client logos with public slug, name, accessible alternate text, image URL, publication approval, and display order.
- [ ] Add published-only `GET /api/testimonials` and `GET /api/client-logos` endpoints with explicit Resources.
- [ ] Add a logo entity, repository contract, use case, and API adapter; replace direct `CLIENT_LOGOS` imports. Replace the testimonial in-memory adapter too.
- [ ] Record approval for published quotes and brand assets before enabling them in production.

Acceptance: unapproved quotes/logos are excluded; ordering and alternate text come from stored content; missing images and empty collections are handled.

## Phase 5 — Protected administration and consultation follow-up

Build this once the public content contract is stable; initial content can be maintained through reviewed seed/import commands. There is currently no administration UI in the frontend.

- [ ] Implement staff authentication, logout, and current-user endpoints. Choose session-based authentication for a first-party browser administration interface and configure CSRF, cookie settings, and allowed origins accordingly.
- [ ] Add policies and explicit permissions for content management versus access to confidential consultations. `EnsureAdmin` alone is not a complete authentication flow.
- [ ] Never accept a privileged role from public registration or generic profile updates. Provision staff through a controlled command/process.
- [ ] Add protected content CRUD, publication/archive actions, relationship management, and server-side validation.
- [ ] Add a protected consultation inbox, assignment, internal notes, and defined transitions such as `received`, `in_review`, `contacted`, and `closed`. Add scheduled appointment fields only when an actual scheduling workflow is agreed.
- [ ] Keep the inquiry receipt reference separate from staff-only data; do not introduce public inquiry listing or anonymous lookup of confidential details.
- [ ] Add protected notification-status inspection and controlled retry actions with authorization and audit records.
- [ ] Record staff changes in a separate administration audit log. Keep mandatory HTTP request metadata in `tbl_request_logs`; do not put confidential bodies or credentials there.
- [ ] Add media upload handling when editors need it: bounded size, verified image types, generated filenames, restricted storage paths, and explicit public/private visibility.

Acceptance: anonymous and unauthorized access is rejected; staff cannot escalate their own role; confidential records and notes are absent from public Resources; changes and retries are attributable to a staff user.

## Phase 6 — Firm settings and editable pages

- [ ] Add a narrowly scoped public firm-settings endpoint for approved contact details, brand assets, and public links. Exclude provider credentials and other operational settings.
- [ ] Add versioned/publishable Home and About content, recognition content, and policy-page content where editorial management is needed.
- [ ] Replace inline Footer/TrustBar data and placeholder policy copy with approved content. Keep careers as an email link until a recruitment workflow is actually requested.
- [ ] Support plain text or a tightly controlled content format; do not render arbitrary stored HTML in React.
- [ ] Make footer dates and office summaries dynamic where appropriate.

Acceptance: approved edits appear consistently; private configuration is never serialized; drafts remain private; recognition claims and policy copy require content-owner review before publication.

## Architecture for all new modules

Follow the consultation feature's established separation:

```text
app/Domain/{Module}/                 # Framework-independent contracts, enums, data
app/Application/{Module}/Actions/    # Use cases depending on domain contracts
app/Infrastructure/Persistence/Eloquent/Models/
app/Infrastructure/Persistence/Eloquent/Repositories/
app/Http/Controllers/               # Thin HTTP adapters
app/Http/Requests/                  # Input validation and authorization
app/Http/Resources/                 # Explicit public and staff response fields
app/Providers/                     # Contract bindings
```

Keep migrations, configuration, commands, routes, and tests in Laravel's standard folders. Controllers must not assemble database queries. Public content Resources and staff Resources must be separate. Infrastructure details must not leak into domain contracts.

## Validation and rollout

- [x] Test published/draft/archived rules through both list and detail endpoints; cover search, filters, pagination, ordering, relationships, and confidential case exclusions.
- [ ] Test API Resources against frontend field names and types, including optional fields, arrays, string IDs, and coordinates. Attorney/case contracts are covered; office coordinates await Phase 2.
- [ ] Test authentication, authorization, privileged field rejection, and audit metadata once protected routes are added.
- [x] Use mocks for notification/provider tests; retain the existing consultation regression suite.
- [ ] Run `php artisan test`, then frontend `npm run lint` and `npm run build` for each integrated module. Perform a browser check of affected cards, modals, loading/error states, and inquiry prefills.
- [ ] Document endpoints, sample responses, configuration, seed/import commands, and operational prerequisites in the README.
- [ ] Apply migrations to a development database first. Import approved content, verify the API, switch the relevant frontend repository, then remove that module's production seed dependency.
- [ ] Keep request logging enabled for new endpoints, avoid returning confidential fields, and add explicit log/content retention rules before production rollout.

## First implementation ticket

**Title:** Serve attorney profiles from Laravel and connect the frontend directory.

**Includes:** attorney/practice-area persistence, publication filtering, read APIs, domain contracts/application actions/Eloquent repositories, public Resources, frontend API adapter and loading/error states, demo-only seeds, tests, and documentation.

**Excludes:** an administration UI, public registration, all other content modules, and a new consultation/scheduling implementation.

**Done when:** Team/Home/PartnerModal display database-backed profiles, draft profiles are inaccessible publicly, API outages are handled visibly, and backend tests plus frontend checks pass. Finish the Phase 0 category mapping alongside this ticket so profile-driven inquiry links remain valid.
