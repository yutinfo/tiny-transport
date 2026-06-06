# Project Conventions

## Backend

- Keep web UI routes in `routes/web.php`, `routes/admin.php`, or `routes/driver.php`.
- Keep API routes in `routes/api.php`.
- Keep request validation in form request classes when the pattern already exists.
- Keep repeated business rules in services or model methods when duplication becomes meaningful.
- Keep Eloquent table names, fillable fields, casts, and relationships close to the model they describe.
- Do not rename routes, tables, columns, or model properties without checking every usage.
- Prefer small controller changes that preserve existing request flow.

## Database

- Every schema change needs a migration with `up()` and `down()`.
- Check current model usage before adding or renaming columns.
- Use nullable columns for backward compatibility when old records may not have new data.
- Document data impact, index impact, and rollback behavior.
- Validate database lifecycle with Docker-based artisan commands when schema or seed behavior changes.

## Frontend

- Use existing Blade layouts and partials before adding new wrappers.
- Match Bootstrap 4 and AdminLTE 3 markup for cards, forms, tables, nav, alerts, modals, and buttons.
- Keep reusable Blade fragments in partials when a pattern appears in multiple places.
- Keep Sass changes in `resources/sass`.
- Prefer shared Sass improvements before page-specific CSS.
- Preserve existing jQuery behavior unless the task explicitly changes it.
- For driver portal screens, keep the layout mobile-first and avoid the admin sidebar shell.

## Testing

- Put Laravel feature tests under `tests/Feature`.
- Use existing test patterns before introducing a new style.
- Use `RefreshDatabase` when test behavior depends on database state.
- Create only the records needed by the test.
- Assert behavior through HTTP status, redirects, rendered text, and database state.
- Run focused tests first, then broader tests when shared behavior changed.

## Documentation

- Update `README.md` when routes, setup, roles, user workflows, validation commands, or public behavior change.
- Update `tasks/` files when the implementation plan itself changes.
- Keep Thai business descriptions direct and specific.
- Keep command examples Docker-correct for PHP.

## Diff Hygiene

- Keep changes scoped to the request.
- Do not mix unrelated cleanup with feature work.
- Do not revert user changes unless explicitly requested.
- Do not modify generated public assets by hand.
- Review `git status --short` and relevant `git diff` before final response or commit.
