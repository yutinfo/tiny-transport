# Workflow: Validation And Release Check

Use this workflow before handing off completed work.

## Steps

1. Read the task acceptance criteria.
2. Identify the narrowest commands that prove the changed behavior.
3. Run focused checks first.
4. Run broader checks when shared behavior changed.
5. Run asset checks when Sass or frontend source changed.
6. Run manual UI checks when routes, forms, dashboards, driver screens, or responsive behavior changed.
7. Review `git status --short`.
8. Review relevant `git diff` or `git diff --stat`.
9. Report actual results, skipped checks, and residual risk.

## Common Checks

```bash
docker compose exec app php artisan test
npm run dev
npm run prod
git diff --check
```

## Driver Portal Checks

```bash
docker compose exec app php artisan test --filter=DriverRoleFeatureTest
docker compose exec app php artisan test --filter=DriverTripAssignmentFeatureTest
docker compose exec app php artisan test --filter=DriverPortalAccessFeatureTest
docker compose exec app php artisan test --filter=DriverMobileViewFeatureTest
docker compose exec app php artisan test --filter=DriverParcelActionFeatureTest
```

## Documentation-Only Checks

```bash
find .codex -maxdepth 3 -type f -print
git diff --check
git diff --stat
```

Do not claim tests, builds, or manual checks passed unless they were actually run.
