# Codex AI Agent Structure Design

## Goal

Add a repository-local AI working structure that helps Codex and other agents plan, implement, test, review, and document changes safely in this Laravel 9 transport management project.

## Context

Tiny Transport is a Laravel 9 application with Blade views, Bootstrap 4, AdminLTE 3, jQuery, Sass, Laravel Mix, MySQL, and Docker. The repo already has a root `AGENTS.md` with important project rules, including the requirement that all PHP-related commands run inside Docker through the `app` service.

The repo does not currently have a `.codex/` directory. The new structure should add reusable instructions and workflows around the existing application without changing runtime code, routes, database schema, compiled assets, dependencies, or deployment behavior.

## Design Summary

Create a lightweight AI playbook in the repository:

```text
AGENTS.md
CODEX.md
.codex/
  project-context.md
  conventions.md
  guardrails.md
  workflows/
  agents/
  skills/
  prompts/
  examples/
```

`AGENTS.md` remains the source of truth for broad project rules. `CODEX.md` becomes a Codex-facing usage guide. Files under `.codex/` provide reusable context, role definitions, workflows, skills, prompts, and examples tailored to this project.

## Architecture

The structure is documentation-first and repo-local. It does not rely on a package, service, or runtime integration. Any Codex session can read `AGENTS.md`, `CODEX.md`, and the relevant `.codex/` files before starting work.

The intended workflow is:

```text
Planner -> Implementer -> Tester -> Reviewer -> Summary
```

Each role is documented as a reusable prompt/reference file rather than a separate executable system. This keeps the setup portable across Codex app, CLI, IDE usage, and other AI agents that can read markdown files.

## Files To Add

### `CODEX.md`

Purpose: entry guide for using Codex on this repo.

Content:

- Which files Codex should read first.
- How to frame requests.
- Project-specific constraints.
- Expected final response format.
- Safe validation commands.
- Examples for common repo tasks.

### `.codex/project-context.md`

Purpose: compact project map for agents.

Content:

- Laravel 9, PHP 8, Blade, Bootstrap/AdminLTE, Sass, Laravel Mix, MySQL, Docker.
- Key folders: `app/`, `routes/`, `database/`, `resources/views/`, `resources/sass/`, `tests/`, `tasks/`.
- Important app areas: admin orders, contacts, trips, driver portal, parcel tracking, dashboard, reports.
- Docker-first PHP command rule.
- Generated asset warning.

### `.codex/conventions.md`

Purpose: practical conventions for making changes.

Content:

- Backend conventions for routes, controllers, services, models, requests, migrations, and feature tests.
- Frontend conventions for Blade, Bootstrap 4, AdminLTE 3, jQuery, Sass, and Laravel Mix.
- Documentation conventions for README and task files.
- Commit and diff hygiene.

### `.codex/guardrails.md`

Purpose: safety rules that agents must apply before and during edits.

Content:

- No secrets, no `.env` credential edits unless explicitly requested.
- No local host PHP/composer/artisan commands.
- No broad rewrites, framework swaps, or dependency upgrades without explicit approval.
- No hand edits to generated files under `public/css`, `public/js`, or `public/mix-manifest.json`.
- Database changes require migration, rollback, data impact note, and validation command.
- Report failed or skipped checks honestly.

## Workflows

### `.codex/workflows/feature-development.md`

Use when adding a feature.

Steps:

1. Read `AGENTS.md`, `CODEX.md`, and `.codex/project-context.md`.
2. Inspect existing routes, controllers, models, views, tests, and task docs.
3. Write a scoped plan.
4. Add or update tests close to the changed behavior.
5. Implement small changes.
6. Run focused checks first, then broader checks if risk is higher.
7. Review the diff.
8. Summarize files, commands, results, risks, and next step.

### `.codex/workflows/bug-fix.md`

Use when fixing a bug.

Steps:

1. Reproduce or trace the failure.
2. Identify root cause.
3. Add or update a regression test when feasible.
4. Make the smallest safe fix.
5. Run the targeted test.
6. Explain why the fix works and what remains unverified.

### `.codex/workflows/database-change.md`

Use when changing schema, seeders, factories, or database lifecycle behavior.

Steps:

1. Inspect migrations and model usage.
2. Confirm table and column names through existing code.
3. Create a migration with `up()` and `down()`.
4. Update model fillable, casts, relationships, and tests.
5. Run Docker-based migration or focused tests.
6. Document data impact and rollback behavior.

### `.codex/workflows/ui-modernization.md`

Use when improving UI without replacing the current stack.

Steps:

1. Inspect Blade layout, partials, Sass entry points, and AdminLTE/Bootstrap conventions.
2. Prefer shared Sass and reusable partials before page-specific rewrites.
3. Keep Bootstrap 4/AdminLTE markup compatible.
4. Avoid new frontend dependencies unless explicitly approved.
5. Run `npm run dev` after Sass or asset entry changes.
6. Check responsive behavior on changed pages.

### `.codex/workflows/driver-portal-change.md`

Use when changing driver role, trip assignment, driver routes, mobile driver UI, or parcel actions.

Steps:

1. Read task docs under `tasks/` related to the driver portal.
2. Inspect `User`, `Trip`, `TripItem`, `DriverTripController`, route files, and driver views.
3. Preserve ownership checks for `trips.driver_user_id`.
4. Keep admin preview behavior separate from authenticated driver routes.
5. Run the focused driver feature test for the changed behavior.
6. Update README route and role notes when route behavior changes.

