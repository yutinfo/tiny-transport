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
  cancel / edit a trip. Driver picked via Select2 with live availability
  (🟢 ว่าง / 🔴 มีรอบแล้ว — warn, not block).
- **Driver management** — `drivers` master-data CRUD under `/admin/drivers`:
  optional 1:1 login account (create/link/unlink/reset-password), active toggle
  synced to the linked user, availability API, `drivers:backfill` command.
- **Driver portal** — a driver's view of assigned trips; update parcel delivery
  status and payment status.
- **Public tracking page** — `/web` (Vue 3 SPA, no auth): multi-code chip
  search, timeline per parcel, `?q=CODE1,CODE2` auto-search; backed by public
  `GET /api/track`.
- **Public landing page** — planned at `/` for guests (see
  `docs/LANDING_PAGE_PLAN.md`); logged-in users keep redirecting to their
  dashboard.
- **COD & cost tracking** — COD due, COD collected, trip cost, profit/loss.
- **Parcel labels & QR** — print parcel face labels from an order or a trip,
  with a QR for tracking.
- **Parcel tracking** — look up a parcel timeline by code; log customer
  notifications.
- **CSV export** — trips, trip items, COD summary.
- **Location API** — province / district / subdistrict for address forms.

## Route areas
- `/` — guests: public landing (planned); authed: redirect to `/admin` or
  `/driver`.
- `/web` — public parcel tracking page (Vue 3, no auth).
- `/login`, `/logout` — auth (Sanctum).
- `/admin/*` — dashboard, orders, contacts, users, drivers, trips, reports,
  parcel tracking, labels, exports. Admin/staff only; `role:admin` for writes
  on drivers/users.
- `/api/*` — location APIs + authenticated resource APIs, **plus public
  `GET /api/track`** (outside the sanctum group, capped at 10 codes).
- `/driver/*` — driver portal; **ownership-scoped** to the logged-in driver.

Route files: `routes/web.php` (root/landing/web/login), `routes/admin.php`,
`routes/api.php`, `routes/driver.php`.

## Admin list screens — server-side DataTables
The 5 big lists (orders, trips, assign-pool, trip-items, parcel search) are
jQuery DataTables fed by hand-rolled `*.data` GET endpoints (no yajra). Shared
helper: `app/Support/DataTable.php` — whitelist ordering via each column's
`db` key, global search, 200-row page cap. Pattern for any new list: add a
`*.data` route **before** the conflicting wildcard, keep page filters applied
to the base query in the controller, pre-render action cells as HTML partials
(incl. `@csrf`).

## Key tables / models
- `users` — has `role_name` (admin/staff vs driver).
- `drivers` — driver master data (code `DRV-XXXX`, name, mobile unique,
  license_plate, area, status); `drivers.user_id` = optional 1:1 link to a
  driver-role login. Busy = has a non-cancelled trip on that date.
- `orders`, `order_receives` — work orders and their receiver lines; receive
  rows carry parcel price and status columns. **COD amount lives on
  `trip_items.cod_amount`, NOT on `order_receives`.**
- `contacts` — contact directory.
- `trips`, `trip_items` — delivery rounds and the parcels assigned to them.
  `trips.driver_id` → `drivers` (current FK); `trips.driver_user_id` (legacy
  login link, still used for driver-portal ownership) + snapshot columns
  (driver_name/mobile/car_id/area_name) are kept on the trip.
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

## Public Vue 3 surface (separate from AdminLTE)
- Mix entries: `resources/js/web/` → `public/js/web.js` (tracking);
  `resources/js/landing/` → `public/js/landing.js` (landing, planned).
- Each page = standalone Blade shell + own Mix entry; no AdminLTE/jQuery there.
- **Brand rename contract:** company name comes ONLY from `config('app.name')`
  (`.env APP_NAME`) → injected as `window.__BRAND`; logo markup only in
  `BrandLogo.vue`. Never hardcode the brand name in a public page.
- Build pattern + rules: `laravel9` skill → `references/vue-public.md`.

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
- `docs/LANDING_PAGE_PLAN.md` — the approved plan for the `/` landing page
  (design system, sections, brand contract, phases). Implement from this.
- `tasks/` — task plans (the driver-portal work spans several task files).
- Plan docs are deleted once implemented and merged (repo convention).
