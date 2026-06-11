# Access control — roles, admin vs driver, ownership

The app has two kinds of authenticated users sharing one login. Getting this
wrong is a security bug, not a style issue.

## Roles
- `users.role_name` distinguishes **admin/staff** from **driver** behavior.
- Admin/staff work the full back office under `/admin/*`.
- Drivers use the mobile-first portal under `/driver/*`.

## Hard rules
1. **Admin-only screens stay protected.** Don't expose an `/admin/*` action to a
   driver user. Keep the existing middleware/guard on admin routes.
2. **Driver routes are ownership-scoped.** A driver may only see and act on trips
   where `trips.driver_user_id` matches the authenticated driver's id. Every
   driver query must filter on that — never list all trips.
3. **Guard trip-item actions by ownership.** Before updating a `trip_items` row
   (parcel/COD status), verify the item belongs to a trip assigned to the
   authenticated driver. Don't trust an id from the request.
4. **Staff routes must not leak to drivers.** A driver hitting an admin URL
   directly must be blocked (403/redirect), not silently served.

## When you touch auth/routes
- Add or keep the middleware that enforces the above; don't remove a guard to
  make something work.
- Add a feature test that proves the forbidden direction is blocked, not just
  that the happy path works (see `testing.md`).
