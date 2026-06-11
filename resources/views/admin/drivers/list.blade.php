@extends('layouts.app')

@php
    $isAdmin = auth()->user()->role_name === 'admin';
@endphp

@section('content')
<div class="container-fluid ta-page-shell">
    <section class="ta-page-header-card">
        <div class="card-body">
            <div class="ta-page-header-row">
                <div>
                    <span class="ta-page-kicker"><i class="fas fa-id-card" aria-hidden="true"></i> Drivers</span>
                    <h1 class="ta-page-title">คนขับรถ</h1>
                    <p class="ta-page-subtitle">ฐานข้อมูลคนขับรถ เบอร์ติดต่อ ทะเบียนรถ และบัญชีเข้าสู่ระบบ</p>
                </div>
                <div class="ta-page-actions">
                    @if($isAdmin)
                        <a class="btn bg-success" href="{{ route('admin.drivers.create') }}">
                            <i class="fas fa-plus"></i> เพิ่มคนขับ
                        </a>
                    @endif
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
            <form action="{{ route('admin.drivers.index') }}" method="GET">
                <div class="ta-toolbar-grid">
                    <div class="ta-col-span-4">
                        <label for="keyword">ค้นหา</label>
                        <input type="text" name="keyword" id="keyword" value="{{ $selected['keyword'] ?? '' }}" class="form-control" placeholder="ชื่อ / เบอร์ / ทะเบียนรถ / รหัส">
                    </div>
                    <div class="ta-col-span-2">
                        <label for="status">สถานะ</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">ทั้งหมด</option>
                            @foreach($statusLabels as $value => $label)
                                <option value="{{ $value }}" {{ ($selected['status'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ta-col-span-2">
                        <label for="has_account">บัญชี login</label>
                        <select name="has_account" id="has_account" class="form-control">
                            <option value="">ทั้งหมด</option>
                            <option value="yes" {{ ($selected['has_account'] ?? '') === 'yes' ? 'selected' : '' }}>มีบัญชี</option>
                            <option value="no" {{ ($selected['has_account'] ?? '') === 'no' ? 'selected' : '' }}>ไม่มีบัญชี</option>
                        </select>
                    </div>
                    <div class="ta-col-span-12 ta-toolbar-actions">
                        <button type="submit" class="btn bg-info"><i class="fas fa-search"></i> ค้นหา</button>
                        <a href="{{ route('admin.drivers.index') }}" class="btn btn-default"><i class="fas fa-redo"></i> ล้าง</a>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="card ta-table-card">
        <div class="card-header">
            <div>
                <h3 class="ta-section-title">รายชื่อคนขับ</h3>
                <p class="ta-section-subtitle">สรุปข้อมูลติดต่อ สถานะ และจำนวนรอบขนส่งของคนขับแต่ละคน</p>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>รหัส</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>เบอร์โทร</th>
                        <th>ทะเบียนรถ</th>
                        <th>พื้นที่</th>
                        <th>บัญชี</th>
                        <th>สถานะ</th>
                        <th>วันนี้</th>
                        <th class="text-right">รอบทั้งหมด</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $driver)
                        <tr>
                            <td>{{ $driver->code }}</td>
                            <td>{{ $driver->full_name }}</td>
                            <td>
                                @if($driver->mobile)
                                    <a href="tel:{{ $driver->mobile }}">{{ $driver->mobile }}</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $driver->license_plate ?: '-' }}</td>
                            <td>{{ $driver->area_name ?: '-' }}</td>
                            <td>
                                @if($driver->user_id)
                                    <span class="badge badge-success">มี</span>
                                @else
                                    <span class="badge badge-secondary">ไม่มี</span>
                                @endif
                            </td>
                            <td><span class="badge {{ $driver->status_badge_class }}">{{ $driver->status_label }}</span></td>
                            <td>
                                @if($busyToday->has($driver->id))
                                    <a href="{{ route('admin.trips.show', $busyToday->get($driver->id)) }}" class="badge badge-danger">มีรอบ</a>
                                @else
                                    <span class="badge badge-light">ว่าง</span>
                                @endif
                            </td>
                            <td class="text-right">{{ number_format($driver->trips_count) }}</td>
                            <td class="text-right">
                                <div class="ta-table-actions">
                                    <a href="{{ route('admin.drivers.show', $driver) }}" class="btn bg-primary btn-xs"><i class="fas fa-eye"></i> ดู</a>
                                    @if($isAdmin)
                                        <a href="{{ route('admin.drivers.edit', $driver) }}" class="btn bg-info btn-xs"><i class="fas fa-edit"></i> แก้ไข</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="ta-empty-state">
                                    <div class="ta-empty-state__icon"><i class="fas fa-user-slash"></i></div>
                                    <div>ไม่พบข้อมูลคนขับ</div>
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
