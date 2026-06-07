@extends('layouts.app')

@section('content')
<div class="container-fluid ta-page-shell">
    <section class="card ta-state-card">
        <div class="card-body">
            <div class="ta-empty-state">
                <div class="ta-empty-state__icon"><i class="fas fa-tools"></i></div>
                <h1 class="ta-page-title">500</h1>
                <p class="ta-page-subtitle">ระบบเกิดข้อผิดพลาดภายใน กรุณาลองใหม่อีกครั้ง</p>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-default mt-3"><i class="fas fa-arrow-left"></i> กลับหน้าหลัก</a>
            </div>
        </div>
    </section>
</div>
@endsection
