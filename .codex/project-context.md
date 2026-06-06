# Project Context

## Project

Tiny Transport is a small transport and parcel management system.

Primary user workflows:

- Admin/staff login and dashboard.
- Order creation with multiple receivers.
- Contact management and contact suggestion/search APIs.
- Trip creation, trip assignment, trip start/completion/cancel.
- Driver portal for assigned trips and parcel/COD status updates.
- Parcel labels, QR tracking, parcel timeline, notification logs.
- CSV exports for trips, trip items, and COD summaries.

## Stack

- Backend: Laravel 9 on PHP 8.0+
- Auth/API: Laravel Sanctum
- Frontend build: Laravel Mix 6, Webpack 5, Sass
- UI: Blade, Bootstrap 4.6, AdminLTE 3.1, jQuery 3.6, Font Awesome 4
- Database: MySQL
- Docker: PHP 8.1 Apache app service, MySQL 8.0, Node 16 for asset builds

## Key Folders

- `app/Http/Controllers/`: web and API controllers.
- `app/Http/Requests/`: form request validation.
- `app/Models/`: Eloquent models and relationships.
- `app/Services/`: shared business logic such as trip handling and notifications.
- `routes/web.php`: login/root web routes.
- `routes/admin.php`: admin/staff web routes.
- `routes/api.php`: API routes.
- `routes/driver.php`: driver portal routes when present.
- `database/migrations/`: schema changes.
- `database/seeders/`: seed data.
- `resources/views/`: Blade templates.
- `resources/views/admin/`: AdminLTE admin screens.
- `resources/views/driver/`: mobile-first driver screens when present.
- `resources/sass/`: Sass sources for Laravel Mix.
- `public/css`, `public/js`, `public/mix-manifest.json`: generated assets.
- `tests/Feature/`: Laravel feature tests.
- `tasks/`: project task plans.

## Command Rules

All PHP-related commands must run inside Docker through the `app` service:

```bash
docker compose exec app php artisan test
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app composer install
```

Do not run host `php`, host `php artisan`, or host `composer` commands.

Frontend commands run on the host unless a task says otherwise:

```bash
npm install
npm run dev
npm run prod
```

## Route Areas

- `/login`: login page and action.
- `/logout`: logout action.
- `/admin/*`: dashboard, orders, contacts, users, trips, reports, parcel tracking, labels, exports.
- `/api/*`: location APIs and authenticated resource APIs.
- `/driver/*`: driver portal for assigned trips when driver routes are enabled.

## Driver Portal Notes

The driver portal work is planned across `tasks/task-001.md` through `tasks/task-007.md`.

Important concepts:

- `users.role_name` supports admin/staff and driver role behavior.
- `trips.driver_user_id` links a trip to the login account for the assigned driver.
- Driver routes must only show trips where `trips.driver_user_id` matches the authenticated driver.
- Driver parcel actions must guard ownership before updating `trip_items`.
- The driver UI should be mobile-first and separate from the admin sidebar layout.

## Asset Notes

Edit Sass source files under `resources/sass`. Rebuild assets with `npm run dev` or `npm run prod` when source assets change.

Do not hand edit generated files under:

```text
public/css
public/js
public/mix-manifest.json
```
