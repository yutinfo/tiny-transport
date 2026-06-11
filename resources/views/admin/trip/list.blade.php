@extends('layouts.app')

@section('third_party_stylesheets')
<link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
@endsection

@section('third_party_scripts')
<script src="/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
@endsection

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
            <div class="ta-toolbar-grid">
                <div class="ta-col-span-3">
                    <label for="filter_trip_date">วันที่รอบรถ</label>
                    <input type="date" id="filter_trip_date" value="{{ $selected['date_from'] ?? '' }}" class="form-control">
                </div>
                <div class="ta-col-span-3">
                    <label for="filter_status">สถานะ</label>
                    <select id="filter_status" class="form-control">
                        <option value="">ทั้งหมด</option>
                        @foreach($statusLabels as $status => $label)
                            <option value="{{ $status }}" {{ ($selected['status'] ?? '') === $status ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ta-col-span-12 ta-toolbar-actions">
                    <button type="button" id="apply_filters" class="btn bg-info"><i class="fas fa-search"></i> ค้นหา</button>
                    <button type="button" id="reset_filters" class="btn btn-default"><i class="fas fa-redo"></i> ล้าง</button>
                </div>
            </div>
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
            <table class="table table-striped table-bordered" id="trips_table" style="width:100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>วันที่</th>
                        <th>รหัสรอบ</th>
                        <th>พนักงานขับรถ</th>
                        <th>สถานะ</th>
                        <th class="text-right">พัสดุ</th>
                        <th class="text-right">COD</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </section>
</div>
@endsection

@push('page_scripts')
<script>
$(function () {
    const table = $('#trips_table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        order: [[1, 'desc']],
        ajax: {
            url: @json(route('admin.trips.data')),
            data: function (d) {
                d.trip_date = $('#filter_trip_date').val();
                d.status = $('#filter_status').val();
            }
        },
        columns: [
            { data: 'row', orderable: false, searchable: false },
            { data: 'trip_date' },
            { data: 'code' },
            { data: 'driver_name', orderable: false },
            { data: 'status' },
            { data: 'items_count', className: 'text-right', orderable: false, searchable: false },
            { data: 'cod_amount', className: 'text-right', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false }
        ],
        language: @include('admin.partials._datatables-th')
    });

    $('#apply_filters').on('click', function () {
        table.ajax.reload();
    });

    $('#reset_filters').on('click', function () {
        $('#filter_trip_date').val('');
        $('#filter_status').val('');
        table.ajax.reload();
    });
});
</script>
@endpush
