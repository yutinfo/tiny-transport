# CODEX.md

Codex usage guide for Tiny Transport.

## Read First

For every task in this repository, read these files before planning or editing:

1. `AGENTS.md`
2. `CODEX.md`
3. `.codex/project-context.md`
4. `.codex/guardrails.md`
5. The relevant file under `.codex/workflows/`
6. Any active task file under `tasks/` that matches the requested work

`AGENTS.md` is the highest-priority repository instruction file. The `.codex/` files supplement it and do not override it.

## Project Fit

Use Codex here for:

- Planning Laravel feature work.
- Implementing small to medium scoped changes.
- Fixing bugs after inspecting current behavior.
- Updating Blade/AdminLTE/Bootstrap UI without changing the stack.
- Writing or extending feature tests.
- Reviewing local diffs.
- Updating README and task documentation.
- Validating driver portal behavior.

## Hard Constraints

- Keep the current Laravel 9, Blade, Bootstrap 4, AdminLTE 3, jQuery, Sass, Laravel Mix, and MySQL stack.
- Do not introduce dependencies unless explicitly approved.
- Do not perform broad rewrites, framework swaps, or dependency upgrades unless explicitly requested.
- Run PHP-related commands inside Docker only, using the `app` service.
- Do not run host `php`, host `php artisan`, or host `composer` commands.
- Do not edit generated assets in `public/css`, `public/js`, or `public/mix-manifest.json` by hand.
- Do not modify `.env`, credentials, production config, or secrets unless explicitly requested.
- Do not revert unrelated user changes.

## Recommended Request Pattern

```text
Read AGENTS.md, CODEX.md, .codex/project-context.md, and .codex/guardrails.md first.
Use .codex/workflows/<workflow>.md for this task.

Task:
<describe the change>

Constraints:
- Keep the existing Laravel/Blade/Bootstrap/AdminLTE stack.
- Make small, reviewable changes.
- Use Docker for PHP commands.
- Do not edit generated public assets by hand.

Expected output:
- Short understanding
- Implementation plan
- Files changed
- Commands run
- Check results
- Risks and next step
```

## Workflow Selection

- New behavior: `.codex/workflows/feature-development.md`
- Bug fix: `.codex/workflows/bug-fix.md`
- Migration or seed change: `.codex/workflows/database-change.md`
- Admin/driver UI work: `.codex/workflows/ui-modernization.md`
- Driver role, route, assignment, mobile UI, or parcel action work: `.codex/workflows/driver-portal-change.md`
- Diff review: `.codex/workflows/code-review.md`
- Final validation: `.codex/workflows/validation-release.md`

## Useful Commands

Use the narrowest useful validation first:

```bash
docker compose exec app php artisan test
npm run dev
npm run prod
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
```

Focused driver portal tests:

```bash
docker compose exec app php artisan test --filter=DriverRoleFeatureTest
docker compose exec app php artisan test --filter=DriverTripAssignmentFeatureTest
docker compose exec app php artisan test --filter=DriverPortalAccessFeatureTest
docker compose exec app php artisan test --filter=DriverMobileViewFeatureTest
docker compose exec app php artisan test --filter=DriverParcelActionFeatureTest
```

Documentation-only validation:

```bash
find .codex -maxdepth 3 -type f -print
git diff --check
git diff --stat
```

## Final Response Format

Keep final responses short and evidence-based:

```md
Summary:
<what changed>

Files:
<important files>

Checks:
<commands run and actual results>

Risks:
<remaining risk or skipped verification>

Next:
<one useful next action>
```

Do not claim tests or builds passed unless the command was run in the current work session and exited successfully.
