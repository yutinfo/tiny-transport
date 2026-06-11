---
description: Debug a Laravel 9 issue in Tiny Transport — a bug, exception, 500, failed migration, broken workflow, or asset problem. Reproduces, finds the root cause, and proposes the safest minimal fix.
argument-hint: "[the bug / error / traceback / failing command]"
---

Use the **laravel-debugger** subagent (Agent tool, `subagent_type: "laravel-debugger"`) to debug the problem below.

The problem (free text — a symptom, an error message, a traceback, a failing URL/command): $ARGUMENTS

Tell the agent to follow its method in order — **reproduce → isolate → diagnose
root cause → propose the safest fix → verify** — using Docker for all PHP
diagnostics (`docker compose exec app …`, `docker compose logs app`,
`storage/logs/laravel.log`). It should invoke the `laravel9` and `tiny-transport`
skills, name the root cause with `file:line` + the evidence that proves it, make
the smallest fix that addresses the cause (not the symptom), re-run the
reproduction to confirm, and suggest a regression test when the bug was logic.
It must not suppress errors, weaken validation, or edit `.env`/secrets to paper
over a config issue.
