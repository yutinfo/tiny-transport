# AGENTS.md

Guidance for Codex and other AI agents working in this repository.

## Project Snapshot

- Framework: Laravel 9 on PHP 8.0+.
- Frontend build: Laravel Mix with Webpack, Sass, Bootstrap 4, AdminLTE 3, jQuery, and Font Awesome 4.
- Main app areas: `app/`, `routes/`, `database/`, `resources/views/`, `resources/sass/`, and compiled assets in `public/`.
- Views are Blade templates under `resources/views`, with admin screens under `resources/views/admin`.

## Working Rules

- Read this file before making changes.
- Keep changes scoped to the user's request. Do not perform broad rewrites, framework swaps, or dependency upgrades unless explicitly requested.
- Preserve existing Laravel, Blade, Bootstrap/AdminLTE, Sass, and jQuery patterns unless there is a clear local reason to adjust them.
- Do not revert or overwrite unrelated work in the git tree. Treat existing uncommitted changes as user work.
- Prefer small, reviewable commits. Include this file in commits so team guidance travels with the project.
- Avoid changing generated assets in `public/css`, `public/js`, or `public/mix-manifest.json` by hand. Update source files and rebuild when asset output is needed.

## Commands

Use the commands that match the change being made:

Important for AI agents: run all PHP-related commands inside Docker only. Do not run local `php`, `php artisan`, or `composer` commands on the host machine; this project environment is expected to be available through the Docker `app` service, and host PHP commands may fail and waste time/tokens.

```bash
docker compose exec app composer install
npm install
docker compose exec app php artisan test
npm run dev
npm run prod
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
```

Docker workflow from `README.md`:

```bash
docker compose up -d --build
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose logs app
docker compose down
```

## Backend Conventions

- Keep routes in the appropriate file: web UI routes in `routes/web.php` or `routes/admin.php`, API routes in `routes/api.php`.
- Keep Eloquent relationships, fillable fields, casts, and table names close to the relevant model.
- When changing migrations, seeders, or factories, verify the intended database lifecycle: migrate, refresh, seed, or fresh setup.
- Do not rename tables, columns, routes, or model properties without checking every usage.
- Keep controllers focused on request flow. Move repeated business logic into models, services, or helpers only when duplication becomes meaningful.

## Frontend/UI Conventions

- Use existing Blade layouts and partials before creating new wrappers.
- Match AdminLTE and Bootstrap 4 markup conventions for cards, forms, tables, nav, alerts, and modals.
- Keep reusable UI fragments in Blade partials when a pattern appears in multiple places.
- Keep Sass changes in `resources/sass`. Use existing variables and shared files before adding new global styles.
- For UI refreshes, prefer incremental consistency improvements over a full visual rewrite.
- Check responsive behavior for admin pages, forms, tables, menus, and dashboards after layout changes.

## Testing And Validation

Before finishing a code change, run the narrowest useful validation first, then broader checks if risk is higher:

- PHP behavior: `docker compose exec app php artisan test`
- Frontend assets: `npm run dev` for development builds, `npm run prod` for production asset checks
- Database changes: relevant Docker-based commands such as `docker compose exec app php artisan migrate` or `docker compose exec app php artisan migrate:fresh --seed`
- Manual UI checks: login/register, admin navigation, dashboards, forms, tables, alerts, and changed pages

If a command cannot be run, report the reason and the remaining risk.

## Subagent Guidance

Use subagents only when the task is large enough to benefit from parallel analysis. Good candidates include broad UI audits, dependency reviews, migration-risk reviews, or multi-area refactors.

When using subagents:

- Define each agent's scope clearly.
- Ask analysis agents not to modify files.
- Ask them to return findings, risks, and low-risk recommendations.
- Consolidate results into one implementation plan before editing.
- Do not install packages, upgrade dependencies, or change framework versions during analysis.

Suggested analysis split:

- Tech Stack & Dependency Auditor: detect framework, package manager, scripts, UI libraries, styling method, and dependency risks.
- UI/UX Auditor: inspect major pages, layouts, forms, tables, navigation, modals, dashboards, and responsive behavior.
- Code Structure Auditor: inspect reusable components, duplicated CSS/classes, layout wrappers, shared components, and folder structure.
- Testing & Risk Auditor: identify lint/build/test commands, likely breakpoints, and a safe validation checklist.

End analysis-only work with:

```text
Please review this plan. I will not modify files until you approve.
```
