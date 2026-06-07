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
    @include('admin.order.edit-form-component.edit-style')
@endpush()

@section('content')
<div class="container-fluid ta-page-shell">
    <form id="order_update" action="{{ route('admin.orders.update', $data->id) }}" method="POST" class="ta-page-shell">
        @method("PUT")
        <section class="ta-page-header-card">
            <div class="card-body">
                <div class="ta-page-header-row">
                    <div>
                        <span class="ta-page-kicker"><i class="fas fa-box-open" aria-hidden="true"></i> Orders</span>
                        <h1 class="ta-page-title">แก้ไขข้อมูลออเดอร์</h1>
                        <p class="ta-page-subtitle">อัปเดตข้อมูลผู้ฝาก ผู้รับ และรายการพัสดุของออเดอร์ #{{ $data->id }}</p>
                    </div>
                    <div class="ta-page-actions">
                        <a class="btn bg-danger" href="{{ route('admin.orders.index') }}">
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
                @include('admin.order.edit-form-component.sender')
                @include('admin.order.edit-form-component.driver')
            </div>
            <div class="ta-form-sidebar">
                @foreach ($data->receivers as $data_item)
                    @include('admin.order.edit-form-component.receiver', ['data_item' => $data_item])
                @endforeach
            </div>
        </div>

        <div class="ta-sticky-savebar">
            <div class="ta-sticky-savebar__inner">
                <div class="ta-sticky-savebar__text">
                    <strong>พร้อมบันทึกการเปลี่ยนแปลง</strong>
                    <span>หลังบันทึก ระบบจะอัปเดตรายการจัดส่งและข้อมูลติดตามทันที</span>
                </div>
                <div class="ta-page-actions">
                    <a class="btn btn-default" href="{{ route('admin.orders.index') }}">ยกเลิก</a>
                    <button type="submit" class="btn btn-success">
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
@if(session()->has('errors'))
<script>
    $(function() {
        $(".msg-alert-danger-show-text").html("");
        $(".msg-alert-danger-show-text").append("{{ $errors }}")
        $(".msg-alert-danger").show("slow");
        $("html, body").animate({
            scrollTop: 0
        }, "fast");
    })
</script>
@endif

@if(session()->has('message'))
<script>
    $(function() {
        $(".msg-alert-success-show-text").html("");
        $(".msg-alert-success-show-text").append("เพิ่มข้อมูลสำเร็จ")
        $(".msg-alert-success").show("slow");
        $("html, body").animate({
            scrollTop: 0
        }, "fast");
        setTimeout(() => {
            window.location.href = "{{route('admin.orders.index')}}";
        }, 2000);
    })
</script>
@endif

    @include('admin.order.edit-form-component.edit-script')
@endpush()
