@extends('layouts.app')

@section('content')
<div class="container-fluid ta-page-shell">
    <section class="ta-page-header-card">
        <div class="card-body">
            <div class="ta-page-header-row">
                <div>
                    <span class="ta-page-kicker"><i class="fas fa-route" aria-hidden="true"></i> Trips</span>
                    <h1 class="ta-page-title">สร้างรอบขนส่ง</h1>
                    <p class="ta-page-subtitle">กำหนดข้อมูลคนขับรถ วันที่จัดส่ง และพื้นที่ให้พร้อมก่อนมอบหมายพัสดุเข้ารอบ</p>
                </div>
                <div class="ta-page-actions">
                    <a href="{{ route('admin.trips.index') }}" class="btn btn-default"><i class="fas fa-arrow-left"></i> กลับ</a>
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

    <form action="{{ route('admin.trips.store') }}" method="POST" class="ta-page-shell">
        @csrf
        <section class="card ta-form-section">
            <div class="card-header">
                <div>
                    <h3 class="ta-section-title">ข้อมูลรอบขนส่ง</h3>
                    <p class="ta-section-subtitle">บันทึกข้อมูลหลักที่ใช้ในมุมมองผู้ดูแลและพนักงานขับรถ</p>
                </div>
            </div>
            <div class="card-body">
                @include('admin.trip.form')
            </div>
        </section>

        <div class="ta-sticky-savebar">
            <div class="ta-sticky-savebar__inner">
                <div class="ta-sticky-savebar__text">
                    <strong>พร้อมสร้างรอบขนส่ง</strong>
                    <span>ตรวจสอบวันที่ คนขับรถ และทะเบียนรถก่อนบันทึก</span>
                </div>
                <div class="ta-page-actions">
                    <a href="{{ route('admin.trips.index') }}" class="btn btn-default">ยกเลิก</a>
                    <button type="submit" class="btn bg-success"><i class="fas fa-save"></i> บันทึก</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
