<?php

namespace App\Console\Commands;

use App\Models\Driver;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillDrivers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'drivers:backfill {--dry-run : Show what would change without writing anything}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a drivers master row for every driver user and link existing trips by driver_user_id';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Dry run — no changes will be written.');
        }

        $driverUsers = User::query()
            ->where('role_name', User::ROLE_DRIVER)
            ->orderBy('id')
            ->get();

        $created = 0;
        $existing = 0;
        $tripsLinked = 0;

        foreach ($driverUsers as $user) {
            $driver = Driver::query()->where('user_id', $user->id)->first();

            if ($driver) {
                $existing++;
                $this->line("- user #{$user->id} ({$user->username}) already linked to driver {$driver->code}");
            } else {
                // Pull mobile / license_plate / area from the user's most recent trip if available.
                $latestTrip = Trip::query()
                    ->where('driver_user_id', $user->id)
                    ->orderByDesc('trip_date')
                    ->orderByDesc('id')
                    ->first();

                $attributes = [
                    'name' => $user->name ?: $user->username,
                    'last_name' => $user->last_name,
                    'mobile' => $this->resolveMobile($user, $latestTrip),
                    'license_plate' => $latestTrip->car_id ?? null,
                    'area_name' => $latestTrip->area_name ?? null,
                    'status' => $user->status === 'active' ? Driver::STATUS_ACTIVE : Driver::STATUS_INACTIVE,
                    'user_id' => $user->id,
                    'created_by' => 'backfill',
                    'updated_by' => 'backfill',
                ];

                if ($dryRun) {
                    $this->info("+ would create driver for user #{$user->id} ({$user->username}) mobile={$attributes['mobile']}");
                } else {
                    $driver = DB::transaction(function () use ($attributes) {
                        $attributes['code'] = Driver::generateCode();

                        return Driver::create($attributes);
                    });
                    $this->info("+ created driver {$driver->code} for user #{$user->id} ({$user->username})");
                }

                $created++;
            }

            // Link the driver's trips by driver_user_id -> driver_id (idempotent).
            $tripQuery = Trip::query()
                ->where('driver_user_id', $user->id)
                ->whereNull('driver_id');

            $count = (clone $tripQuery)->count();

            if ($count > 0) {
                if (! $dryRun && $driver) {
                    $tripQuery->update(['driver_id' => $driver->id]);
                }
                $tripsLinked += $count;
                $this->line("  -> {$count} trip(s) " . ($dryRun ? 'would be' : '') . ' linked');
            }
        }

        $this->newLine();
        $this->info("Driver users scanned: {$driverUsers->count()}");
        $this->info("Drivers created: {$created}");
        $this->info("Drivers already present: {$existing}");
        $this->info("Trips linked to a driver_id: {$tripsLinked}");

        return self::SUCCESS;
    }

    /**
     * Pick a non-empty mobile, preferring the most recent trip snapshot.
     * Drivers.mobile is required + unique, so fall back to a placeholder when
     * nothing is known; the admin fills it in later.
     */
    private function resolveMobile(User $user, ?Trip $latestTrip): string
    {
        $mobile = $latestTrip->driver_mobile ?? null;

        if (! $mobile || ! preg_match('/^\d{9,10}$/', $mobile)) {
            // Unique placeholder derived from the user id so the unique index holds.
            return '0' . str_pad((string) $user->id, 9, '0', STR_PAD_LEFT);
        }

        return $mobile;
    }
}
