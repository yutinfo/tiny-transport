@extends('layouts.app')

@section('content')
<div class="container-fluid ta-page-shell">
    <section class="ta-page-header-card">
        <div class="card-body">
            <div class="ta-page-header-row">
                <div>
                    <span class="ta-page-kicker"><i class="fas fa-id-card" aria-hidden="true"></i> Drivers</span>
                    <h1 class="ta-page-title">เพิ่มคนขับรถ</h1>
                    <p class="ta-page-subtitle">บันทึกข้อมูลคนขับและเลือกวิธีจัดการบัญชีเข้าสู่ระบบ</p>
                </div>
                <div class="ta-page-actions">
                    <a href="{{ route('admin.drivers.index') }}" class="btn btn-default"><i class="fas fa-arrow-left"></i> กลับ</a>
                </div>
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

    <form action="{{ route('admin.drivers.store') }}" method="POST" class="ta-page-shell">
        @csrf
        <section class="card ta-form-section">
            <div class="card-header">
                <div>
                    <h3 class="ta-section-title">ข้อมูลคนขับ</h3>
                    <p class="ta-section-subtitle">รหัสคนขับจะถูกสร้างให้อัตโนมัติเมื่อบันทึก</p>
                </div>
            </div>
            <div class="card-body">
                @include('admin.drivers.form')
            </div>
        </section>

        <div class="ta-sticky-savebar">
            <div class="ta-sticky-savebar__inner">
                <div class="ta-sticky-savebar__text">
                    <strong>พร้อมเพิ่มคนขับ</strong>
                    <span>ตรวจสอบเบอร์โทรและบัญชีเข้าสู่ระบบก่อนบันทึก</span>
                </div>
                <div class="ta-page-actions">
                    <a href="{{ route('admin.drivers.index') }}" class="btn btn-default">ยกเลิก</a>
                    <button type="submit" class="btn bg-success"><i class="fas fa-save"></i> บันทึก</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