### `.codex/workflows/code-review.md`

Use when reviewing local changes.

Steps:

1. Inspect `git status --short`.
2. Review relevant `git diff`.
3. Prioritize correctness, access control, data safety, test coverage, UI regressions, and generated asset handling.
4. Report findings first with file and line references.
5. Mention test gaps and residual risk.

### `.codex/workflows/validation-release.md`

Use before handing off completed work.

Steps:

1. Run the narrowest relevant checks.
2. Run broader checks when risk is high or shared behavior changed.
3. For PHP, use `docker compose exec app php artisan test`.
4. For frontend assets, use `npm run dev`.
5. For driver portal changes, run the focused driver tests listed in `tasks/task-007.md`.
6. Summarize passed, failed, skipped, and not-run checks.

## Agent Role Files

### `.codex/agents/planner.md`

Planner inspects context and creates an implementation plan. It does not edit files.

### `.codex/agents/implementer.md`

Implementer follows an approved plan, keeps changes small, and works within existing Laravel, Blade, Sass, Bootstrap, AdminLTE, and jQuery patterns.

### `.codex/agents/tester.md`

Tester identifies existing test style, adds focused behavior tests, and runs Docker-based validation commands.

### `.codex/agents/reviewer.md`

Reviewer checks correctness, maintainability, security, access control, backward compatibility, generated asset handling, and test coverage.

### `.codex/agents/backend-engineer.md`

Backend engineer focuses on routes, controllers, requests, services, models, migrations, and feature tests.

### `.codex/agents/ui-engineer.md`

UI engineer improves Blade/AdminLTE/Bootstrap screens while preserving behavior and existing asset pipeline.

### `.codex/agents/security-reviewer.md`

Security reviewer focuses on authentication, authorization, ownership checks, input validation, escaping, secrets, and unsafe query risks.

## Skill Files

### `.codex/skills/safe-laravel-change.skill.md`

Reusable checklist for scoped Laravel changes:

- Inspect relevant files.
- Identify current behavior.
- Write or update a focused test when behavior changes.
- Implement minimal code.
- Run Docker-based checks.
- Review diff and summarize risk.

### `.codex/skills/generate-feature-test.skill.md`

Reusable checklist for feature tests:

- Use existing `tests/Feature` style.
- Use `RefreshDatabase` when database state matters.
- Create only the records needed for behavior.
- Assert redirects, status codes, database state, and rendered text where useful.
- Avoid brittle implementation-detail assertions.

### `.codex/skills/review-laravel-diff.skill.md`

Reusable review checklist:

- Route and middleware correctness.
- Authorization and ownership checks.
- Request validation.
- Model fillable, casts, and relationships.
- Migration rollback behavior.
- Blade escaping and form CSRF.
- Test coverage and skipped checks.

### `.codex/skills/update-documentation.skill.md`

Reusable documentation checklist:

- Update README when routes, roles, setup, commands, or user workflows change.
- Update task files only when the task plan itself changes.
- Keep Thai business descriptions clear and concise.
- Keep command examples Docker-correct for PHP.

## Prompt Files

### `.codex/prompts/plan-first.prompt.md`

Template for asking Codex to inspect first and return a plan without edits.

### `.codex/prompts/execute-task.prompt.md`

Template for asking Codex to execute an approved task plan in small steps.

### `.codex/prompts/review-current-diff.prompt.md`

Template for asking Codex to review uncommitted changes.

### `.codex/prompts/validate-driver-portal.prompt.md`

Template for validating the current driver portal workflow using the focused tests and manual checklist from `tasks/task-007.md`.

## Example Files

### `.codex/examples/good-task-plan.md`

Small example of a project-specific task plan with files, steps, commands, expected results, and acceptance criteria.

## Integration With Existing Files

`AGENTS.md` should stay mostly unchanged. It can receive one short section that points agents to `CODEX.md` and `.codex/`:

```md
## AI Workflow Files

- Read `CODEX.md` for Codex-specific usage guidance.
- Use `.codex/project-context.md`, `.codex/guardrails.md`, and relevant `.codex/workflows/*` files for reusable project workflows.
- These files supplement this `AGENTS.md`; they do not override repository rules here.
```

No application source files need to change for this structure.

## Validation

This work is documentation-only. Validation should include:

```bash
find .codex -maxdepth 3 -type f -print
sed -n '1,220p' CODEX.md
sed -n '1,220p' .codex/project-context.md
git diff --stat
```

No PHP, database, or asset build command is required unless implementation later changes application code or Sass.

## Risks

- Too many files can make agents ignore the structure. Keep each file short and focused.
- Conflicting instructions can reduce reliability. `AGENTS.md` must remain the highest-priority repo instruction.
- Generic AI docs are less useful than project-specific guidance. Files should mention this repo's Docker, Laravel, Blade, Bootstrap/AdminLTE, Sass, routes, and driver portal patterns.

## Out Of Scope

- No new dependencies.
- No framework or build tool changes.
- No runtime integration with Codex.
- No application behavior changes.
- No route, controller, model, migration, view, Sass, or public asset behavior changes.

## Approval Gate

After this design is approved, the next step is to write a detailed implementation plan for creating the files above. The implementation plan should be saved as a task file, preferably `tasks/task-008.md`, so it fits the existing project planning style.
