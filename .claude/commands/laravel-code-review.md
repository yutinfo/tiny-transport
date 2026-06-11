---
description: Review Laravel 9 / Blade / migration code in Tiny Transport for security, access-control, correctness, performance, and convention issues. Reviews the current diff by default, or whatever you point it at.
argument-hint: "[optional: a path, a diff range, or 'the current changes']"
---

Use the **laravel-code-reviewer** subagent (Agent tool, `subagent_type: "laravel-code-reviewer"`) to review the code below.

What to review (free text — a path, a diff range, or empty for the current changes): $ARGUMENTS

If no target is given, review the current working changes:
```bash
git status --short
git diff
```

Tell the agent to follow its standard process: invoke the `laravel9` and
`tiny-transport` skills, review **only** what changed (plus enough context to
judge correctness), ground every finding in `file:line` with the offending lines
quoted, and pay special attention to the app's #1 risk — **driver access control**
(ownership scoping on `trips.driver_user_id`, `trip_items` guards, admin routes
protected from drivers). Report findings ordered CRITICAL → HIGH → MEDIUM → LOW →
NIT, printing only sections that have findings, and end with a one-line verdict.
The agent does not edit code — it reports findings only.
