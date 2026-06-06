# Task 001: Driver Role Foundation

## Goal

เพิ่ม `driver` เป็น role ระดับระบบ โดยยังไม่เปลี่ยนเส้นทางงานขนส่งหรือหน้าจอ mobile ใน task นี้

## Decision

ใช้ `users.role_name` เดิมต่อไป และเพิ่มค่าคงที่/ตัวช่วยใน `App\Models\User` เพื่อให้ role validation, menu, middleware และ redirect ใช้ค่าเดียวกันในงานถัดไป

## Files

- Modify: `app/Models/User.php`
- Modify: `app/Http/Requests/UserCreateRequest.php`
- Modify: `app/Http/Requests/UserUpdateRequest.php`
- Modify: `resources/views/admin/user/form-component/auth.blade.php`
- Test: `tests/Feature/DriverRoleFeatureTest.php`

## Steps

- [ ] Add role constants and helpers to `app/Models/User.php`.

```php
public const ROLE_ADMIN = 'admin';
public const ROLE_STAFF = 'staff';
public const ROLE_DRIVER = 'driver';

public static function roles(): array
{
    return [
        self::ROLE_ADMIN,
        self::ROLE_STAFF,
        self::ROLE_DRIVER,
    ];
}

public static function roleLabels(): array
{
    return [
        self::ROLE_ADMIN => 'ผู้ดูแลระบบ',
        self::ROLE_STAFF => 'พนักงาน',
        self::ROLE_DRIVER => 'คนขับรถ',
    ];
}

public function isAdmin(): bool
{
    return $this->role_name === self::ROLE_ADMIN;
}

public function isStaff(): bool
{
    return $this->role_name === self::ROLE_STAFF;
}

public function isDriver(): bool
{
    return $this->role_name === self::ROLE_DRIVER;
}
```

- [ ] Update `UserCreateRequest` to import `App\Models\User` and validate `role_name` with `Rule::in(User::roles())`.

```php
use App\Models\User;

'role_name' => ['required', Rule::in(User::roles())],
```

- [ ] Update `UserUpdateRequest` the same way.

```php
use App\Models\User;

'role_name' => ['required', Rule::in(User::roles())],
```

- [ ] Replace hard-coded role options in `resources/views/admin/user/form-component/auth.blade.php` with `User::roleLabels()`.

```blade
@php($roleLabels = \App\Models\User::roleLabels())
@foreach($roleLabels as $roleValue => $roleLabel)
    <option value="{{ $roleValue }}" @isset($data->role_name) {{ $data->role_name === $roleValue ? 'selected' : '' }} @endif>
        {{ $roleLabel }}
    </option>
@endforeach
```

- [ ] Add `tests/Feature/DriverRoleFeatureTest.php`.

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverRoleFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_driver_user()
    {
        $admin = User::create([
            'username' => 'admin-driver-role',
            'password' => 'password',
            'name' => 'Admin',
            'last_name' => 'Role',
            'email' => 'admin-driver-role@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->post('/admin/users/store', [
                'username' => 'driver-one',
                'password' => 'password',
                'name' => 'Driver',
                'last_name' => 'One',
                'email' => 'driver-one@example.com',
                'status' => 'active',
                'role_name' => User::ROLE_DRIVER,
            ])
            ->assertRedirect('/admin/users/create');

        $this->assertDatabaseHas('users', [
            'username' => 'driver-one',
            'role_name' => User::ROLE_DRIVER,
        ]);
    }

    public function test_user_form_shows_driver_role_option()
    {
        $admin = User::create([
            'username' => 'admin-driver-form',
            'password' => 'password',
            'name' => 'Admin',
            'last_name' => 'Form',
            'email' => 'admin-driver-form@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->get('/admin/users/create')
            ->assertOk()
            ->assertSee('คนขับรถ');
    }
}
```

## Validation

Run inside Docker:

```bash
docker compose exec app php artisan test --filter=DriverRoleFeatureTest
```

Expected result: both tests pass.

## Commit

```bash
git add app/Models/User.php app/Http/Requests/UserCreateRequest.php app/Http/Requests/UserUpdateRequest.php resources/views/admin/user/form-component/auth.blade.php tests/Feature/DriverRoleFeatureTest.php
git commit -m "feat: add driver user role"
```

## Acceptance Criteria

- Admin can create and edit users with role `driver`.
- Role labels are centralized in `User`.
- Existing `admin` and `staff` roles continue to work.
