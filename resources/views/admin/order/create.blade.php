@extends('layouts.app')

@section('third_party_stylesheets')
<link rel="stylesheet" href="/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
<link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
@endsection

@section('third_party_scripts')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="/plugins/select2/js/select2.full.min.js"></script>
@endsection

@push('page_css')
    @include('admin.order.form-component.create-style')
@endpush()

@section('content')
<div class="container-fluid ta-page-shell">
    <form id="order_create" action="{{ route('admin.orders.store') }}" method="post" class="ta-page-shell">
        <section class="ta-page-header-card">
            <div class="card-body">
                <div class="ta-page-header-row">
                    <div>
                        <span class="ta-page-kicker"><i class="fas fa-box-open" aria-hidden="true"></i> Orders</span>
                        <h1 class="ta-page-title">บันทึกข้อมูลออเดอร์</h1>
                        <p class="ta-page-subtitle">เพิ่มผู้ฝาก ผู้รับ และรายละเอียดพัสดุในหน้าเดียว พร้อมสรุปรายการด้านซ้าย</p>
                    </div>
                    <div class="ta-page-actions">
                        <button type="button" class="btn btn-default btn-responsive" id="new_receiver">
                            <i class="fas fa-plus"></i> เพิ่มพัสดุและผู้รับ
                        </button>
                        <a class="btn bg-danger btn-responsive" href="{{ route('admin.orders.index') }}">
                            <i class="fas fa-times"></i> ยกเลิก
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    @include('layouts.alert-message')
                </div>
            </div>
        </section>

        <div class="ta-form-layout">
            <div class="ta-form-main">
                @include('admin.order.form-component.sender')
                @include('admin.order.form-component.driver')
                @include('admin.order.form-component.receivers-table')
            </div>
            <div class="ta-form-sidebar">
                @include('admin.order.form-component.receiver')
            </div>
        </div>

        <div class="ta-sticky-savebar">
            <div class="ta-sticky-savebar__inner">
                <div class="ta-sticky-savebar__text">
                    <strong>พร้อมบันทึกออเดอร์ใหม่</strong>
                    <span>ตรวจสอบข้อมูลผู้ฝาก ผู้รับ และรายการพัสดุก่อนยืนยัน</span>
                </div>
                <div class="ta-page-actions">
                    <a class="btn btn-default" href="{{ route('admin.orders.index') }}">ยกเลิก</a>
                    <button type="submit" class="btn btn-success btn-save">
                        <i class="fas fa-save"></i> บันทึก
                    </button>
                </div>
            </div>
        </div>
        @csrf
    </form>
</div>
@endsection

@push('page_scripts')
    @include('admin.order.form-component.create-script')
@endpush()
