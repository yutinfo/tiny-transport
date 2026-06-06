# Task 008: Codex AI Agent Structure

## Goal

เพิ่มโครงสร้างสำหรับ Codex/AI agent ที่ใช้งานได้กับโปรเจกต์ Tiny Transport จริง โดยเป็นงานเอกสารและ workflow เท่านั้น ไม่เปลี่ยน runtime behavior ของ Laravel, route, database, asset build, หรือ dependency

## Decision

คง `AGENTS.md` เป็นกติกาหลักของ repo แล้วเพิ่ม `CODEX.md` และ `.codex/` เป็นชุดคู่มือเสริมสำหรับ context, guardrails, workflows, agent roles, reusable skills, prompts, และ examples

## Files

- Modify: `AGENTS.md`
- Create: `CODEX.md`
- Create: `.codex/project-context.md`
- Create: `.codex/conventions.md`
- Create: `.codex/guardrails.md`
- Create: `.codex/workflows/feature-development.md`
- Create: `.codex/workflows/bug-fix.md`
- Create: `.codex/workflows/database-change.md`
- Create: `.codex/workflows/ui-modernization.md`
- Create: `.codex/workflows/driver-portal-change.md`
- Create: `.codex/workflows/code-review.md`
- Create: `.codex/workflows/validation-release.md`
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
- Create: `docs/superpowers/plans/2026-06-07-codex-ai-agent-structure.md`

## Steps

- [ ] Create `CODEX.md` as the main Codex usage guide for this repository.
- [ ] Create core `.codex/` context files for project context, conventions, and guardrails.
- [ ] Create workflow files for feature development, bug fixes, database changes, UI modernization, driver portal changes, code review, and release validation.
- [ ] Create agent role files for planner, implementer, tester, reviewer, backend engineer, UI engineer, and security reviewer.
- [ ] Create reusable skill checklist files for safe Laravel changes, feature tests, Laravel diff review, and documentation updates.
- [ ] Create prompt templates for plan-first work, executing a task, reviewing current diff, and validating the driver portal.
- [ ] Create one example task plan that future Codex sessions can copy.
- [ ] Add a short `AI Workflow Files` section to `AGENTS.md` that points to `CODEX.md` and `.codex/`.
- [ ] Validate the documentation structure.

```bash
find .codex -maxdepth 3 -type f -print
rg -n "TB[D]|TO[D]O|implement[[:space:]]later|fill[[:space:]]in" CODEX.md .codex tasks/task-008.md docs/superpowers/plans/2026-06-07-codex-ai-agent-structure.md
git diff --check
git diff --stat
```

Expected result:

- `.codex/` contains the expected workflow, agent, skill, prompt, and example files.
- Placeholder scan has no matches.
- `git diff --check` passes.
- Diff is documentation-only.

## Commit

```bash
git add AGENTS.md CODEX.md .codex tasks/task-008.md docs/superpowers/plans/2026-06-07-codex-ai-agent-structure.md
git commit -m "docs: add codex agent workflow structure"
```

## Acceptance Criteria

- Codex can start future tasks by reading `AGENTS.md`, `CODEX.md`, and relevant `.codex/` files.
- The new files are specific to Laravel 9, Docker, Blade, Bootstrap/AdminLTE, Sass, Laravel Mix, MySQL, and the driver portal work in this repo.
- `AGENTS.md` remains the highest-priority repository instruction file.
- No application runtime behavior changes.
- No generated asset files are edited.
- No dependencies are added.
