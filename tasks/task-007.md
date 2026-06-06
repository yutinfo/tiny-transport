# Task 007: End-to-End Validation and Documentation

## Goal

ตรวจครบทั้ง role, assignment, access control, mobile UI, และ asset build ก่อนส่งมอบ

## Files

- Modify: `README.md`
- Optional modify: `AGENTS.md` only if team guidance for driver role needs to be permanent
- Test: existing feature tests plus new driver tests

## Steps

- [ ] Update `README.md` feature and route sections to include driver role behavior.

Add these route rows:

```markdown
| GET | `/driver` | หน้ารายการรอบขนส่งของคนขับ |
| GET | `/driver/trips/{trip}` | หน้ารายละเอียดรอบขนส่ง mobile สำหรับคนขับ |
| POST | `/driver/trip-items/{tripItem}/delivery-status` | คนขับอัปเดตสถานะจัดส่งพัสดุของตนเอง |
| POST | `/driver/trip-items/{tripItem}/payment-status` | คนขับบันทึกยอดเก็บเงิน COD ของตนเอง |
```

- [ ] Add a short account note to `README.md`.

```markdown
Role `driver` ใช้สำหรับบัญชีคนขับรถ หลังเข้าสู่ระบบจะถูกส่งไปที่ `/driver` และเห็นเฉพาะรอบขนส่งที่ `trips.driver_user_id` ตรงกับบัญชีของตนเอง
```

- [ ] Run the focused test set inside Docker.

```bash
docker compose exec app php artisan test --filter=DriverRoleFeatureTest
docker compose exec app php artisan test --filter=DriverTripAssignmentFeatureTest
docker compose exec app php artisan test --filter=DriverPortalAccessFeatureTest
docker compose exec app php artisan test --filter=DriverMobileViewFeatureTest
docker compose exec app php artisan test --filter=DriverParcelActionFeatureTest
```

Expected result: every focused driver test passes.

- [ ] Run the broader PHP test suite inside Docker.

```bash
docker compose exec app php artisan test
```

Expected result: all tests pass.

- [ ] Run frontend development build.

```bash
npm run dev
```

Expected result: Laravel Mix builds `resources/sass/app.scss` and JavaScript without errors.

- [ ] Manual browser checks.

Use these scenarios:

```text
1. Admin login goes to /admin/dashboard.
2. Admin creates a driver user.
3. Admin creates a trip and assigns that driver.
4. Admin assigns parcels to the trip and starts the trip.
5. Driver login goes to /driver.
6. Driver sees only assigned active trip.
7. Driver opens trip on mobile viewport 390x844.
8. Driver can call, open map, mark delivered, mark failed with reason, and collect COD after delivered.
9. Driver cannot open /admin/orders.
10. Driver cannot POST to another driver's trip item.
```

- [ ] Inspect changed files before final commit.

```bash
git status --short
git diff -- app/Models/User.php app/Models/Trip.php app/Http/Controllers/DriverTripController.php routes/admin.php routes/driver.php resources/views/layouts/driver.blade.php resources/sass/_driver.scss README.md
```

- [ ] Commit final documentation and any validation-only updates.

```bash
git add README.md
git commit -m "docs: document driver portal"
```

## Acceptance Criteria

- All driver-specific tests pass.
- Existing trip/order/dashboard tests still pass.
- `npm run dev` passes after Sass changes.
- README documents driver routes and role behavior.
- Manual mobile check confirms the driver screen is vertical, touch-friendly, and not using the admin sidebar layout.
