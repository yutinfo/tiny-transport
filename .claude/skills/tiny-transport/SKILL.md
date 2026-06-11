---
name: tiny-transport
description: Domain map for the Tiny Transport app — what actually exists: the product (orders, contacts, trips, driver portal, COD/cost tracking, parcel labels/QR/tracking, CSV exports), the route areas (/login, /admin/*, /api/*, /driver/*), the key tables/models, the driver-portal data model, the Docker command map, and how to run/verify the app locally. Invoke whenever you need to know WHERE something lives or HOW a concept works in this codebase, before reading code. Pairs with the `laravel9` skill (which is HOW to build); this one is WHAT exists.
---

# Tiny Transport — domain map

Small transport & parcel-management system: create work orders, receive parcels,
batch them into delivery trips, track status, and reconcile COD vs delivery cost.
Built on **Laravel 9 / PHP 8.0+**, AdminLTE 3 admin UI, MySQL.

> This is the *what/where*. For *how to build* (conventions, Docker rule,
> testing), use the **`laravel9`** skill. For branching, the **`git-flow`** skill.

## Product surface
- **Orders & Order Receive** — create a transport work order; record multiple
  receivers per order; delete receive lines.
- **Contacts** — contact directory + search/suggest API for address forms.
- **Dashboard** — parcel totals, COD revenue, delivery status, trip status,
  recent activity.
- **Trips** — create a delivery round, assign parcels into it, start / complete /
  cancel / edit a trip.
- **Driver portal** — a driver's view of assigned trips; update parcel delivery
  status and payment status.
- **COD & cost tracking** — COD due, COD collected, trip cost, profit/loss.
- **Parcel labels & QR** — print parcel face labels from an order or a trip,
  with a QR for tracking.
- **Parcel tracking** — look up a parcel timeline by code; log customer
  notifications.
- **CSV export** — trips, trip items, COD summary.
- **Location API** — province / district / subdistrict for address forms.

## Route areas
- `/login`, `/logout` — auth (Sanctum).
- `/admin/*` — dashboard, orders, contacts, users, trips, reports, parcel
  tracking, labels, exports. Admin/staff only.
- `/api/*` — location APIs + authenticated resource APIs.
- `/driver/*` — driver portal; **ownership-scoped** to the logged-in driver.

Route files: `routes/web.php` (login/root), `routes/admin.php`,
`routes/api.php`, `routes/driver.php`.

## Key tables / models
- `users` — has `role_name` (admin/staff vs driver).
- `orders`, `order_receives` — work orders and their receiver lines; receive
  rows carry parcel price, COD, and status columns.
- `contacts` — contact directory.
- `trips`, `trip_items` — delivery rounds and the parcels assigned to them.
  `trips.driver_user_id` links a trip to the assigned driver's login account.
- `trip_costs` — per-trip delivery cost.
- `parcel_status_logs` — parcel timeline events.
- `parcel_notifications` — customer-notification history.
- location tables — province/district/subdistrict.

(Confirm exact columns in `database/migrations/` before relying on them.)

## Driver portal model (security-critical)
- `users.role_name` drives admin/staff vs driver behavior.
- `trips.driver_user_id` = the driver login that owns the trip.
- Driver routes must only show trips where `driver_user_id` = the authenticated
  driver. Driver actions on `trip_items` must verify the item's trip is theirs.
- Driver UI is **mobile-first** and separate from the admin sidebar layout.
- See the `laravel9` skill → `references/access-control.md` for the enforcement
  rules and required tests.

## Command map
**PHP / artisan / composer — inside Docker only:**
```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app php artisan test
docker compose exec app php artisan route:list
docker compose exec app composer install
```
**Frontend — on the host:**
```bash
npm install
npm run dev      # or: npm run watch / npm run prod
```

## Run & verify locally
- Stack runs via Docker Compose: `app` (PHP 8.1 + Apache, port `8000→80`) and
  `mysql` (8.0, host port `33306`).
  ```bash
  docker compose up -d --build
  docker compose ps
  ```
- App: http://localhost:8000 — seeded login `admin` / `password`.
- Quick smoke check:
  ```bash
  curl -s -o /dev/null -w "/login HTTP %{http_code}\n" http://localhost:8000/login
  ```
- `bootstrap/cache/{packages,services}.php` is generated; if you hit a
  "class not found" provider error after a `--no-dev` image build, clear it with
  `docker compose exec app php artisan optimize:clear`.

## Docs & plans
- `README.md` — setup, Docker, features (Thai). Update it when routes, setup,
  roles, workflows, or public behavior change.
- `tasks/` — task plans (the driver-portal work spans several task files).
