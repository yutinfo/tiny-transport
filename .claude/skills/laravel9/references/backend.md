# Backend — controllers, Eloquent, requests, services

## Where code goes
- Web UI routes live in `routes/web.php` (login/root), `routes/admin.php`
  (admin/staff), and `routes/driver.php` (driver portal). API routes live in
  `routes/api.php`. Put a route in the file that matches its area — never an
  admin screen in `api.php`.
- Controllers: `app/Http/Controllers/`. Keep controller changes small and
  preserve the existing request → validate → act → respond flow.
- Validation: `app/Http/Requests/` Form Request classes — use them when the
  surrounding code already does. Don't inline `$request->validate()` next to a
  controller that has a dedicated Request class.
- Shared business rules (trip handling, notifications, COD math): `app/Services/`.
  Extract to a service or model method only when duplication becomes real —
  don't pre-abstract a single caller.
- Models: `app/Models/`. Keep `$table`, `$fillable`, `$casts`, and relationships
  next to the model they describe.

## Eloquent rules
- Verify a relationship exists on the model before calling it; verify a column
  is `$fillable` before mass-assigning it.
- Don't rename a route name, table, column, or model property without grepping
  every usage first (Blade `route('...')`, `->column`, `Route::name`, JS that
  posts to a URL).
- Prefer eager loading (`with()`) over N+1 in list/dashboard queries.
- Use query-builder bindings, never string concatenation, for any user input.

## Auth / API
- Auth is **Laravel Sanctum**. API resource routes are authenticated; location
  lookup APIs (province/district/subdistrict) and contact-suggest are the read
  endpoints the forms call.
- Keep API responses in the shape existing clients (Blade pages, jQuery) already
  consume — don't reshape a payload without checking the JS that reads it.

## Lifecycle / safety
- Don't change public route behavior or an API contract without documenting the
  impact.
- Don't weaken validation or swallow errors to make something pass.
