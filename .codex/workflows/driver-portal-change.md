# Workflow: Driver Portal Change

Use this workflow when changing driver role behavior, trip assignment, driver routes, driver mobile UI, parcel delivery actions, or COD collection.

## Read First

- `tasks/task-001.md`
- `tasks/task-002.md`
- `tasks/task-003.md`
- `tasks/task-004.md`
- `tasks/task-005.md`
- `tasks/task-006.md`
- `tasks/task-007.md`

Read the relevant subset if the requested change is narrow.

## Critical Files

- `app/Models/User.php`
- `app/Models/Trip.php`
- `app/Models/TripItem.php`
- `app/Http/Controllers/TripController.php`
- `app/Http/Controllers/DriverTripController.php`
- `app/Http/Middleware/EnsureRole.php`
- `routes/admin.php`
- `routes/driver.php`
- `resources/views/driver/`
- `resources/views/layouts/driver.blade.php`
- `resources/sass/_driver.scss`
- `README.md`

## Safety Rules

- Driver users must not access admin/staff work routes.
- Driver route access must be behind `auth` and `role:driver`.
- Driver trips must be filtered by `trips.driver_user_id = Auth::id()`.
- Driver trip item actions must verify the item belongs to a trip assigned to the authenticated driver.
- Admin driver preview behavior must stay separate from authenticated driver routes.
- COD collection must follow existing `TripService` business rules.

## Focused Checks

```bash
docker compose exec app php artisan test --filter=DriverRoleFeatureTest
docker compose exec app php artisan test --filter=DriverTripAssignmentFeatureTest
docker compose exec app php artisan test --filter=DriverPortalAccessFeatureTest
docker compose exec app php artisan test --filter=DriverMobileViewFeatureTest
docker compose exec app php artisan test --filter=DriverParcelActionFeatureTest
```

Run `npm run dev` when driver Sass or frontend source changed.

## Manual Checklist

Use the checklist in `tasks/task-007.md` before handoff when route, role, UI, or parcel action behavior changed.
