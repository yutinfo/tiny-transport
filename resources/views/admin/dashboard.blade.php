@extends('layouts.app')

@section('third_party_stylesheets')
<link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
<link rel="stylesheet" href="/plugins/daterangepicker/daterangepicker.css">
<link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
<link rel="stylesheet" href="/plugins/chart.js/Chart.min.css">
@endsection

@section('third_party_scripts')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="/plugins/select2/js/select2.full.min.js"></script>
<script src="/plugins/moment/moment.min.js"></script>
<script src="/plugins/daterangepicker/daterangepicker.js"></script>
<script src="/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="/plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
<script src="/plugins/jszip/jszip.min.js"></script>
<script src="/plugins/pdfmake/pdfmake.min.js"></script>
<script src="/plugins/pdfmake/vfs_fonts.js"></script>
<script src="/plugins/chart.js/Chart.min.js"></script>
@endsection

@section('content')
@php
    $legacySelected = $selected[0] ?? [];
    $reportDate = Arr::get($legacySelected, 'db_date', date('Y-m-d'));
    $reportDisplayDate = Arr::get($legacySelected, 'select_date', '');
    $operationStatusLabel = blank($operationFilters['status']) ? 'ทั้งหมด' : ($tripStatusLabels[$operationFilters['status']] ?? $operationFilters['status']);

    $primaryMetrics = [
        [
            'label' => 'จำนวนพัสดุ',
            'value' => number_format((int) Arr::get($data, 'count_id', 0)),
            'icon' => 'fas fa-dolly-flatbed',
            'theme' => 'info',
            'note' => 'รายการพัสดุทั้งหมด',
        ],
        [
            'label' => 'ยอดเงินทั้งหมด',
            'value' => number_format((float) Arr::get($data, 'sum_parcel_pice', 0), 2),
            'icon' => 'far fa-flag',
            'theme' => 'success',
            'note' => 'รวมทุกช่องทางชำระ',
        ],
        [
            'label' => 'จ่ายทันที',
            'value' => number_format((float) Arr::get($data, 'parcel_pice_immediately', 0), 2),
            'icon' => 'fas fa-money-bill-alt',
            'theme' => 'warning',
            'note' => 'ชำระทันที',
            'progress' => (float) Arr::get($data, 'sum_parcel_pice', 0) > 0 ? (Arr::get($data, 'parcel_pice_immediately', 0) / Arr::get($data, 'sum_parcel_pice', 0)) * 100 : 0,
        ],
        [
            'label' => 'เก็บเงินปลายทาง',
            'value' => number_format((float) Arr::get($data, 'parcel_pice_on_delivery', 0), 2),
            'icon' => 'fas fa-money-check',
            'theme' => 'danger',
            'note' => 'ยอด COD จากพัสดุที่รอเก็บ',
            'progress' => (float) Arr::get($data, 'sum_parcel_pice', 0) > 0 ? (Arr::get($data, 'parcel_pice_on_delivery', 0) / Arr::get($data, 'sum_parcel_pice', 0)) * 100 : 0,
        ],
    ];
 
    $operationMetrics = [
        [
            'label' => 'รอบขนส่ง',
            'value' => number_format((int) ($operationKpis['trips_count'] ?? 0)),
            'icon' => 'fas fa-truck',
            'theme' => 'primary',
            'note' => 'ช่วงวันที่ที่เลือก',
        ],
        [
            'label' => 'พัสดุเข้ารอบ',
            'value' => number_format((int) ($operationKpis['assigned_count'] ?? 0)),
            'icon' => 'fas fa-boxes',
            'theme' => 'info',
            'note' => 'พัสดุที่ถูกมอบหมาย',
        ],
        [
            'label' => 'ส่งสำเร็จ',
            'value' => number_format((int) ($operationKpis['delivered_count'] ?? 0)),
            'icon' => 'fas fa-check',
            'theme' => 'success',
            'note' => 'จำนวนงานที่ปิดจบ',
            'progress' => ($operationKpis['assigned_count'] ?? 0) > 0 ? (($operationKpis['delivered_count'] ?? 0) / $operationKpis['assigned_count']) * 100 : 0,
        ],
        [
            'label' => 'อัตราส่งสำเร็จ',
            'value' => number_format((float) ($operationKpis['delivery_success_rate'] ?? 0), 2) . '%',
            'icon' => 'fas fa-percentage',
            'theme' => 'dark',
            'note' => 'ประสิทธิภาพการส่ง',
            'progress' => (float) ($operationKpis['delivery_success_rate'] ?? 0),
        ],
    ];

    $deliveryBadgeClasses = [
        \App\Models\TripItem::DELIVERY_STATUS_WAITING => 'badge-secondary',
        \App\Models\TripItem::DELIVERY_STATUS_PICKED_UP => 'badge-info',
        \App\Models\TripItem::DELIVERY_STATUS_IN_TRANSIT => 'badge-primary',
        \App\Models\TripItem::DELIVERY_STATUS_DELIVERED => 'badge-success',
        \App\Models\TripItem::DELIVERY_STATUS_FAILED => 'badge-danger',
        \App\Models\TripItem::DELIVERY_STATUS_RETURNED => 'badge-warning',
    ];

    $tripBadgeClasses = [
        \App\Models\Trip::STATUS_DRAFT => 'badge-secondary',
        \App\Models\Trip::STATUS_ASSIGNED => 'badge-info',
        \App\Models\Trip::STATUS_IN_TRANSIT => 'badge-primary',
        \App\Models\Trip::STATUS_PENDING_VERIFICATION => 'badge-warning',
        \App\Models\Trip::STATUS_COMPLETED => 'badge-success',
        \App\Models\Trip::STATUS_CANCELLED => 'badge-danger',
    ];

    $reportTotals = [
        'immediately' => 0,
        'on_delivery' => 0,
    ];

    foreach ($dataTable as $reportRow) {
        if (($reportRow['payment_type_id'] ?? null) === 'immediately') {
            $reportTotals['immediately'] += (float) ($reportRow['parcel_pice'] ?? 0);
            continue;
        }

        $reportTotals['on_delivery'] += (float) ($reportRow['parcel_pice'] ?? 0);
    }
