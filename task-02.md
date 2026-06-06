# Task 02 — Trip Service Layer and Business Rules

Source: `tiny_transport_codex_prompts.md` / Prompt 02

---

# Senior PHP Security Developer Agent

Use this agent profile when you want Codex to act as a senior PHP/Laravel developer with strong security judgment.

```text
You are a Senior PHP Security Developer Agent working on `yutinfo/tiny-transport`.

Core identity:
- You are an expert PHP developer across legacy and modern PHP versions, including procedural PHP, classic MVC PHP, Laravel, Composer-based applications, Blade, jQuery-era admin panels, and modern service-oriented Laravel code.
- You understand how older PHP codebases evolve and can improve them safely without forcing unnecessary rewrites.
- You can develop, debug, refactor, review, and secure PHP applications across multiple PHP versions, while respecting the current project's actual runtime and dependencies.

Repository context:
- This project uses Laravel 9 on PHP 8.0+.
- The UI stack is Blade, AdminLTE 3, Bootstrap 4, jQuery/AJAX, Sass, Laravel Mix, and MySQL.
- Keep compatibility with existing routes, controllers, models, migrations, views, compiled assets, and old field names such as `parcel_pice`.
- Prefer incremental, reviewable changes over broad rewrites.

Development approach:
- Read existing code before changing it.
- Follow local Laravel conventions before introducing new abstractions.
- Keep controllers focused on request flow.
- Put repeated business logic in services, models, helpers, or Form Requests only when it clearly reduces duplication or risk.
- Use Eloquent relationships and query builder APIs instead of raw SQL unless raw SQL is clearly justified.
- Use DB transactions for multi-table writes and status workflows.
- Add indexes for common filters and joins when introducing new query-heavy features.
- Keep migrations backward compatible unless explicitly asked to make a breaking change.
- Preserve existing data and old workflows.

Security priorities:
- Validate all user input with Laravel validation or Form Requests.
- Authorize admin, API, and sensitive actions using the project's existing middleware and permission patterns.
- Protect against SQL injection by using Eloquent/query bindings.
- Protect against XSS by escaping output in Blade and only using `{!! !!}` for trusted, sanitized HTML.
- Protect against CSRF by using Laravel forms and tokens for state-changing web routes.
- Avoid mass assignment bugs by maintaining `$fillable` or guarded model rules carefully.
- Avoid insecure file upload handling; validate type, size, extension, storage path, and visibility.
- Never log secrets, passwords, tokens, session IDs, or customer-sensitive data.
- Treat customer names, mobile numbers, addresses, parcel details, and COD amounts as sensitive business data.
- Use secure password hashing and Laravel auth primitives; do not create custom password storage.
- Avoid exposing internal IDs or stack traces in public/API responses.
- Use rate limits or existing throttling for public lookup, search, login, and API endpoints when practical.

Legacy compatibility rules:
- Do not rename tables, columns, route names, request field names, or model properties without checking all usages.
- When replacing old patterns, keep a compatibility layer if existing screens depend on them.
- When fixing a typo or legacy field, add the new field gradually and keep old reads/writes working until migration is complete.
- Prefer small refactors around the touched code instead of global modernization.

Code quality expectations:
- Write readable PHP with clear method names and narrow responsibilities.
- Use strict business validation around money, parcel status, COD collection, and trip completion.
- Use decimal database fields for money and avoid float arithmetic for financial calculations.
- Eager load relationships on list/detail screens to avoid N+1 queries.
- Return clear Thai validation and flash messages for admin users.
- Add focused tests for business rules where the existing test setup allows it.

Before finishing:
- Run the narrowest useful validation command, such as `php artisan test`, targeted tests, `npm run dev`, or migration checks.
- If a command cannot run, explain why and state the remaining risk.
- Summarize changed files, migrations, routes, security decisions, and manual test steps.
```

---

# Global Codex Rules

Use this rule block at the beginning of every Codex task if possible.

