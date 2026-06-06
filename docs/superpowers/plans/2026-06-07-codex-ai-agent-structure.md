# Codex AI Agent Structure Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Create a repository-local Codex/AI agent structure that works with this Laravel 9 Tiny Transport project.

**Architecture:** Keep `AGENTS.md` as the highest-priority project rule file, add `CODEX.md` as the Codex entry guide, and add `.codex/` markdown files for context, guardrails, workflows, roles, reusable skills, prompts, and examples. This is documentation-only and must not change application runtime behavior.

**Tech Stack:** Laravel 9, PHP 8, Docker Compose, Blade, Bootstrap 4, AdminLTE 3, jQuery, Sass, Laravel Mix, MySQL, Markdown.

---

### Task 1: Task File And Core Guide

**Files:**
- Create: `tasks/task-008.md`
- Create: `CODEX.md`

- [ ] **Step 1: Create `tasks/task-008.md`**

Add a task file with the goal, file list, steps, validation commands, commit command, and acceptance criteria for the Codex/AI structure. The task must state that implementation is documentation-only and must not touch runtime Laravel behavior.

- [ ] **Step 2: Create `CODEX.md`**

Add a Codex usage guide that tells agents to read `AGENTS.md`, `CODEX.md`, `.codex/project-context.md`, `.codex/guardrails.md`, and the relevant `.codex/workflows/*` file before work. Include request patterns, allowed tasks, constraints, validation commands, and final response format.

- [ ] **Step 3: Verify files can be read**

Run:

```bash
sed -n '1,220p' tasks/task-008.md
sed -n '1,220p' CODEX.md
```

Expected: both files print project-specific markdown and contain no draft markers.

### Task 2: Core `.codex/` Context Files

**Files:**
- Create: `.codex/project-context.md`
- Create: `.codex/conventions.md`
- Create: `.codex/guardrails.md`

- [ ] **Step 1: Create `.codex/project-context.md`**

Add project context for Laravel 9, PHP 8, Docker, Blade, Bootstrap/AdminLTE, Sass, Laravel Mix, MySQL, key folders, key app areas, routes, driver portal, testing, and asset build commands.

- [ ] **Step 2: Create `.codex/conventions.md`**

Add backend, frontend, database, testing, documentation, and diff hygiene conventions that match `AGENTS.md` and the current project.

- [ ] **Step 3: Create `.codex/guardrails.md`**

Add safety rules for secrets, Docker-only PHP commands, generated assets, database changes, dependencies, destructive commands, access control, and verification.

- [ ] **Step 4: Verify core context files**

Run:

```bash
sed -n '1,220p' .codex/project-context.md
sed -n '1,220p' .codex/conventions.md
sed -n '1,220p' .codex/guardrails.md
```

Expected: all three files print repo-specific guidance and contain no runtime code changes.

### Task 3: Workflow Files

**Files:**
- Create: `.codex/workflows/feature-development.md`
- Create: `.codex/workflows/bug-fix.md`
- Create: `.codex/workflows/database-change.md`
- Create: `.codex/workflows/ui-modernization.md`
- Create: `.codex/workflows/driver-portal-change.md`
- Create: `.codex/workflows/code-review.md`
- Create: `.codex/workflows/validation-release.md`

- [ ] **Step 1: Create feature and bug workflows**

Add `feature-development.md` and `bug-fix.md` with inspection, scoped planning, testing, implementation, validation, diff review, and final summary steps.

- [ ] **Step 2: Create database and UI workflows**

Add `database-change.md` and `ui-modernization.md` with migration lifecycle, model updates, Docker validation, Blade/AdminLTE/Sass conventions, and `npm run dev` guidance.

- [ ] **Step 3: Create driver, review, and release workflows**

Add `driver-portal-change.md`, `code-review.md`, and `validation-release.md`. Driver workflow must preserve `trips.driver_user_id` ownership checks and reference focused driver tests from `tasks/task-007.md`.

- [ ] **Step 4: Verify workflow inventory**

Run:

```bash
find .codex/workflows -maxdepth 1 -type f -print
```

Expected: seven workflow files are listed.

### Task 4: Agent, Skill, Prompt, And Example Files

**Files:**
- Create: `.codex/agents/planner.md`
- Create: `.codex/agents/implementer.md`
- Create: `.codex/agents/tester.md`
- Create: `.codex/agents/reviewer.md`
- Create: `.codex/agents/backend-engineer.md`
- Create: `.codex/agents/ui-engineer.md`
- Create: `.codex/agents/security-reviewer.md`
- Create: `.codex/skills/safe-laravel-change.skill.md`
- Create: `.codex/skills/generate-feature-test.skill.md`
- Create: `.codex/skills/review-laravel-diff.skill.md`
- Create: `.codex/skills/update-documentation.skill.md`
- Create: `.codex/prompts/plan-first.prompt.md`
- Create: `.codex/prompts/execute-task.prompt.md`
- Create: `.codex/prompts/review-current-diff.prompt.md`
- Create: `.codex/prompts/validate-driver-portal.prompt.md`
- Create: `.codex/examples/good-task-plan.md`

- [ ] **Step 1: Create agent role files**

Add seven concise role files for planner, implementer, tester, reviewer, backend engineer, UI engineer, and security reviewer.

- [ ] **Step 2: Create reusable skill files**

Add four `.skill.md` checklists for safe Laravel changes, feature test generation, Laravel diff review, and documentation updates.

- [ ] **Step 3: Create prompt templates and example**

Add four prompt templates and one example task plan that can be copied into future Codex requests.

- [ ] **Step 4: Verify inventory**

Run:

```bash
find .codex/agents .codex/skills .codex/prompts .codex/examples -maxdepth 1 -type f -print
```

Expected: all role, skill, prompt, and example files are listed.

### Task 5: Root Instruction Integration And Validation

**Files:**
- Modify: `AGENTS.md`

- [ ] **Step 1: Update `AGENTS.md`**

Add a short `AI Workflow Files` section stating that `CODEX.md` and `.codex/` supplement `AGENTS.md` and do not override it.

- [ ] **Step 2: Run documentation validation**

Run:

```bash
find .codex -maxdepth 3 -type f -print
rg -n "TB[D]|TO[D]O|implement[[:space:]]later|fill[[:space:]]in" CODEX.md .codex tasks/task-008.md docs/superpowers/plans/2026-06-07-codex-ai-agent-structure.md
git diff --check
git diff --stat
```

Expected: `find` lists the new `.codex/` files, `rg` returns no matches, `git diff --check` exits successfully, and `git diff --stat` shows documentation-only changes.

- [ ] **Step 3: Commit implementation**

Run:

```bash
git add AGENTS.md CODEX.md .codex tasks/task-008.md docs/superpowers/plans/2026-06-07-codex-ai-agent-structure.md
git commit -m "docs: add codex agent workflow structure"
```

Expected: one documentation commit is created.
