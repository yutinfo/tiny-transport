# Prompt: Validate Driver Portal

```text
Read AGENTS.md, CODEX.md, .codex/project-context.md, .codex/guardrails.md, .codex/workflows/driver-portal-change.md, and tasks/task-007.md first.

Validate the driver portal work.

Run focused tests inside Docker:
- docker compose exec app php artisan test --filter=DriverRoleFeatureTest
- docker compose exec app php artisan test --filter=DriverTripAssignmentFeatureTest
- docker compose exec app php artisan test --filter=DriverPortalAccessFeatureTest
- docker compose exec app php artisan test --filter=DriverMobileViewFeatureTest
- docker compose exec app php artisan test --filter=DriverParcelActionFeatureTest

Run broader checks if focused tests pass:
- docker compose exec app php artisan test
- npm run dev

Then inspect:
- README route and role notes
- driver route access
- mobile driver layout
- ownership guard for trip item actions

Report exact commands, results, skipped checks, and residual risk.
```
