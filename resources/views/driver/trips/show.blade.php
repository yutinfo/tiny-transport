@extends('layouts.driver')
 
@section('content')
<div class="driver-topbar">
    <a href="{{ route('driver.dashboard') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> กลับ
    </a>
    <div>
        <strong>รายละเอียดงาน</strong>
    </div>
    <div style="width: 50px;"></div> {{-- spacer to center title --}}
</div>
 
<div class="driver-content">
    @if(session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger py-2">
            @foreach($errors->all() as $error)
                <div class="small">{{ $error }}</div>
            @endforeach
        </div>
    @endif
 
    @if($readOnly)
        <div class="alert alert-warning py-2 small">รอบขนส่งนี้เสร็จสิ้นหรือยกเลิกแล้ว แสดงผลแบบอ่านอย่างเดียว</div>
    @endif
 
    <div class="driver-trip-card mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="font-weight-bold mb-0">{{ $data->code }}</h5>
            <span class="badge {{ $data->status_badge_class }}">{{ $data->status_label }}</span>
        </div>
        <div class="small">
            <div><strong>วันที่:</strong> {{ optional($data->trip_date)->format('Y-m-d') }}</div>
            <div><strong>ทะเบียนรถ:</strong> {{ $data->car_id ?: '-' }}</div>
            <div><strong>พื้นที่:</strong> {{ $data->area_name ?: '-' }}</div>
        </div>
    </div>
 
    <div class="driver-summary-grid mb-3">
        <div class="driver-summary-card">
            <div class="text-muted small">พัสดุทั้งหมด</div>
            <div class="h5 font-weight-bold mb-0 text-info">{{ number_format($summary['total_parcels']) }}</div>
        </div>
        <div class="driver-summary-card">
            <div class="text-muted small">จัดส่งสำเร็จ</div>
            <div class="h5 font-weight-bold mb-0 text-success">{{ number_format($summary['delivered_count']) }}</div>
        </div>
        <div class="driver-summary-card">
            <div class="text-muted small">ส่งไม่สำเร็จ/คืน</div>
            <div class="h5 font-weight-bold mb-0 text-danger">{{ number_format($summary['failed_count']) }}</div>
        </div>
        <div class="driver-summary-card">
            <div class="text-muted small">คงเหลือ</div>
            <div class="h5 font-weight-bold mb-0 text-warning">{{ number_format($summary['remaining_count']) }}</div>
        </div>
        <div class="driver-summary-card">
            <div class="text-muted small">COD ทั้งหมด</div>
            <div class="h5 font-weight-bold mb-0 text-primary">{{ number_format($summary['total_cod_amount'], 2) }}</div>
        </div>
        <div class="driver-summary-card">
            <div class="text-muted small">COD เก็บแล้ว</div>
            <div class="h5 font-weight-bold mb-0 text-secondary">{{ number_format($summary['collected_amount'], 2) }}</div>
        </div>
    </div>
 
    <h6 class="font-weight-bold mb-2">รายการพัสดุในรอบ</h6>
    @forelse($items as $item)
        @include('driver.trips._parcel-card', ['item' => $item])
    @empty
        <div class="card">
            <div class="card-body text-center text-muted py-4 small">ยังไม่มีพัสดุในรอบนี้</div>
        </div>
    @endforelse
</div>
@endsection
