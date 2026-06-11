@extends('layouts.app')

@section('content')
<div class="container-fluid ta-page-shell">
    <section class="ta-page-header-card">
        <div class="card-body">
            <div class="ta-page-header-row">
                <div>
                    <span class="ta-page-kicker"><i class="fas fa-id-card" aria-hidden="true"></i> Drivers</span>
                    <h1 class="ta-page-title">แก้ไขคนขับ {{ $data->code }}</h1>
                    <p class="ta-page-subtitle">อัปเดตข้อมูลคนขับและจัดการบัญชีเข้าสู่ระบบ</p>
                </div>
                <div class="ta-page-actions">
                    <a href="{{ route('admin.drivers.show', $data) }}" class="btn btn-default"><i class="fas fa-arrow-left"></i> กลับ</a>
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

    <form action="{{ route('admin.drivers.update', $data) }}" method="POST" class="ta-page-shell">
        @csrf
        @method('PUT')
        <section class="card ta-form-section">
            <div class="card-header">
                <div>
                    <h3 class="ta-section-title">ข้อมูลคนขับ</h3>
                    <p class="ta-section-subtitle">รหัสคนขับ {{ $data->code }} แก้ไขไม่ได้</p>
                </div>
            </div>
            <div class="card-body">
                @include('admin.drivers.form')
            </div>
        </section>

        <div class="ta-sticky-savebar">
            <div class="ta-sticky-savebar__inner">
                <div class="ta-sticky-savebar__text">
                    <strong>พร้อมอัปเดตข้อมูลคนขับ</strong>
                    <span>บันทึกแล้วสถานะบัญชี login จะถูกซิงก์ตามคนขับ</span>
                </div>
                <div class="ta-page-actions">
                    <a href="{{ route('admin.drivers.show', $data) }}" class="btn btn-default">ยกเลิก</a>
                    <button type="submit" class="btn bg-success"><i class="fas fa-save"></i> บันทึก</button>
                </div>
            </div>
        </div>
    </form>

    @if($data->user)
        <section class="card ta-form-section">
            <div class="card-header">
                <div>
                    <h3 class="ta-section-title">รีเซ็ตรหัสผ่าน</h3>
                    <p class="ta-section-subtitle">ตั้งรหัสผ่านใหม่ให้บัญชี {{ $data->user->username }}</p>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.drivers.reset-password', $data) }}" method="POST"
                      onsubmit="return confirm('ยืนยันการรีเซ็ตรหัสผ่านของบัญชีนี้?')">
                    @csrf
                    <div class="ta-form-grid">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="reset_password">รหัสผ่านใหม่</label>
                                <input type="password" name="password" id="reset_password" class="form-control" autocomplete="new-password">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="reset_password_confirmation">ยืนยันรหัสผ่านใหม่</label>
                                <input type="password" name="password_confirmation" id="reset_password_confirmation" class="form-control" autocomplete="new-password">
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn bg-warning"><i class="fas fa-key"></i> รีเซ็ตรหัสผ่าน</button>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    @endif
</div>
@endsection
