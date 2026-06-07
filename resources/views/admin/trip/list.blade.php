@extends('layouts.app')

@section('content')
<div class="container-fluid ta-page-shell">
    <section class="ta-page-header-card">
        <div class="card-body">
            <div class="ta-page-header-row">
                <div>
                    <span class="ta-page-kicker"><i class="fas fa-truck" aria-hidden="true"></i> Trips</span>
                    <h1 class="ta-page-title">รอบขนส่ง</h1>
                    <p class="ta-page-subtitle">ติดตามสถานะงานขนส่ง คนขับรถ และยอด COD ของแต่ละรอบขนส่ง</p>
                </div>
                <div class="ta-page-actions">
                    <a class="btn btn-default" href="{{ route('admin.trips.export.csv', request()->only(['date_from', 'date_to', 'status', 'driver_name', 'car_id'])) }}">
                        <i class="fas fa-file-csv"></i> ส่งออก CSV
                    </a>
                    <a class="btn bg-success" href="{{ route('admin.trips.create') }}">
                        <i class="fas fa-plus"></i> สร้างรอบขนส่ง
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
    </section>

    <section class="card ta-toolbar-card">
        <div class="card-body">
            <form action="{{ route('admin.trips.index') }}" method="GET">
                <div class="ta-toolbar-grid">
                    <div class="ta-col-span-2">
                        <label for="date_from">วันที่เริ่ม</label>
                        <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="form-control">
                    </div>
                    <div class="ta-col-span-2">
                        <label for="date_to">วันที่สิ้นสุด</label>
                        <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="form-control">
                    </div>
                    <div class="ta-col-span-2">
                        <label for="status">สถานะ</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">ทั้งหมด</option>
                            @foreach($statusLabels as $status => $label)
                                <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ta-col-span-2">
                        <label for="driver_name">พนักงานขับรถ</label>
                        <input type="text" name="driver_name" id="driver_name" value="{{ request('driver_name') }}" class="form-control">
                    </div>
                    <div class="ta-col-span-2">
                        <label for="car_id">ทะเบียนรถ</label>
                        <input type="text" name="car_id" id="car_id" value="{{ request('car_id') }}" class="form-control">
                    </div>
                    <div class="ta-col-span-2">
                        <label for="area_name">พื้นที่</label>
                        <input type="text" name="area_name" id="area_name" value="{{ request('area_name') }}" class="form-control">
                    </div>
                    <div class="ta-col-span-12 ta-toolbar-actions">
                        <button type="submit" class="btn bg-info"><i class="fas fa-search"></i> ค้นหา</button>
                        <a href="{{ route('admin.trips.index') }}" class="btn btn-default"><i class="fas fa-redo"></i> ล้าง</a>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="card ta-table-card">
        <div class="card-header">
            <div>
                <h3 class="ta-section-title">รายการรอบขนส่ง</h3>
                <p class="ta-section-subtitle">สรุปจำนวนพัสดุ ยอด COD และสถานะปัจจุบันของแต่ละรอบ</p>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>วันที่</th>
                        <th>รหัสรอบ</th>
                        <th>พนักงานขับรถ</th>
                        <th>ทะเบียนรถ</th>
                        <th>พื้นที่</th>
                        <th class="text-right">พัสดุ</th>
                        <th class="text-right">COD</th>
                        <th class="text-right">เก็บแล้ว</th>
                        <th>สถานะ</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $key => $trip)
                        <tr>
                            <td>{{ $data->firstItem() + $key }}.</td>
                            <td>{{ optional($trip->trip_date)->format('Y-m-d') }}</td>
                            <td>{{ $trip->code }}</td>
                            <td>{{ $trip->driver_name }}</td>
                            <td>{{ $trip->car_id }}</td>
                            <td>{{ $trip->area_name }}</td>
                            <td class="text-right">{{ number_format($trip->total_parcels) }}</td>
                            <td class="text-right">{{ number_format($trip->total_cod_amount, 2) }}</td>
                            <td class="text-right">{{ number_format($trip->collected_amount, 2) }}</td>
                            <td><span class="badge {{ $trip->status_badge_class }}">{{ $trip->status_label }}</span></td>
                            <td class="text-right">
                                <div class="ta-table-actions">
                                    <a href="{{ route('admin.trips.show', $trip) }}" class="btn bg-primary btn-xs"><i class="fas fa-eye"></i> ดู</a>
                                    <a href="{{ route('admin.trips.driver', $trip) }}" class="btn btn-default btn-xs"><i class="fas fa-mobile-alt"></i> Driver</a>
                                    @if(! in_array($trip->status, [\App\Models\Trip::STATUS_COMPLETED, \App\Models\Trip::STATUS_CANCELLED], true))
                                        <a href="{{ route('admin.trips.edit', $trip) }}" class="btn bg-info btn-xs"><i class="fas fa-edit"></i> แก้ไข</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">
                                <div class="ta-empty-state">
                                    <div class="ta-empty-state__icon"><i class="fas fa-road"></i></div>
                                    <div>ไม่พบข้อมูล</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $data->links() }}
        </div>
    </section>
</div>
@endsection
