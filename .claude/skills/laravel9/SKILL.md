---
name: laravel9
description: Complete Laravel 9 development suite for the Tiny Transport app — version-locked mindset (Laravel 9 / PHP 8.0+), the Docker-only command rule, Eloquent/migration/request-validation conventions, Blade + AdminLTE 3 / Bootstrap 4 UI rules, Sanctum auth, Laravel Mix asset pipeline, feature testing, and the project's access-control rules. ALWAYS use this skill for ANY task touching this codebase — reading a task plan, writing or reviewing PHP/Blade, designing a feature, writing tests, changing the schema, or answering a "how does X work here" question — even if the user doesn't say "Laravel" explicitly.
---

# Laravel 9 Development Suite (router)

This file is intentionally small. **Read it, apply the core rules below, then
load ONLY the reference(s) the task needs.** For deep project facts (routes,
models, driver portal) invoke the **`tiny-transport`** skill. For branching,
invoke the **`git-flow`** skill.

## Core rules (always active, no reference needed)

1. **Target is Laravel 9 on PHP 8.0.2+. Always.** Do not introduce Laravel 10/11
   idioms (no `bootstrap/app.php` slimming, no `casts()` method, no first-class
   route files beyond what exists, no `Number`/`Context` helpers). Match what
   `composer.json` actually pins. If a snippet from memory targets a newer
   version, flag it once and write the L9 form.

2. **All PHP runs inside Docker.** The app + MySQL run as Compose services
   (`app`, `mysql`). Never run host `php`, host `php artisan`, or host
   `composer`. Use:
   ```bash
   docker compose exec app php artisan <cmd>
   docker compose exec app composer <cmd>
   docker compose exec app ./vendor/bin/phpunit
   ```
   Frontend (`npm`) runs on the **host**. See `tiny-transport` skill for the
   full command map.

3. **Never hand-edit generated assets.** `public/css/app.css`, every Mix-built
   `public/js/*.js` (`app.js`, `web.js`, `landing.js`, …), and
   `public/mix-manifest.json` are build output. Edit `resources/sass/*` and
   `resources/js/*`, then rebuild with `npm run dev` / `npm run prod` on the host.

4. **Read before you write.** Open the real controller/model/migration/Blade and
   verify the route name, column, relationship, or helper exists before you use
   it. Never invent a column, route name, model method, or config key.

5. **Smallest correct diff.** Preserve the existing request flow, Blade layout,
   and jQuery behavior. Don't rename routes/tables/columns/model properties
   without checking every usage (`grep` route names, `->column`, `Route::`).

6. **Validate at the boundary, escape at the edge.** Use Form Request classes
   where the pattern exists; keep Blade output escaped (`{{ }}`, not `{!! !!}`)
   unless there is a reviewed reason. No string-built SQL — use the query
   builder / bindings.

## Routing table — load what the task needs

| If the task involves…                                   | Read this reference            |
|---------------------------------------------------------|--------------------------------|
| Controllers, Eloquent, Form Requests, services, the request lifecycle | `references/backend.md` |
| Migrations, columns, indexes, seeders, rollback safety  | `references/database.md`       |
| Blade, AdminLTE 3 / Bootstrap 4 markup, Sass, jQuery, the asset pipeline | `references/frontend.md` |
| Public Vue 3 pages (`/web`, the `/` landing), standalone shells, brand contract, public APIs | `references/vue-public.md` |
| Feature tests, RefreshDatabase, what/how to assert      | `references/testing.md`        |
| Auth, roles, driver-vs-admin access, ownership guards   | `references/access-control.md` |

For domain facts (which routes/models exist, the driver portal model,
COD/trip concepts), invoke the **`tiny-transport`** skill instead — this skill
is *how to build*, that skill is *what exists*.

## Definition of done

- The narrowest useful check ran and passed (focused feature test, route list,
  or a manual `curl` against `http://localhost:8000`), broadened only if shared
  behavior/routes/middleware/schema/assets changed.
- DB changes have a migration with a working `down()`, plus a data + index
  impact note.
- No generated asset hand-edited; rebuilt from source if assets changed.
- Report what changed with `file:line`, and name any check you could not run.
