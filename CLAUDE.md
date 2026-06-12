# CLAUDE.md

Guidance for **Claude Code** working in the Tiny Transport repository.

## Project snapshot
- **Tiny Transport** — small transport & parcel-management system: orders,
  contacts, delivery trips, driver management + driver portal, COD/cost
  tracking, parcel labels/QR/tracking, CSV exports, and public-facing pages
  (parcel tracking at `/web`, company landing at `/`).
- **Stack:** Laravel 9 on PHP 8.0+, Laravel Sanctum, Blade + AdminLTE 3.1 /
  Bootstrap 4.6 / jQuery 3.6 for admin/driver screens, **Vue 3 SPAs for public
  pages** (separate Mix entries), Laravel Mix 6 (Webpack 5, Sass), MySQL.
- **Runs on Docker Compose:** `app` (PHP 8.1 + Apache, http://localhost:8000) and
  `mysql` (8.0, host port `33306`). Seeded login: `admin` / `password`.

## Golden rules
1. **All PHP runs inside Docker.** Never host `php` / `php artisan` / `composer`.
   Use `docker compose exec app php artisan …` / `docker compose exec app composer …`.
   Frontend `npm` runs on the **host**.
2. **Never hand-edit generated assets** (`public/css/app.css`, any Mix-built
   `public/js/*.js` — `app.js`, `web.js`, `landing.js` — and
   `public/mix-manifest.json`) — edit `resources/`, rebuild with `npm run dev`/`prod`.
3. **Read before you write**; make the smallest correct diff; don't rename
   routes/tables/columns without grepping every usage.
4. **Driver access control is the #1 risk** — driver routes must scope to
   `trips.driver_user_id`, and `trip_items` actions must verify ownership.
5. Keep changes scoped to the request. No broad rewrites or dependency upgrades
   unless explicitly asked. Commit/push only when the user asks.

## Where the Claude assets live (`.claude/`)
- **Skills** (`.claude/skills/`) — invoke with the Skill tool:
  - `laravel9` — *how to build*: version-locked core rules + a router to
    `references/` (`backend`, `database`, `frontend`, `testing`, `access-control`).
  - `tiny-transport` — *what exists*: the domain map (product, routes, tables,
    driver model, command map, run/verify).
  - `git-flow` — the branching standard (base branch `main`).
- **Agents** (`.claude/agents/`) — `laravel-developer`, `laravel-code-reviewer`,
  `laravel-debugger`, `laravel-ui-tester`.
- **Commands** (`.claude/commands/`) — `/laravel-developer`, `/laravel-code-review`,
  `/laravel-debug`, `/ui-test`.

For any non-trivial task, invoke the `laravel9` + `tiny-transport` skills first.

## Boundary with Codex (do not cross)
This `.claude/` setup is **separate** from the Codex setup and must not change it.
Codex owns: `AGENTS.md`, `CODEX.md`, and everything under `.codex/`. Those files
remain the source of truth for Codex and for repository-wide rules — `AGENTS.md`
is the highest-priority instruction file in this repo and this guide supplements
it, it does not override it. When editing AI tooling, keep Claude changes inside
`.claude/` and `CLAUDE.md`; leave `.codex/`, `AGENTS.md`, and `CODEX.md` untouched
unless the user explicitly asks.
