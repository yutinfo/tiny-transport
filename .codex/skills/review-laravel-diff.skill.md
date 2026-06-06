# Skill: Review Laravel Diff

## Use When

Reviewing local changes before commit or handoff.

## Instructions

1. Run `git status --short`.
2. Inspect `git diff --stat`.
3. Inspect relevant `git diff` hunks.
4. Check route and middleware correctness.
5. Check authorization, role checks, and ownership checks.
6. Check request validation and error handling.
7. Check model fillable fields, casts, constants, and relationships.
8. Check migration rollback behavior when migrations changed.
9. Check Blade escaping, CSRF, and route names.
10. Check tests and validation commands.

## Output

Report findings first. Use this shape:

```md
Findings:
- Major: path:line - explanation and impact.

Open questions:
- ...

Summary:
- ...
```
