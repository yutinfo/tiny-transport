@extends('layouts.driver')

@section('content')
<div class="driver-hero-header driver-profile-hero">
    <div class="brand-logo mb-3">
        <i class="fas fa-user-circle mr-2"></i>
        <span class="font-weight-bold">โปรไฟล์</span>
    </div>
    <div class="driver-profile d-flex align-items-center">
        <div class="driver-avatar mr-3">
            <i class="fas fa-user-circle fa-3x text-white-50"></i>
        </div>
        <div>
            <div class="driver-name font-weight-bold">{{ $user->name }}</div>
            <div class="driver-welcome">พนักงานขับรถ</div>
        </div>
    </div>
</div>

<div class="driver-content">
    <div class="driver-summary-grid mb-4">
        <div class="driver-summary-card">
            <div class="text-muted small">งานที่กำลังทำ</div>
            <div class="h5 font-weight-bold mb-0 text-primary">{{ number_format($activeCount) }}</div>
        </div>
        <div class="driver-summary-card">
            <div class="text-muted small">งานที่เสร็จแล้ว</div>
            <div class="h5 font-weight-bold mb-0 text-success">{{ number_format($historyCount) }}</div>
        </div>
    </div>

    <div class="driver-profile-list mb-4">
        <a href="{{ route('driver.dashboard') }}" class="driver-profile-item text-reset">
            <span><i class="fas fa-truck-moving text-primary mr-2"></i> งานของฉัน</span>
            <i class="fas fa-chevron-right text-muted"></i>
        </a>
        <a href="{{ route('driver.trips.history') }}" class="driver-profile-item text-reset">
            <span><i class="fas fa-history text-primary mr-2"></i> ประวัติงาน</span>
            <i class="fas fa-chevron-right text-muted"></i>
        </a>
    </div>

    <form action="{{ route('login.logout') }}" method="POST" onsubmit="return confirm('ยืนยันออกจากระบบ?')">
        @csrf
        <button type="submit" class="btn btn-block driver-logout-btn">
            <i class="fas fa-sign-out-alt mr-2"></i> ออกจากระบบ
        </button>
    </form>

    <div class="text-center text-muted small mt-4">
        {{ config('app.name') }}
    </div>
</div>
@endsection
