---
description: Click-test a Tiny Transport feature in the running app (browser), then verify the real effect in the database. Logs in as the right role, walks the workflow, captures screenshots, and reports PASS/FAIL.
argument-hint: "[the feature/workflow to test, e.g. 'driver updates COD status' or a tasks/ plan path]"
---

Use the **laravel-ui-tester** subagent (Agent tool, `subagent_type: "laravel-ui-tester"`) to click-test the feature below.

What to test (free text — a feature, a workflow, or a tasks/ plan path): $ARGUMENTS

Tell the agent to: confirm the stack is up (`docker compose ps`, app on
`http://localhost:8000`), invoke the `tiny-transport` skill for the route map and
the seeded login (`admin` / `password`) and driver model, drive the workflow in
the browser as the correct role (admin/staff for `/admin/*`, a driver account
for `/driver/*`), capture a screenshot at each meaningful step, and — critically —
**verify the real effect at the DB layer** with
`docker compose exec app php artisan tinker`, not the UI alone. For access-control
features it should also test the forbidden direction (a driver blocked from admin
URLs). Report PASS/FAIL per step with screenshots and DB evidence. The agent is a
tester — it never edits source; it hands any bug to the developer/user with an
exact repro.
