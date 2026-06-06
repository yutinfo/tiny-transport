@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <section class="content-header">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="d-flex flex-row">
                            <h5 class="font-weight-bold">รอบขนส่ง</h5>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><small>หน้าหลัก</small></a></li>
                                <li class="breadcrumb-item active"><small>รอบขนส่ง</small></li>
                            </ol>
                        </div>
                    </div>
                    <div class="col-sm-6 text-right">
                        <a class="btn bg-info" href="{{ route('admin.trips.export.csv', request()->only(['date_from', 'date_to', 'status', 'driver_name', 'car_id'])) }}">
                            <i class="fas fa-file-csv"></i> ส่งออก CSV
                        </a>
                        <a class="btn bg-success" href="{{ route('admin.trips.create') }}">
                            <i class="fas fa-plus"></i> สร้างรอบขนส่ง
                        </a>
                    </div>
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

        <div class="card">
            <form action="{{ route('admin.trips.index') }}" method="GET">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="date_from">วันที่เริ่ม</label>
                                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="date_to">วันที่สิ้นสุด</label>
                                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="status">สถานะ</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">ทั้งหมด</option>
                                    @foreach($statusLabels as $status => $label)
                                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="driver_name">พนักงานขับรถ</label>
                                <input type="text" name="driver_name" id="driver_name" value="{{ request('driver_name') }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="car_id">ทะเบียนรถ</label>
                                <input type="text" name="car_id" id="car_id" value="{{ request('car_id') }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="area_name">พื้นที่</label>
                                <input type="text" name="area_name" id="area_name" value="{{ request('area_name') }}" class="form-control">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn bg-info"><i class="fas fa-search"></i> ค้นหา</button>
                    <a href="{{ route('admin.trips.index') }}" class="btn bg-secondary"><i class="fas fa-redo"></i> ล้าง</a>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">รายการรอบขนส่ง</h3>
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
                                <td><span class="badge badge-info">{{ $trip->status_label }}</span></td>
                                <td class="text-right">
                                    <a href="{{ route('admin.trips.show', $trip) }}" class="btn bg-primary btn-xs"><i class="fas fa-eye"></i> ดู</a>
                                    <a href="{{ route('admin.trips.driver', $trip) }}" class="btn bg-success btn-xs"><i class="fas fa-mobile-alt"></i> Driver</a>
                                    @if(! in_array($trip->status, [\App\Models\Trip::STATUS_COMPLETED, \App\Models\Trip::STATUS_CANCELLED], true))
                                        <a href="{{ route('admin.trips.edit', $trip) }}" class="btn bg-info btn-xs"><i class="fas fa-edit"></i> แก้ไข</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted">ไม่พบข้อมูล</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $data->links() }}
            </div>
        </div>
    </section>
</div>
@endsection
