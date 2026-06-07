@extends('layouts.app')

@section('content')
<div class="container-fluid ta-page-shell">
    <section class="card ta-state-card">
        <div class="card-body">
            <div class="ta-empty-state">
                <div class="ta-empty-state__icon"><i class="fas fa-lock"></i></div>
                <h1 class="ta-page-title">403</h1>
                <p class="ta-page-subtitle">คุณไม่มีสิทธิ์เข้าถึงหน้านี้</p>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-default mt-3"><i class="fas fa-arrow-left"></i> กลับหน้าหลัก</a>
            </div>
        </div>
    </section>
</div>
@endsection
