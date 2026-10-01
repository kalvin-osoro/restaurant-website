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
