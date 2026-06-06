# Workflow: Bug Fix

Use this workflow when investigating and fixing incorrect behavior.

## Steps

1. Read `AGENTS.md`, `CODEX.md`, `.codex/project-context.md`, and `.codex/guardrails.md`.
2. Reproduce the bug with a failing test, manual request, or direct code trace.
3. Identify the root cause before editing.
4. Find the smallest safe change that fixes the root cause.
5. Add or update a regression test when feasible.
6. Implement the fix without unrelated refactoring.
7. Run the focused regression test.
8. Run broader checks when shared behavior changed.
9. Review the diff for accidental behavior changes.
10. Explain why the fix works and what remains unverified.

## Rules

- Do not remove or weaken tests to make the suite pass.
- Do not hide exceptions without preserving useful feedback.
- Do not change unrelated UI or data behavior.
- Do not bypass authorization, role checks, or ownership checks.

## Useful Checks

```bash
docker compose exec app php artisan test --filter=<RegressionTest>
docker compose exec app php artisan test
```
