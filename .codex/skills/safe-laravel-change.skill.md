# Skill: Safe Laravel Change

## Use When

Making a scoped Laravel code or documentation change in this repository.

## Instructions

1. Read `AGENTS.md`, `CODEX.md`, `.codex/project-context.md`, and `.codex/guardrails.md`.
2. Select the matching workflow under `.codex/workflows/`.
3. Inspect relevant existing files before editing.
4. Identify current behavior and the requested behavior.
5. Write or update focused tests when behavior changes.
6. Implement the smallest safe change.
7. Run focused checks first.
8. Run broader checks when shared behavior changed.
9. Review `git status --short` and relevant `git diff`.
10. Summarize changes, checks, and risks.

## Required Safety

- Use Docker for PHP commands.
- Do not edit generated public assets by hand.
- Do not change unrelated behavior.
- Do not claim checks passed unless they were run.
