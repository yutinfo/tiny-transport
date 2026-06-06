# Guardrails

## Priority

`AGENTS.md` is the highest-priority repository instruction file. This file supplements it and does not override it.

## Security

- Never commit secrets, passwords, API keys, tokens, private credentials, or production configuration.
- Do not modify `.env` or credential files unless the user explicitly asks for that exact change.
- Validate external input at the request boundary.
- Escape rendered output in Blade unless there is a clear, reviewed reason not to.
- Avoid unsafe raw SQL and string-built query conditions.

## PHP And Docker

- Run PHP-related commands inside Docker only.
- Use `docker compose exec app php artisan ...` for artisan commands.
- Use `docker compose exec app composer ...` for Composer commands.
- Do not run host `php`, host `php artisan`, or host `composer`.

## Production Safety

- Prefer small, reviewable diffs.
- Do not perform broad rewrites, framework swaps, dependency upgrades, or architecture migrations unless explicitly requested.
- Do not change public route behavior or API contracts without documenting impact.
- Do not remove tests to make checks pass.
- Do not hide errors or weaken validation to bypass failures.

## Database Safety

Every database change needs:

- A migration.
- A rollback path in `down()`.
- A data impact note.
- An index impact note when queries may be affected.
- Docker-based validation.

## Frontend Assets

- Edit source files in `resources/sass` and `resources/js` when asset changes are needed.
- Rebuild with `npm run dev` or `npm run prod`.
- Do not hand edit generated files in `public/css`, `public/js`, or `public/mix-manifest.json`.

## Access Control

- Admin-only screens must remain protected.
- Staff/admin work routes must not become accessible to driver users.
- Driver routes must verify ownership through `trips.driver_user_id`.
- Driver trip item actions must verify the item belongs to a trip assigned to the authenticated driver.

## Verification

- Run the narrowest useful check first.
- Run broader checks when shared behavior, routes, middleware, database, or assets changed.
- Report checks that could not be run and the reason.
- Do not claim tests, builds, migrations, or manual checks passed unless they were actually run.
