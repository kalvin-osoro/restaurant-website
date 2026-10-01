# Kalvin's Restaurant — Laravel conversion

The legacy vanilla-PHP application remains at the repository root. The Laravel replacement is in this `laravel/` directory so it can be introduced without deleting production source.

## Included domain features

- Environment-only database configuration (`.env`; copy/edit `.env.example` for another environment).
- Laravel authentication with hashed passwords, customer and admin roles, CSRF protection, validation, authorization middleware, and file upload validation.
- Normalized categories, menu items, wishlists, orders, and order lines.
- Session-backed cart, wishlist, checkout, order history, and admin menu management.
- Payment is modeled as pending. A real provider must be integrated through a signed webhook before marking a payment paid.

## Run

```bash
cd laravel
composer install
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve
```

Set a user's `role` to `admin` in the database to grant back-office access. Seed/import menu categories before using the admin item form.

## Legacy data

There was no SQL dump or reliable schema in the legacy project. Do not copy its plaintext password fields. Import food records into `categories` and `menu_items`, create users with `Hash::make()`, and reconcile old order data into `orders`/`order_items` in a one-off, reviewed migration.
