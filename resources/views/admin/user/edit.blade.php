@extends('layouts.app')

@section('third_party_stylesheets')
<link rel="stylesheet" href="/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
@endsection

@section('content')
<div class="container-fluid ta-page-shell">
    <form action="{{ route('admin.users.update', $data->id) }}" method="post" class="ta-page-shell">
        @method('PUT')
        <section class="ta-page-header-card">
            <div class="card-body">
                <div class="ta-page-header-row">
                    <div>
                        <span class="ta-page-kicker"><i class="fas fa-users-cog" aria-hidden="true"></i> Users</span>
                        <h1 class="ta-page-title">แก้ไขข้อมูลผู้ใช้</h1>
                        <p class="ta-page-subtitle">ปรับข้อมูลบัญชี {{ $data->username }} และอัปเดตบทบาทของผู้ใช้งาน</p>
                    </div>
                    <div class="ta-page-actions">
                        <a class="btn btn-default" href="{{ route('admin.users.index') }}">
                            <i class="fas fa-arrow-left"></i> กลับ
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
                @include('admin.user.form-component.info')
            </div>
            <div class="ta-form-sidebar">
                @include('admin.user.form-component.auth')
            </div>
        </div>

        <div class="ta-sticky-savebar">
            <div class="ta-sticky-savebar__inner">
                <div class="ta-sticky-savebar__text">
                    <strong>พร้อมบันทึกการเปลี่ยนแปลง</strong>
                    <span>ตรวจสอบสิทธิ์และสถานะก่อนอัปเดตบัญชีผู้ใช้</span>
                </div>
                <div class="ta-page-actions">
                    <a class="btn btn-default" href="{{ route('admin.users.index') }}">ยกเลิก</a>
                    <button type="submit" class="btn bg-success">
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
@if(count($errors)>=1)
<script>
    $(function() {
        $(".msg-alert-danger-show-text").html("");
        let error = JSON.parse(`{!!$errors!!}`);

        for (const [key, value] of Object.entries(error)) {
            $(".msg-alert-danger-show-text").append(value + "<br />")
        }

        $(".msg-alert-danger").show("slow");
        $("html, body").animate({
            scrollTop: 0
        }, "fast");
    })
</script>
@endif

@if(session()->has('success'))
<script>
    $(function() {
        $(".msg-alert-success-show-text").html("");
        $(".msg-alert-success-show-text").append("เพิ่มข้อมูลสำเร็จ")
        $(".msg-alert-success").show("slow");
        $("html, body").animate({
            scrollTop: 0
        }, "fast");
        setTimeout(() => {
            window.location.href = "{{route('admin.users.index')}}";
        }, 2000);
    })
</script>
@endif
@endpush()
