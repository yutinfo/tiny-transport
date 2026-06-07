@extends('layouts.app')

@section('third_party_stylesheets')
<link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
<link rel="stylesheet" href="/plugins/daterangepicker/daterangepicker.css">
@endsection

@section('third_party_scripts')
<script src="/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/plugins/moment/moment.min.js"></script>
<script src="/plugins/daterangepicker/daterangepicker.js"></script>
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
@endsection

@push('page_css')
<style>
    .display-none {
        display: none;
    }
</style>
@endpush

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
            <form action="{{ route('admin.orders.index') }}" method="GET" id="generate_report">
                @csrf
                <div class="ta-toolbar-grid">
                    <div class="ta-col-span-6">
                        <label for="select_date">วันที่ค้นหา</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="far fa-calendar-alt"></i>
                                </span>
                            </div>
                            @if(count($selected[0]) >= 1)
                                <input type="hidden" name="db_date" value="{{ $selected[0]['db_date'] }}">
                                <input type="text" name="select_date" class="form-control float-right" id="select_date" value="{{ $selected[0]['select_date'] }}" placeholder="เลือกวัน">
                            @else
                                <input type="hidden" name="db_date" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                                <input type="text" name="select_date" class="form-control float-right" id="select_date" value="" placeholder="เลือกวัน">
                            @endif
                        </div>
                    </div>
                    <div class="ta-col-span-6 ta-toolbar-actions">
                        <button class="btn bg-info" id="view_report">
                            <i class="fas fa-search"></i> ค้นหา
                        </button>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-default">
                            <i class="fas fa-redo"></i> ล้างตัวกรอง
                        </a>
                    </div>
                </div>
            </form>
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
                <table class="table table-striped table-bordered dataTable dtr-inline" id="order_table">
                    <thead>
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>วันที่</th>
                            <th>รหัสพัสดุ</th>
                            <th>ข้อมูลพัสดุ</th>
                            <th>ชื่อผู้ฝาก</th>
                            <th>ชื่อผู้รับ</th>
                            <th>จังหวัด</th>
                            <th>จำนวนเงิน</th>
                            <th>รูปแบบการชำระเงิน</th>
                            <th>รูปแบบการจัดส่ง</th>
                            <th style="width: 10%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $immediately_total = 0;
                            $on_delivery_total = 0;
                        @endphp
                        @foreach ($data as $key => $value)
                            @php
                                if ($value['payment_type_id'] == 'immediately') {
                                    $immediately_total += $value['parcel_pice'];
                                } else {
                                    $on_delivery_total += $value['parcel_pice'];
                                }
                            @endphp
                            <tr>
                                <td>{{ $key + 1 }}.</td>
                                <td>{{ $value['created_at'] }}</td>
                                <td>{{ $value['parcel_code'] }}</td>
                                <td>{{ $value['parcel_description'] }}</td>
                                <td>{{ $value['customer_name'] }}</td>
                                <td>{{ $value['receive_name'] }}</td>
                                <td>{{ $value['province_name'] }}</td>
                                <td class="text-right">{{ number_format($value['parcel_pice'], 2) }}</td>
                                <td>{{ $value['payment_type'] }}</td>
                                <td>{{ $value['parcel_pickup_type'] }}</td>
                                <td>
                                    <div class="ta-table-actions">
                                        <a class="btn bg-info btn-xs" href="{{ route('admin.orders.edit', $value['order_id']) }}"><i class="fas fa-edit"></i> แก้ไข</a>
                                        <a class="btn bg-dark btn-xs" href="{{ route('admin.orders.labels', $value['order_id']) }}"><i class="fas fa-qrcode"></i> Label</a>
                                        <a class="btn bg-secondary btn-xs" href="{{ route('admin.parcels.tracking', $value['order_receive_id']) }}"><i class="fas fa-history"></i> ดูประวัติ</a>
                                        <button type="button" class="btn bg-danger btn-xs" onclick="dt('{{ $value['order_receive_id'] }}','{{ $value['customer_name'] }}')"><i class="fas fa-trash-alt"></i> ลบ</button>
                                    </div>
                                </td>
                            </tr>
                            @if ($value == end($data))
                                <tr class="display-none">
                                    <td>{{ $key + 2 }}.</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-right">{{ number_format($immediately_total, 2) }}</td>
                                    <th>รวมจ่ายทันที</th>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="display-none">
                                    <td>{{ $key + 3 }}.</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-right text-underline">{{ number_format($on_delivery_total, 2) }}</td>
                                    <th>รวมเก็บเงินปลายทาง</th>
                                    <td></td>
                                    <td></td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection

@push('page_scripts')
    @include('admin.order.form-component.list-script')
@endpush()
