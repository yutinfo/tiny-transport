@extends('layouts.app')

@section('content')
<div class="container-fluid ta-page-shell">
    <section class="ta-page-header-card">
        <div class="card-body">
            <div class="ta-page-header-row">
                <div>
                    <span class="ta-page-kicker"><i class="fas fa-route" aria-hidden="true"></i> Trips</span>
                    <h1 class="ta-page-title">แก้ไขรอบขนส่ง {{ $data->code }}</h1>
                    <p class="ta-page-subtitle">อัปเดตข้อมูลคนขับรถ ทะเบียนรถ และพื้นที่ของรอบขนส่งนี้</p>
                </div>
                <div class="ta-page-actions">
                    <a href="{{ route('admin.trips.show', $data) }}" class="btn btn-default"><i class="fas fa-arrow-left"></i> กลับ</a>
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

    <form action="{{ route('admin.trips.update', $data) }}" method="POST" class="ta-page-shell">
        @csrf
        @method('PUT')
        <section class="card ta-form-section">
            <div class="card-header">
                <div>
                    <h3 class="ta-section-title">ข้อมูลรอบขนส่ง</h3>
                    <p class="ta-section-subtitle">แก้ไขข้อมูลหลักของรอบขนส่งโดยไม่เปลี่ยน workflow เดิมของระบบ</p>
                </div>
            </div>
            <div class="card-body">
                @include('admin.trip.form')
            </div>
        </section>

        <div class="ta-sticky-savebar">
            <div class="ta-sticky-savebar__inner">
                <div class="ta-sticky-savebar__text">
                    <strong>พร้อมอัปเดตรอบขนส่ง</strong>
                    <span>บันทึกแล้วหน้ารายละเอียดรอบจะอัปเดตตามข้อมูลล่าสุด</span>
                </div>
                <div class="ta-page-actions">
                    <a href="{{ route('admin.trips.show', $data) }}" class="btn btn-default">ยกเลิก</a>
                    <button type="submit" class="btn bg-success"><i class="fas fa-save"></i> บันทึก</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
