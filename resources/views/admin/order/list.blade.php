@extends('layouts.app')

@section('third_party_stylesheets')
<link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="/plugins/daterangepicker/daterangepicker.css">
@endsection

@section('third_party_scripts')
<script src="/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/plugins/moment/moment.min.js"></script>
<script src="/plugins/daterangepicker/daterangepicker.js"></script>
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
                    <span class="ta-page-kicker"><i class="fas fa-box-open" aria-hidden="true"></i> Orders</span>
                    <h1 class="ta-page-title">รายการจัดการออเดอร์</h1>
                    <p class="ta-page-subtitle">ติดตามรายการพัสดุ ค้นหาตามวันที่ และจัดการออเดอร์ได้จากหน้าจอเดียว</p>
                </div>
                <div class="ta-page-actions">
                    <a class="btn bg-success" href="{{ route('admin.orders.create') }}">
                        <i class="fas fa-plus"></i> เพิ่มรายการ
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="card ta-toolbar-card">
        <div class="card-body">
            <div class="ta-toolbar-grid">
                <div class="ta-col-span-6">
                    <label for="select_date">วันที่ค้นหา</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="far fa-calendar-alt"></i>
                            </span>
                        </div>
                        <input type="hidden" name="db_date" id="db_date" value="{{ $dbDate }}">
                        <input type="text" name="select_date" class="form-control float-right" id="select_date" value="{{ $selectDate }}" placeholder="เลือกวัน">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="card ta-state-card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-2 mb-md-0">
                    <div class="card dashboard-metric dashboard-metric--success mb-0">
                        <div class="card-body">
                            <div class="dashboard-metric__head">
                                <div>
                                    <span class="dashboard-metric__label">รวมจ่ายเงินทันที</span>
                                    <span class="dashboard-metric__value" id="summary-immediately">{{ number_format($summary['immediately_total'], 2) }}</span>
                                </div>
                                <span class="dashboard-metric__icon" aria-hidden="true"><i class="fas fa-hand-holding-usd"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card dashboard-metric dashboard-metric--warning mb-0">
                        <div class="card-body">
                            <div class="dashboard-metric__head">
                                <div>
                                    <span class="dashboard-metric__label">รวมเก็บเงินปลายทาง</span>
                                    <span class="dashboard-metric__value" id="summary-on-delivery">{{ number_format($summary['on_delivery_total'], 2) }}</span>
                                </div>
                                <span class="dashboard-metric__icon" aria-hidden="true"><i class="fas fa-truck-loading"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="card ta-table-card">
        <div class="card-header">
            <div>
                <h3 class="ta-section-title">รายการจัดส่ง</h3>
                <p class="ta-section-subtitle">ตารางรายการพัสดุพร้อมยอดจ่ายทันทีและเก็บเงินปลายทาง</p>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive ta-table-panel">
                <table class="table table-striped table-bordered dataTable dtr-inline" id="order_table" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>วันที่</th>
                            <th>รหัสพัสดุ</th>
                            <th>ข้อมูลพัสดุ</th>
                            <th>ชื่อผู้ฝาก</th>
                            <th>ชื่อผู้รับ</th>
                            <th>จังหวัด</th>
                            <th class="text-right">จำนวนเงิน</th>
                            <th>รูปแบบการชำระเงิน</th>
                            <th>รูปแบบการจัดส่ง</th>
                            <th style="width: 10%"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection

@push('page_scripts')
    @include('admin.order.form-component.list-script')
@endpush
