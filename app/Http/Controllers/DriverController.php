<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Trip;
use App\Models\TripItem;
use App\Models\User;
use App\Services\DriverService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class DriverController extends Controller
{
    public function __construct(private DriverService $driverService)
    {
    }

    public function index(Request $request)
    {
        $query = Driver::query()
            ->withCount('trips')
            ->with('user')
            ->orderByDesc('id');

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('mobile', 'like', "%{$keyword}%")
                    ->orWhere('license_plate', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('has_account')) {
            if ($request->has_account === 'yes') {
                $query->whereNotNull('user_id');
            } elseif ($request->has_account === 'no') {
                $query->whereNull('user_id');
            }
        }

        $drivers = $query->paginate(20)->appends($request->query());

        // "Today" availability per listed driver (busy = has a non-cancelled trip today).
        $today = now()->toDateString();
        $busyToday = Trip::query()
            ->whereDate('trip_date', $today)
            ->where('status', '!=', Trip::STATUS_CANCELLED)
            ->whereNotNull('driver_id')
            ->pluck('id', 'driver_id');

        return view('admin.drivers.list', [
            'data' => $drivers,
            'selected' => $request->only(['keyword', 'status', 'has_account']),
            'statusLabels' => Driver::statusLabels(),
            'busyToday' => $busyToday,
        ]);
    }

    public function create()
    {
        return view('admin.drivers.create', [
            'data' => new Driver(['status' => Driver::STATUS_ACTIVE]),
            'unlinkedDriverUsers' => $this->unlinkedDriverUsers(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateDriver($request);
        $account = $this->validateAccount($request);

        $data = array_merge($validated, [
            'created_by' => Auth::user()->name ?? null,
            'updated_by' => Auth::user()->name ?? null,
        ]);

        try {
            $driver = $this->driverService->createDriver($data, $account);
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->withInput()->withErrors(['account' => $exception->getMessage()]);
        }

        return redirect()->route('admin.drivers.show', $driver)->with('success', 'บันทึกข้อมูลคนขับแล้ว');
    }

    public function show(Driver $driver)
    {
        $driver->load('user');

        $startOfMonth = now()->startOfMonth();

        $trips = $driver->trips()
            ->orderByDesc('trip_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $stats = [
            'total_trips' => $driver->trips()->count(),
            'this_month' => $driver->trips()->whereDate('trip_date', '>=', $startOfMonth->toDateString())->count(),
            'cod_collected' => (float) $driver->trips()->sum('collected_amount'),
        ];

        $deliveryStats = TripItem::query()
            ->whereIn('trip_id', $driver->trips()->select('id'))
            ->selectRaw('COUNT(*) as total_items')
            ->selectRaw('SUM(CASE WHEN delivery_status = ? THEN 1 ELSE 0 END) as delivered_items', [TripItem::DELIVERY_STATUS_DELIVERED])
            ->first();

        $totalItems = (int) ($deliveryStats->total_items ?? 0);
        $deliveredItems = (int) ($deliveryStats->delivered_items ?? 0);
        $stats['success_rate'] = $totalItems > 0 ? round(($deliveredItems / $totalItems) * 100, 1) : 0.0;

        return view('admin.drivers.show', [
            'data' => $driver,
            'trips' => $trips,
            'stats' => $stats,
            'tripStatusLabels' => Trip::statusLabels(),
        ]);
    }

    public function edit(Driver $driver)
    {
        $driver->load('user');

        return view('admin.drivers.edit', [
            'data' => $driver,
            'unlinkedDriverUsers' => $this->unlinkedDriverUsers($driver),
        ]);
    }

    public function update(Request $request, Driver $driver)
    {
        $validated = $this->validateDriver($request, $driver);
        $account = $this->validateAccount($request, $driver);

        $data = array_merge($validated, [
            'updated_by' => Auth::user()->name ?? null,
        ]);

        try {
            $this->driverService->updateDriver($driver, $data, $account);
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->withInput()->withErrors(['account' => $exception->getMessage()]);
        }

        return redirect()->route('admin.drivers.show', $driver)->with('success', 'บันทึกข้อมูลคนขับแล้ว');
    }

    public function destroy(Request $request, Driver $driver)
    {
        $deleteAccount = $request->boolean('delete_account');

        try {
            $this->driverService->destroyDriver($driver, $deleteAccount);
        } catch (InvalidArgumentException $exception) {
            return redirect()->route('admin.drivers.show', $driver)->withErrors(['driver' => $exception->getMessage()]);
        }

        return redirect()->route('admin.drivers.index')->with('success', 'ลบข้อมูลคนขับแล้ว');
    }

    public function toggleStatus(Driver $driver)
    {
        $this->driverService->toggleStatus($driver);

        return redirect()->route('admin.drivers.show', $driver)
            ->with('success', $driver->fresh()->status === Driver::STATUS_ACTIVE ? 'เปิดใช้งานคนขับแล้ว' : 'ปิดใช้งานคนขับแล้ว');
    }

    public function resetPassword(Request $request, Driver $driver)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'required' => ':attribute จำเป็นต้องกรอก',
            'min' => ':attribute อย่างน้อย 8 ตัวอักษร',
            'confirmed' => ':attribute ยืนยันไม่ตรงกัน',
        ], [
            'password' => 'รหัสผ่านใหม่',
        ]);

        try {
            $this->driverService->resetPassword($driver, $request->input('password'));
        } catch (InvalidArgumentException $exception) {
            return redirect()->route('admin.drivers.edit', $driver)->withErrors(['password' => $exception->getMessage()]);
        }

        return redirect()->route('admin.drivers.edit', $driver)->with('success', 'รีเซ็ตรหัสผ่านแล้ว');
    }

    /**
     * Availability of active drivers on a given date.
     * GET /admin/api/drivers/availability?date=YYYY-MM-DD&exclude_trip={id?}
     *
     * Returns [{driver_id, busy, trips:[{code, status_label}]}].
     * A driver is "busy" when they have a non-cancelled trip on that date.
     */
    public function availability(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'exclude_trip' => ['nullable', 'integer'],
        ]);

        $date = $validated['date'];
        $excludeTrip = $validated['exclude_trip'] ?? null;

        $trips = Trip::query()
            ->whereDate('trip_date', $date)
            ->where('status', '!=', Trip::STATUS_CANCELLED)
            ->whereNotNull('driver_id')
            ->when($excludeTrip, fn ($q) => $q->where('id', '!=', $excludeTrip))
            ->get(['id', 'driver_id', 'code', 'status']);

        $byDriver = $trips->groupBy('driver_id');

        $result = Driver::query()
            ->active()
            ->pluck('id')
            ->map(function ($driverId) use ($byDriver) {
                $driverTrips = $byDriver->get($driverId, collect());

                return [
                    'driver_id' => $driverId,
                    'busy' => $driverTrips->isNotEmpty(),
                    'trips' => $driverTrips->map(fn ($trip) => [
                        'code' => $trip->code,
                        'status_label' => Trip::statusLabel($trip->status),
                    ])->values(),
                ];
            })
            ->values();

        return response()->json($result);
    }

    private function validateDriver(Request $request, ?Driver $driver = null): array
    {
        $mobileRule = Rule::unique('drivers', 'mobile');

        if ($driver) {
            $mobileRule->ignore($driver->id);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'mobile' => ['required', 'regex:/^\d{9,10}$/', $mobileRule],
            'license_plate' => ['nullable', 'string', 'max:20'],
            'driver_license_no' => ['nullable', 'string', 'max:20'],
            'area_name' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string'],
            'status' => ['required', Rule::in([Driver::STATUS_ACTIVE, Driver::STATUS_INACTIVE])],
        ], [
            'required' => ':attribute จำเป็นต้องกรอก',
            'regex' => ':attribute ต้องเป็นตัวเลข 9 ถึง 10 หลัก',
            'unique' => ':attribute นี้ถูกใช้กับคนขับรายอื่นแล้ว',
            'max' => ':attribute ยาวเกินไป',
        ], [
            'name' => 'ชื่อ',
            'last_name' => 'นามสกุล',
            'mobile' => 'เบอร์โทรศัพท์',
            'license_plate' => 'ทะเบียนรถ',
            'driver_license_no' => 'เลขใบขับขี่',
            'area_name' => 'พื้นที่ประจำ',
            'status' => 'สถานะ',
        ]);
    }

    private function validateAccount(Request $request, ?Driver $driver = null): array
    {
        $allowedModes = $driver
            ? ['keep', 'create', 'link', 'unlink']
            : ['none', 'create', 'link'];

        $request->validate([
            'account_mode' => ['nullable', Rule::in($allowedModes)],
        ]);

        $mode = $request->input('account_mode', $driver ? 'keep' : 'none');

        if ($mode === 'create') {
            $emailRule = ['nullable', 'email', 'unique:users,email'];

            $request->validate([
                'username' => ['required', 'string', 'unique:users,username'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'email' => $emailRule,
            ], [
                'required' => ':attribute จำเป็นต้องกรอก',
                'unique' => ':attribute นี้ถูกใช้แล้ว',
                'min' => ':attribute อย่างน้อย 8 ตัวอักษร',
                'confirmed' => ':attribute ยืนยันไม่ตรงกัน',
                'email' => ':attribute รูปแบบไม่ถูกต้อง',
            ], [
                'username' => 'ชื่อผู้ใช้',
                'password' => 'รหัสผ่าน',
                'email' => 'อีเมล',
            ]);

            return [
                'mode' => 'create',
                'username' => $request->input('username'),
                'password' => $request->input('password'),
                'email' => $request->input('email'),
            ];
        }

        if ($mode === 'link') {
            $request->validate([
                'user_id' => [
                    'required',
                    Rule::exists('users', 'id')->where(fn ($q) => $q->where('role_name', User::ROLE_DRIVER)),
                ],
            ], [
                'required' => ':attribute จำเป็นต้องเลือก',
                'exists' => ':attribute ไม่ถูกต้อง',
            ], [
                'user_id' => 'บัญชีคนขับ',
            ]);

            return ['mode' => 'link', 'user_id' => (int) $request->input('user_id')];
        }

        return ['mode' => $mode];
    }

    /**
     * Driver-role users not yet linked to any driver (plus the current driver's own
     * account so the edit form can keep showing it).
     */
    private function unlinkedDriverUsers(?Driver $driver = null)
    {
        return User::query()
            ->where('role_name', User::ROLE_DRIVER)
            ->where(function ($q) use ($driver) {
                $q->whereDoesntHave('driverProfile');

                if ($driver && $driver->user_id) {
                    $q->orWhere('id', $driver->user_id);
                }
            })
            ->orderBy('name')
            ->orderBy('last_name')
            ->get();
    }
}
