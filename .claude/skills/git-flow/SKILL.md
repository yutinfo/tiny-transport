---
name: git-flow
description: >-
  The branching standard for the Tiny Transport repo — branch types, base
  branch, and naming rules, plus the exact safe steps to create and check out a
  branch. Use whenever you need to create a branch for a piece of work, or when
  the user asks "what branch should I use", "git flow", "create a branch for
  this", "ตั้งชื่อ branch", "แตก branch". This is the single source of truth —
  edit the CONFIG section to change the standard.
---

# Git Flow — branching standard

Single source of truth for how branches are named and created in this repo.
To change the standard, edit the **CONFIG** section below — nothing else needs to
change.

---

## CONFIG — edit this to change the standard

### Base branch
- **`main`** — the integration and production branch. Feature/fix/docs/chore
  branches are created from `main` and merged back via PR (the repo's history
  shows `feature/*` → PR → `main`).

### Branch types
| Type     | Prefix     | Branch from | Use for                                          |
|----------|------------|-------------|--------------------------------------------------|
| Feature  | `feature/` | `main`      | New feature or general task                      |
| Fix      | `fix/`     | `main`      | Bug fix                                           |
| Hotfix   | `hotfix/`  | `main`      | Urgent production fix                             |
| Docs     | `docs/`    | `main`      | Documentation only                               |
| Chore    | `chore/`   | `main`      | Tooling, config, refactor, no behavior change    |
| Release  | `release/` | `main`      | Release preparation (`release/<version>`)        |

### Naming rules
- Format: `<prefix><short-feature-name>`, e.g.
  `feature/driver-cod-status-update`, `fix/trip-cancel-validation`.
- `<short-feature-name>`: a few words, lowercase, kebab-case, ASCII, no spaces —
  short but understandable (aim for ≤ 6 words).
- If the work tracks an external card/ticket key, prefix the key as-is:
  `<prefix><KEY>-<short-feature-name>`.

---

## Procedure — create and check out a branch

1. **Resolve the name** from CONFIG: pick the type, build
   `<prefix><short-feature-name>`.

2. **Check the working tree is clean** (`git status`). If there are uncommitted
   changes, stop and ask the user — do not branch on top of their uncommitted
   work.

3. **Update the base and branch off it:**
   ```sh
   git fetch origin
   git checkout -b <prefix><short-feature-name> origin/main
   ```
   If there is no `origin` remote, branch off the local base:
   `git checkout main && git pull --ff-only` (if it tracks a remote), then
   `git checkout -b <branch> main`.

4. **If the branch already exists**, switch to it rather than failing or forcing:
   ```sh
   git checkout <prefix><short-feature-name>
   ```

5. **Confirm to the user** in one line: the branch name and the base.

## Rules
- Never force-create or force-push to resolve a name clash — switch to the
  existing branch and tell the user.
- Don't commit or push as part of branch creation — just create and check out.
- Commit/push only when the user asks (per the repo's commit conventions).
