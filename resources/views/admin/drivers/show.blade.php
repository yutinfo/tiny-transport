@extends('layouts.app')

@php
    $isAdmin = auth()->user()->role_name === 'admin';
    $hasTrips = $stats['total_trips'] > 0;
    $statCards = [
        ['value' => number_format($stats['total_trips']), 'label' => 'รอบทั้งหมด'],
        ['value' => number_format($stats['this_month']), 'label' => 'รอบเดือนนี้'],
        ['value' => number_format($stats['success_rate'], 1) . '%', 'label' => 'ส่งสำเร็จ'],
        ['value' => number_format($stats['cod_collected'], 2), 'label' => 'COD เก็บสะสม'],
    ];
@endphp

@section('content')
<div class="container-fluid ta-page-shell">
    <section class="ta-page-header-card">
        <div class="card-body">
            <div class="ta-page-header-row">
                <div>
                    <span class="ta-page-kicker"><i class="fas fa-id-card" aria-hidden="true"></i> Drivers</span>
                    <h1 class="ta-page-title">{{ $data->full_name }} <small class="text-muted">{{ $data->code }}</small></h1>
                    <p class="ta-page-subtitle">ข้อมูลคนขับ สถิติการจัดส่ง และประวัติรอบขนส่งล่าสุด</p>
                </div>
                <div class="ta-page-actions">
                    <a href="{{ route('admin.drivers.index') }}" class="btn btn-default"><i class="fas fa-arrow-left"></i> กลับ</a>
                    @if($isAdmin)
                        <a href="{{ route('admin.drivers.edit', $data) }}" class="btn bg-info"><i class="fas fa-edit"></i> แก้ไข</a>
                        <form action="{{ route('admin.drivers.toggle-status', $data) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('{{ $data->status === \App\Models\Driver::STATUS_ACTIVE ? 'ปิดใช้งานคนขับรายนี้? บัญชี login ที่ผูกจะเข้าระบบไม่ได้' : 'เปิดใช้งานคนขับรายนี้? บัญชี login ที่ผูกจะกลับมาใช้งานได้' }}')">
                            @csrf
                            @if($data->status === \App\Models\Driver::STATUS_ACTIVE)
                                <button type="submit" class="btn bg-warning"><i class="fas fa-ban"></i> ปิดใช้งาน</button>
                            @else
                                <button type="submit" class="btn bg-success"><i class="fas fa-check"></i> เปิดใช้งาน</button>
                            @endif
                        </form>
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

    <section class="card ta-state-card">
        <div class="card-header">
            <div>
                <h3 class="ta-section-title">สถิติการจัดส่ง</h3>
                <p class="ta-section-subtitle">สรุปผลงานคนขับจากรอบขนส่งทั้งหมดในระบบ</p>
            </div>
        </div>
        <div class="card-body">
            <div class="ta-kpi-grid">
                @foreach($statCards as $card)
                    <article class="ta-kpi-card">
                        <span class="ta-kpi-card__value">{{ $card['value'] }}</span>
                        <span class="ta-kpi-card__label">{{ $card['label'] }}</span>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <div class="ta-form-layout">
        <div class="ta-form-main">
            <section class="card ta-table-card">
                <div class="card-header">
                    <div>
                        <h3 class="ta-section-title">ประวัติรอบขนส่งล่าสุด</h3>
                        <p class="ta-section-subtitle">10 รอบล่าสุด</p>
                    </div>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>วันที่</th>
                                <th>รหัสรอบ</th>
                                <th class="text-right">พัสดุ</th>
                                <th class="text-right">COD</th>
                                <th>สถานะ</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($trips as $trip)
                                <tr>
                                    <td>{{ optional($trip->trip_date)->format('Y-m-d') }}</td>
                                    <td>{{ $trip->code }}</td>
                                    <td class="text-right">{{ number_format($trip->total_parcels) }}</td>
                                    <td class="text-right">{{ number_format($trip->total_cod_amount, 2) }}</td>
                                    <td><span class="badge {{ $trip->status_badge_class }}">{{ $trip->status_label }}</span></td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.trips.show', $trip) }}" class="btn bg-primary btn-xs"><i class="fas fa-eye"></i> ดู</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="ta-empty-state">
                                            <div class="ta-empty-state__icon"><i class="fas fa-road"></i></div>
                                            <div>ยังไม่มีรอบขนส่ง</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="ta-form-sidebar">
            <section class="card ta-state-card">
                <div class="card-header">
                    <div>
                        <h3 class="ta-section-title">ข้อมูลคนขับ</h3>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="ta-info-list mb-0">
                        <div class="ta-info-list__item"><dt>รหัส</dt><dd>{{ $data->code }}</dd></div>
                        <div class="ta-info-list__item"><dt>ชื่อ-นามสกุล</dt><dd>{{ $data->full_name }}</dd></div>
                        <div class="ta-info-list__item">
                            <dt>เบอร์โทร</dt>
                            <dd>@if($data->mobile)<a href="tel:{{ $data->mobile }}">{{ $data->mobile }}</a>@else - @endif</dd>
                        </div>
                        <div class="ta-info-list__item"><dt>ทะเบียนรถ</dt><dd>{{ $data->license_plate ?: '-' }}</dd></div>
                        <div class="ta-info-list__item"><dt>เลขใบขับขี่</dt><dd>{{ $data->driver_license_no ?: '-' }}</dd></div>
                        <div class="ta-info-list__item"><dt>พื้นที่ประจำ</dt><dd>{{ $data->area_name ?: '-' }}</dd></div>
                        <div class="ta-info-list__item">
                            <dt>สถานะ</dt>
                            <dd><span class="badge {{ $data->status_badge_class }}">{{ $data->status_label }}</span></dd>
                        </div>
                        <div class="ta-info-list__item">
                            <dt>บัญชี login</dt>
                            <dd>
                                @if($data->user)
                                    <span class="badge badge-success">มี</span> {{ $data->user->username }}
                                    <span class="badge {{ $data->user->status === 'active' ? 'badge-success' : 'badge-secondary' }}">{{ $data->user->status }}</span>
                                @else
                                    <span class="badge badge-secondary">ไม่มี</span>
                                @endif
                            </dd>
                        </div>
                        @if($data->note)
                            <div class="ta-info-list__item"><dt>หมายเหตุ</dt><dd>{{ $data->note }}</dd></div>
                        @endif
                    </dl>

                    @if($isAdmin)
                        <hr>
                        <div class="ta-page-actions">
                            @if($hasTrips)
                                <button type="button" class="btn btn-danger" disabled title="มีรอบขนส่งผูกอยู่ ลบไม่ได้">
                                    <i class="fas fa-trash"></i> ลบ (มีรอบผูกอยู่)
                                </button>
                                <p class="text-muted small mb-0 mt-2">คนขับรายนี้มีรอบขนส่งผูกอยู่ ลบไม่ได้ — ใช้ "ปิดใช้งาน" แทน</p>
                            @else
                                <form action="{{ route('admin.drivers.destroy', $data) }}" method="POST"
                                      onsubmit="return confirm('ยืนยันการลบคนขับรายนี้?')">
                                    @csrf
                                    @method('DELETE')
                                    @if($data->user)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="delete_account" id="delete_account" value="1">
                                            <label class="form-check-label" for="delete_account">ลบบัญชี login ({{ $data->user->username }}) ด้วย</label>
                                        </div>
                                    @endif
                                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> ลบคนขับ</button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
