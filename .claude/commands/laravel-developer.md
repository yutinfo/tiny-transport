---
description: Implement a Laravel 9 change in Tiny Transport — fix a bug/error, add a feature, or work a planned task. Branches only when the work is driven by a tasks/ plan or the user asks; plain fixes stay on the current branch.
argument-hint: "[a bug/error to fix | a change to make | path to a tasks/<plan>.md]"
---

Use the **laravel-developer** subagent (Agent tool, `subagent_type: "laravel-developer"`) to do the development work below.

Instruction from the user (free text — a bug, an error, a feature, a refactor, or a task-plan path): $ARGUMENTS

Hand the instruction to the agent and tell it which branching mode applies:

- **Plan-driven task → branch first.** If the instruction points at a `tasks/`
  plan or the user asks for a branch, the agent must follow the `git-flow` skill
  to create and check out the branch **before** writing any code.
- **Plain dev task → stay on the current branch.** A normal bug fix, error, or
  small change: make the fix on whatever branch is checked out; skip branch
  creation.

Everything else is the agent's standard behavior: invoke the `laravel9` and
`tiny-transport` skills first (apply core rules, read only the matching
`references/` file), read the real code, verify every route/column/method
exists, make the smallest correct edit, prove it runs inside Docker
(`docker compose exec app php artisan …`), and report what changed with
`file:line`. For anything touching auth/access control, COD/money math, or data
migration, pause and confirm before editing.
