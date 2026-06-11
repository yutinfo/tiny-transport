<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DriverService
{
    /**
     * Create a driver and optionally create/link a login account.
     *
     * $data keys: name, last_name, mobile, license_plate, driver_license_no,
     *   area_name, note, status, created_by, updated_by
     * $account: ['mode' => 'none'|'create'|'link', ...]
     */
    public function createDriver(array $data, array $account = []): Driver
    {
        return DB::transaction(function () use ($data, $account) {
            $data['code'] = Driver::generateCode();
            $data['user_id'] = $this->resolveAccount($account, $data);

            return Driver::create($data);
        });
    }

    public function updateDriver(Driver $driver, array $data, array $account = []): Driver
    {
        return DB::transaction(function () use ($driver, $data, $account) {
            $userId = $this->resolveAccount($account, $data, $driver);

            // 'keep' leaves the existing linkage untouched.
            if (($account['mode'] ?? 'keep') !== 'keep') {
                $data['user_id'] = $userId;
            }

            $driver->fill($data);
            $driver->save();

            // Keep the linked user's active/inactive state in sync with the driver.
            $this->syncUserStatus($driver->fresh());

            return $driver->refresh();
        });
    }

    /**
     * Delete a driver. Blocked when any trip references it.
     */
    public function destroyDriver(Driver $driver, bool $deleteAccount = false): void
    {
        DB::transaction(function () use ($driver, $deleteAccount) {
            if ($driver->trips()->exists()) {
                throw new InvalidArgumentException('คนขับรายนี้มีรอบขนส่งผูกอยู่ ลบไม่ได้ กรุณาปิดใช้งานแทน');
            }

            $user = $driver->user;

            $driver->delete();

            if ($deleteAccount && $user) {
                $user->delete();
            }
        });
    }

    /**
     * Toggle active/inactive and mirror the state onto the linked login account.
     */
    public function toggleStatus(Driver $driver): Driver
    {
        return DB::transaction(function () use ($driver) {
            $driver->status = $driver->status === Driver::STATUS_ACTIVE
                ? Driver::STATUS_INACTIVE
                : Driver::STATUS_ACTIVE;
            $driver->save();

            $this->syncUserStatus($driver);

            return $driver->refresh();
        });
    }

    /**
     * Link an existing (unlinked) driver-role user to this driver.
     */
    public function linkAccount(Driver $driver, int $userId): Driver
    {
        return DB::transaction(function () use ($driver, $userId) {
            $user = $this->assertLinkableUser($userId, $driver);

            $driver->user_id = $user->id;
            $driver->save();

            $this->syncUserStatus($driver);

            return $driver->refresh();
        });
    }

    /**
     * Create a brand-new driver-role user and link it to this driver.
     */
    public function createAndLinkAccount(Driver $driver, array $credentials): Driver
    {
        return DB::transaction(function () use ($driver, $credentials) {
            $user = $this->createDriverUser($credentials, $driver);

            $driver->user_id = $user->id;
            $driver->save();

            return $driver->refresh();
        });
    }

    public function resetPassword(Driver $driver, string $password): void
    {
        $user = $driver->user;

        if (! $user) {
            throw new InvalidArgumentException('คนขับรายนี้ยังไม่มีบัญชีเข้าสู่ระบบ');
        }

        $user->password = $password;
        $user->save();
    }

    /**
     * Resolve an account choice into a user_id for create/update.
     */
    private function resolveAccount(array $account, array $driverData, ?Driver $driver = null): ?int
    {
        $mode = $account['mode'] ?? 'none';

        switch ($mode) {
            case 'create':
                $user = $this->createDriverUser([
                    'username' => $account['username'] ?? null,
                    'email' => $account['email'] ?? null,
                    'password' => $account['password'] ?? null,
                    'name' => $driverData['name'] ?? '',
                    'last_name' => $driverData['last_name'] ?? null,
                ], $driver);

                return $user->id;

            case 'link':
                $user = $this->assertLinkableUser((int) ($account['user_id'] ?? 0), $driver);

                return $user->id;

            case 'unlink':
                return null;

            case 'keep':
                return $driver?->user_id;

            case 'none':
            default:
                return $driver?->user_id;
        }
    }

    private function createDriverUser(array $credentials, ?Driver $driver = null): User
    {
        return User::create([
            'username' => $credentials['username'],
            'email' => $credentials['email'] ?? null,
            'password' => $credentials['password'],
            'name' => $credentials['name'] ?: ($credentials['username'] ?? 'driver'),
            'last_name' => $credentials['last_name'] ?? null,
            // Driver accounts are always created as active drivers — role cannot be chosen.
            'role_name' => User::ROLE_DRIVER,
            'status' => 'active',
            'username_verified_at' => now(),
        ]);
    }

    private function assertLinkableUser(int $userId, ?Driver $driver = null): User
    {
        $user = User::find($userId);

        if (! $user) {
            throw new InvalidArgumentException('ไม่พบบัญชีผู้ใช้ที่เลือก');
        }

        if ($user->role_name !== User::ROLE_DRIVER) {
            throw new InvalidArgumentException('บัญชีที่ผูกได้ต้องเป็นบัญชีคนขับเท่านั้น');
        }

        $existing = Driver::query()
            ->where('user_id', $user->id)
            ->when($driver, fn ($q) => $q->where('id', '!=', $driver->id))
            ->exists();

        if ($existing) {
            throw new InvalidArgumentException('บัญชีนี้ถูกผูกกับคนขับรายอื่นแล้ว');
        }

        return $user;
    }

    private function syncUserStatus(Driver $driver): void
    {
        if (! $driver->user_id) {
            return;
        }

        $user = $driver->user;

        if (! $user) {
            return;
        }

        $targetStatus = $driver->status === Driver::STATUS_ACTIVE ? 'active' : 'inactive';

        if ($user->status !== $targetStatus) {
            $user->status = $targetStatus;
            $user->save();
        }
    }
}