@endphp

<div class="container-fluid dashboard-page">
    <section class="dashboard-topbar">
        <div class="dashboard-topbar__main">
            <div class="dashboard-titleblock">
                <span class="dashboard-kicker">
                    <i class="fas fa-chart-line" aria-hidden="true"></i>
                    ภาพรวมการขนส่ง
                </span>
                <h1 class="dashboard-title">แดชบอร์ดการขนส่ง</h1>
                <p class="dashboard-subtitle">ติดตาม KPI รอบขนส่ง พัสดุ และรายงานตามตัวกรองที่เลือก</p>
            </div>
            <div class="dashboard-actions">
                <a href="{{ route('admin.trips.export.csv', request()->only(['date_from', 'date_to', 'driver_name', 'status'])) }}" class="btn bg-success btn-sm">
                    <i class="fas fa-file-csv"></i> ส่งออก CSV
                </a>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-default btn-sm">
                    <i class="fas fa-redo"></i> ล้างตัวกรอง
                </a>
            </div>
        </div>
        <div class="dashboard-filter-tags" aria-label="ตัวกรองปัจจุบัน">
            <span class="dashboard-chip">ช่วงวันที่: {{ $operationFilters['date_from'] }} - {{ $operationFilters['date_to'] }}</span>
            <span class="dashboard-chip">พนักงานขับรถ: {{ $operationFilters['driver_name'] !== '' ? $operationFilters['driver_name'] : 'ทั้งหมด' }}</span>
            <span class="dashboard-chip">สถานะรอบ: {{ $operationStatusLabel }}</span>
        </div>
    </section>

    <section class="card dashboard-toolbar mb-3">
        <form action="{{ route('admin.dashboard') }}" method="GET">
            <div class="card-body">
                <div class="dashboard-filter-heading">
                    <span class="dashboard-filter-title">
                        <i class="fas fa-filter" aria-hidden="true"></i>
                        ตัวกรองข้อมูล
                    </span>
                    <small class="text-muted">ปรับช่วงวันที่ พนักงาน และสถานะเพื่ออัปเดตตัวเลขด้านล่าง</small>
                </div>
                <div class="form-row align-items-end">
                    <div class="form-group col-xl-2 col-lg-3 col-md-6">
                        <label for="date_from">วันที่เริ่ม</label>
                        <input type="date" name="date_from" id="date_from" value="{{ $operationFilters['date_from'] }}" class="form-control">
                    </div>
                    <div class="form-group col-xl-2 col-lg-3 col-md-6">
                        <label for="date_to">วันที่สิ้นสุด</label>
                        <input type="date" name="date_to" id="date_to" value="{{ $operationFilters['date_to'] }}" class="form-control">
                    </div>
                    <div class="form-group col-xl-3 col-lg-3 col-md-6">
                        <label for="driver_name">พนักงานขับรถ</label>
                        <input type="text" name="driver_name" id="driver_name" value="{{ $operationFilters['driver_name'] }}" class="form-control" placeholder="ค้นหาชื่อพนักงาน">
                    </div>
                    <div class="form-group col-xl-2 col-lg-3 col-md-6">
                        <label for="status">สถานะรอบ</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">ทั้งหมด</option>
                            @foreach($tripStatusLabels as $status => $label)
                                <option value="{{ $status }}" {{ $operationFilters['status'] === $status ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-xl-2 col-lg-3 col-md-6">
                        <button type="submit" class="btn bg-info btn-block">
                            <i class="fas fa-search"></i> ค้นหา
                        </button>
                    </div>
                    <div class="form-group col-xl-1 col-lg-2 col-md-6">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-default btn-block">
                            <i class="fas fa-redo"></i> ล้าง
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </section>

    <section class="dashboard-section mb-3">
        <div class="dashboard-section-heading">
            <div>
                <span class="dashboard-section-kicker">KPI การขนส่ง</span>
                <h3>สรุปการขนส่ง</h3>
                <p>ตัวเลขสำคัญที่ต้องอ่านก่อนลงรายละเอียด</p>
            </div>
        </div>

        <div class="row">
            @foreach($primaryMetrics as $metric)
                <div class="col-lg-3 col-sm-6 mb-3">
                    <div class="card dashboard-metric dashboard-metric--{{ $metric['theme'] }}">
                        <div class="card-body">
                            <div class="dashboard-metric__head">
                                <div>
                                    <span class="dashboard-metric__label">{{ $metric['label'] }}</span>
                                    <div class="dashboard-metric__value">{{ $metric['value'] }}</div>
                                </div>
                                <span class="dashboard-metric__icon" aria-hidden="true">
                                    <i class="{{ $metric['icon'] }}"></i>
                                </span>
                            </div>
                            @if(isset($metric['progress']))
                                <div class="progress mt-2" style="height: 4px; border-radius: 2px; background: rgba(0,0,0,0.06);">
                                    <div class="progress-bar bg-{{ $metric['theme'] }}" role="progressbar" style="width: {{ $metric['progress'] }}%" aria-valuenow="{{ $metric['progress'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            @endif
                            <div class="dashboard-metric__note mt-1">{{ $metric['note'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row">
            @foreach($operationMetrics as $metric)
                <div class="col-lg-3 col-sm-6 mb-3">
                    <div class="card dashboard-metric dashboard-metric--{{ $metric['theme'] }}">
                        <div class="card-body">
                            <div class="dashboard-metric__head">
                                <div>
                                    <span class="dashboard-metric__label">{{ $metric['label'] }}</span>
                                    <div class="dashboard-metric__value">{{ $metric['value'] }}</div>
                                </div>
                                <span class="dashboard-metric__icon" aria-hidden="true">
                                    <i class="{{ $metric['icon'] }}"></i>
                                </span>
                            </div>
                            @if(isset($metric['progress']))
                                <div class="progress mt-2" style="height: 4px; border-radius: 2px; background: rgba(0,0,0,0.06);">
                                    <div class="progress-bar bg-{{ $metric['theme'] === 'dark' ? 'primary' : $metric['theme'] }}" role="progressbar" style="width: {{ $metric['progress'] }}%" aria-valuenow="{{ $metric['progress'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            @endif
                            <div class="dashboard-metric__note mt-1">{{ $metric['note'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="row mb-4">
        <div class="col-lg-8 mb-3">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title font-weight-bold mb-0">แนวโน้มการจัดส่งพัสดุรายวัน</h3>
                    <div class="card-tools ml-auto">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="deliveryTrendChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title font-weight-bold mb-0">สถานะจัดส่ง</h3>
                    <div class="card-tools ml-auto">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <canvas id="deliveryStatusChart" style="min-height: 160px; height: 160px; max-height: 160px; max-width: 100%;"></canvas>
                        </div>
                        <div class="col-6 p-0">
                            <table class="table table-sm table-borderless mb-0" style="font-size: 0.8rem;">
                                <tbody>
                                    @foreach($deliveryStatusLabels as $status => $label)
                                        <tr>
                                            <td class="p-1">
                                                <span class="dashboard-status-badge {{ $deliveryBadgeClasses[$status] ?? 'badge-secondary' }}"></span>
                                                {{ $label }}
                                            </td>
                                            <td class="p-1 text-right font-weight-bold">{{ number_format((int) ($deliveryBreakdown[$status] ?? 0)) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title font-weight-bold mb-0">ความคืบหน้าการเก็บเงินปลายทาง (COD)</h3>
                    <div class="card-tools ml-auto">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <canvas id="codChart" style="min-height: 160px; height: 160px; max-height: 160px; max-width: 100%;"></canvas>
                        </div>
                        <div class="col-6">
                            <table class="table table-sm table-borderless mb-0" style="font-size: 0.85rem;">
                                <tbody>
                                    <tr>
                                        <td class="p-1 text-muted">ยอด COD รวม</td>
                                        <td class="p-1 text-right font-weight-bold text-primary">{{ number_format($codSummary['total_cod_amount'], 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="p-1 text-muted">ยอดเก็บแล้ว</td>
                                        <td class="p-1 text-right font-weight-bold text-success">{{ number_format($codSummary['collected_amount'], 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="p-1 text-muted">ยอด COD คงเหลือ</td>
                                        <td class="p-1 text-right font-weight-bold text-danger">{{ number_format($codSummary['remaining_cod_amount'], 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title font-weight-bold mb-0">รอบขนส่งตามสถานะ</h3>
                    <div class="card-tools ml-auto">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <canvas id="tripsChart" style="min-height: 160px; height: 160px; max-height: 160px; max-width: 100%;"></canvas>
                        </div>
                        <div class="col-6">
                            <table class="table table-sm table-borderless mb-0" style="font-size: 0.8rem;">
                                <tbody>
                                    @foreach($tripStatusLabels as $status => $label)
                                        <tr>
                                            <td class="p-1">
                                                <span class="dashboard-status-badge {{ $tripBadgeClasses[$status] ?? 'badge-secondary' }}"></span>
                                                {{ $label }}
                                            </td>
                                            <td class="p-1 text-right font-weight-bold">{{ number_format((int) ($tripsByStatus[$status] ?? 0)) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="card dashboard-recent-card mb-4">
        <div class="card-header d-flex flex-wrap align-items-center">
            <div>
                <h3 class="card-title mb-1">รอบขนส่งล่าสุด</h3>
                <small class="text-muted">10 รายการล่าสุดในช่วงวันที่เลือก</small>
            </div>
            <div class="card-tools ml-auto d-flex align-items-center">
                <a href="{{ route('admin.trips.index', request()->only(['date_from', 'date_to', 'driver_name', 'status'])) }}" class="btn btn-default btn-sm mr-2 mt-2 mt-sm-0">
                    <i class="fas fa-list"></i> ดูทุกรอบ
                </a>
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-striped mb-0 dashboard-table">
                <thead>
                    <tr>
                        <th>วันที่</th>
                        <th>รหัสรอบ</th>
                        <th>พนักงานขับรถ</th>
                        <th>ทะเบียนรถ</th>
                        <th>พัสดุ</th>
                        <th>ส่งสำเร็จ / ไม่สำเร็จ / คงเหลือ</th>
                        <th class="text-right">เก็บ COD</th>
                        <th>สถานะ</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTrips as $trip)
                        @php
                            $delivered = $trip->tripItems->where('delivery_status', \App\Models\TripItem::DELIVERY_STATUS_DELIVERED)->count();
                            $failed = $trip->tripItems->where('delivery_status', \App\Models\TripItem::DELIVERY_STATUS_FAILED)->count();
                            $returned = $trip->tripItems->where('delivery_status', \App\Models\TripItem::DELIVERY_STATUS_RETURNED)->count();
                            $remaining = max(0, $trip->tripItems->count() - $delivered - $failed - $returned);
                        @endphp
                        <tr>
                            <td>{{ optional($trip->trip_date)->format('Y-m-d') }}</td>
                            <td>{{ $trip->code }}</td>
                            <td>{{ $trip->driver_name ?: '-' }}</td>
                            <td>{{ $trip->car_id ?: '-' }}</td>
                            <td>{{ number_format((int) $trip->total_parcels) }}</td>
                            <td>{{ number_format((int) $delivered) }} / {{ number_format((int) $failed) }} / {{ number_format((int) $remaining) }}</td>
                            <td class="text-right">{{ number_format((float) $trip->collected_amount, 2) }}</td>
                            <td><span class="badge {{ $tripBadgeClasses[$trip->status] ?? 'badge-secondary' }}">{{ $trip->status_label }}</span></td>
                            <td class="text-right">
                                <a href="{{ route('admin.trips.show', $trip) }}" class="btn bg-primary btn-xs"><i class="fas fa-eye"></i> ดู</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">ไม่พบรอบขนส่งในช่วงวันที่เลือก</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="card dashboard-report-panel mb-4">
        <div class="card-header">
            <div class="d-flex flex-wrap justify-content-between align-items-start">
                <div class="mb-2 mb-lg-0">
                    <h3 class="card-title mb-1">รายงานพัสดุ</h3>
                    <small class="text-muted">ค้นหาพัสดุแบบละเอียดตามจังหวัดและวันที่</small>
                </div>
                <div class="d-flex align-items-center">
                    <div class="dashboard-report-summary">
                        <div class="dashboard-mini-stat">
                            <span>รวมจ่ายทันที</span>
                            <strong>{{ number_format($reportTotals['immediately'], 2) }}</strong>
                        </div>
                        <div class="dashboard-mini-stat">
                            <span>รวมเก็บเงินปลายทาง</span>
                            <strong>{{ number_format($reportTotals['on_delivery'], 2) }}</strong>
                        </div>
                    </div>
                    <div class="card-tools ml-2">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.dashboard') }}" method="GET" id="generate_report" class="dashboard-report-form mt-3">
                <div class="form-row">
                    <div class="form-group col-lg-4 col-md-5">
                        <label for="select_province" class="sr-only">จังหวัด</label>
                        <select class="form-control" id="select_province" name="select_province">
                            <option value="">เลือกจังหวัด</option>
                            @foreach ($province as $item)
                                <option {{ $item['id'] == Arr::get($legacySelected, 'select_province') ? 'selected' : '' }} value="{{ $item['id'] }}">{{ $item['name_th'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-lg-4 col-md-4">
                        <label for="select_date" class="sr-only">วันที่รายงาน</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="far fa-calendar-alt"></i>
                                </span>
                            </div>
                            <input type="hidden" name="db_date" value="{{ $reportDate }}">
                            <input type="text" name="select_date" class="form-control float-right" id="select_date" value="{{ $reportDisplayDate }}" placeholder="เลือกวัน">
                        </div>
                    </div>
                    <div class="form-group col-lg-4 col-md-3 d-flex align-items-end">
                        <button class="btn bg-info btn-block" id="view_report">
                            <i class="far fa-chart-bar"></i> ดูรายงาน
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive ta-table-panel">
                <table class="table table-striped table-bordered dataTable dtr-inline mb-0 dashboard-report-table" id="order_table">
                    <thead>
                        <tr>
                            <th>วันที่</th>
                            <th>รหัสพัสดุ</th>
                            <th>ข้อมูลพัสดุ</th>
                            <th>ชื่อผู้ฝาก</th>
                            <th>ชื่อผู้รับ</th>
                            <th>จังหวัดจัดส่ง</th>
                            <th>จำนวนเงิน</th>
                            <th>รูปแบบการชำระเงิน</th>
                            <th>รูปแบบการจัดส่ง</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dataTable as $value)
                            <tr>
                                <td>{{ $value['created_at'] }}</td>
                                <td>{{ $value['parcel_code'] }}</td>
                                <td>{{ $value['parcel_description'] }}</td>
                                <td>{{ $value['customer_name'] }}</td>
                                <td>{{ $value['receive_name'] }}</td>
                                <td>{{ $value['province_name'] }}</td>
                                <td class="text-right">{{ number_format($value['parcel_pice'], 2) }}</td>
                                <td>{{ $value['payment_type'] }}</td>
                                <td>{{ $value['parcel_pickup_type'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection

@push('page_scripts')
    @include('admin.dashboard.dashboard-script')
@endpush
