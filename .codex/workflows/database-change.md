# Workflow: Database Change

Use this workflow for migrations, seeders, factories, model schema fields, indexes, or database lifecycle changes.

## Steps

1. Read `AGENTS.md`, `CODEX.md`, `.codex/project-context.md`, and `.codex/guardrails.md`.
2. Inspect existing migrations for the target table.
3. Inspect related models, controllers, requests, services, views, tests, and README/database notes.
4. Confirm exact table and column names through code usage.
5. Create a migration with both `up()` and `down()`.
6. Keep backward compatibility for existing records when possible.
7. Update model `$fillable`, `$casts`, and relationships when needed.
8. Add or update feature tests for changed behavior.
9. Run Docker-based validation.
10. Document data impact, index impact, and rollback behavior.

## Migration Rules

- Nullable columns are preferred when old rows may not have values.
- Foreign keys should define delete behavior explicitly.
- Indexes should match real query patterns.
- Do not rename or drop columns without checking every usage.

## Useful Checks

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan test --filter=<FocusedFeatureTest>
docker compose exec app php artisan test
```

For fresh lifecycle validation when needed:

```bash
docker compose exec app php artisan migrate:fresh --seed
```