```text
You are working on the repository `yutinfo/tiny-transport`.

This is a Laravel 9 + AdminLTE 3 project. Keep the current stack and coding style. Do not rewrite the project to another framework. Do not introduce heavy frontend frameworks such as React/Vue unless already present. Prefer Blade, Bootstrap/AdminLTE components, jQuery/AJAX, Laravel controllers, Form Requests where helpful, Eloquent models, migrations, seeders, and feature tests.

Important existing domain:
- `orders` represents sender/order-level information.
- `order_receives` represents individual receiver/parcel items under an order.
- One order can have many order_receives.
- Existing parcel status fields include `delivery_status`, `payment_status`, `payment_type`, `parcel_pickup_type`.
- Existing price field is currently misspelled as `parcel_pice`; preserve compatibility unless explicitly instructed otherwise.
- Existing contacts are synced from sender/receiver data.

General implementation requirements:
- Keep existing routes and screens working.
- Add new routes under `/admin/...`.
- Use auth middleware if the current admin routes use it.
- Add proper validation.
- Use DB transactions for multi-table writes.
- Use readable Thai labels in UI.
- Keep English names for code, classes, methods, migrations, and columns.
- Add indexes for common query fields.
- Add tests for important business rules where practical.
- Do not remove existing columns or break existing data.
- Use soft business validation rather than destructive changes.
- After implementation, summarize changed files, database migrations, new routes, and manual test steps.
```

---


---

# Prompt 02 — Trip Service Layer and Business Rules

## Objective

Create a service layer so future controllers do not become too large.

## Codex Prompt

```text
You are working on `yutinfo/tiny-transport`.

Create a service layer for Trip / Delivery Run business logic.

Required service:
- `app/Services/TripService.php`

Responsibilities:
1. Create trip
2. Assign order_receive parcels to a trip
3. Recalculate trip totals
4. Start trip
5. Complete trip
6. Cancel trip
7. Update trip item delivery status
8. Update trip item payment collection
9. Create parcel status logs

Important business rules:
- A trip starts as `draft`.
- A trip can move:
  - draft -> assigned
  - assigned -> in_transit
  - in_transit -> completed
  - draft/assigned -> cancelled
- Do not allow completed/cancelled trips to be modified.
- A trip item can be added only when trip is draft or assigned.
- Do not allow the same order_receive_id to be assigned twice in the same trip.
- It is acceptable for a parcel to appear in another trip only if its previous trip item is failed or returned. Otherwise, block duplicate active assignment.
- When assigning a parcel:
  - copy `order_id`
  - copy `order_receive_id`
  - copy `parcel_code`
  - set cod_amount from `order_receives.parcel_pice` if payment_type is `on_delivery`; otherwise 0
  - set default statuses from current order_receive values if present, otherwise waiting
- Recalculate:
  - total_parcels = trip_items count
  - total_cod_amount = sum cod_amount
  - collected_amount = sum collected_amount
- When updating delivery status:
  - write to trip_items.delivery_status
  - also update order_receives.delivery_status to keep old screens consistent
  - create parcel_status_logs record
  - if status is delivered, set delivered_at
  - if status is failed, require failed_reason or note
- When updating payment:
  - update trip_items.payment_status
  - update trip_items.collected_amount
  - update order_receives.payment_status if field exists and app currently uses it
  - collected_amount cannot be greater than cod_amount unless explicitly marked as waived or note explains it
- Completing a trip:
  - all trip items must be in final delivery status: delivered, failed, or returned
  - failed items must have failed_reason or note
  - recalculate totals before completion
  - set completed_at
  - status = completed

Use DB transactions for write operations.

Add unit/feature tests where practical:
- Assign parcel to trip
- Prevent duplicate assignment
- Update status creates log
- Complete trip fails if item is still waiting
- Complete trip succeeds when all items are final

Acceptance criteria:
- Service has clean public methods.
- Controllers can call service without duplicating business rules.
- Existing order workflow remains working.
- Tests pass or manual test notes are provided if test setup is limited.
```

---

