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
    <section class="ta-search-hero">
        <span class="ta-page-kicker"><i class="fas fa-search-location" aria-hidden="true"></i> Parcel Search</span>
        <h1 class="ta-search-hero__title">ค้นหาพัสดุ</h1>
        <p class="ta-search-hero__subtitle">ค้นหาด้วยรหัสพัสดุหรือชื่อผู้รับ ติดตามสถานะ และเข้าถึงข้อมูลออเดอร์อย่างรวดเร็ว</p>
        <div class="input-group">
            <input type="text" id="parcel_search" value="{{ $keyword }}" class="form-control" placeholder="กรอกรหัสพัสดุ เช่น P2026... หรือชื่อผู้รับ">
            <div class="input-group-append">
                <button type="button" id="parcel_search_btn" class="btn bg-primary"><i class="fas fa-search"></i> ค้นหา</button>
            </div>
        </div>
    </section>

    <section class="content">
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
    </section>

    <section class="card ta-table-card">
        <div class="card-header">
            <div>
                <h3 class="ta-section-title">ผลการค้นหา</h3>
                <p class="ta-section-subtitle">รายการพัสดุทั้งหมด ค้นหาตามรหัสพัสดุหรือชื่อผู้รับ</p>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-bordered table-striped mb-0" id="parcel_table" style="width:100%">
                <thead>
                    <tr>
                        <th>รหัสพัสดุ</th>
                        <th>ออเดอร์</th>
                        <th>ผู้รับ</th>
                        <th>ปลายทาง</th>
                        <th>สถานะ</th>
                        <th>วันที่</th>
                        <th style="width: 180px;"></th>
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
    const table = $('#parcel_table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        order: [[0, 'desc']],
        search: { search: @json($keyword) },
        ajax: @json(route('admin.parcels.search.data')),
        columns: [
            { data: 'parcel_code' },
            { data: 'order_code', orderable: false, searchable: false },
            { data: 'receive_name', orderable: false },
            { data: 'destination', orderable: false, searchable: false },
            { data: 'delivery_status', orderable: false, searchable: false },
            { data: 'created_at', searchable: false },
            { data: 'actions', orderable: false, searchable: false }
        ],
        language: @include('admin.partials._datatables-th')
    });

    // The hero search box drives the DataTables global search.
    $('#parcel_search_btn').on('click', function () {
        table.search($('#parcel_search').val()).draw();
    });
    $('#parcel_search').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            table.search($(this).val()).draw();
        }
    });
});
</script>
@endpush
