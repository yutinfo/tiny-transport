# Workflow: Feature Development

Use this workflow when adding user-visible behavior or expanding existing behavior.

## Steps

1. Read `AGENTS.md`, `CODEX.md`, `.codex/project-context.md`, and `.codex/guardrails.md`.
2. Read any relevant task file under `tasks/`.
3. Inspect current routes, controllers, models, services, views, tests, and README sections related to the feature.
4. Identify the smallest useful scope that satisfies the request.
5. Write a short implementation plan before editing.
6. Add or update focused feature tests when behavior changes.
7. Implement in small file groups.
8. Run the focused validation command first.
9. Run broader checks if shared behavior changed.
10. Review `git status --short` and relevant `git diff`.
11. Summarize files changed, checks run, results, risks, and next step.

## Laravel Checklist

- Routes are in the correct route file.
- Request validation is explicit.
- Authorization and role behavior are preserved.
- Model fillable, casts, and relationships are updated when needed.
- Blade forms include CSRF.
- README is updated when public route or workflow behavior changes.

## Preferred Checks

```bash
docker compose exec app php artisan test --filter=<FocusedFeatureTest>
docker compose exec app php artisan test
npm run dev
```

Only run asset builds when frontend source files changed.
