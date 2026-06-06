# Skill: Update Documentation

## Use When

Updating README, task files, Codex guidance, or workflow documentation.

## Instructions

1. Identify the behavior or workflow that changed.
2. Update README when routes, roles, setup, user workflows, validation commands, or public behavior changed.
3. Update `tasks/` when the implementation plan changes.
4. Keep `AGENTS.md` focused on durable project rules.
5. Keep `CODEX.md` focused on Codex usage.
6. Keep `.codex/` files short, reusable, and project-specific.
7. Keep PHP command examples Docker-correct.
8. Run documentation validation.

## Documentation Validation

```bash
rg -n "TB[D]|TO[D]O|implement[[:space:]]later|fill[[:space:]]in" README.md AGENTS.md CODEX.md .codex tasks
git diff --check
git diff --stat
```
