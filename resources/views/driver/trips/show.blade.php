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

    @if($data->status === \App\Models\Trip::STATUS_ASSIGNED)
        <div class="mb-3">
            <form action="{{ route('driver.trips.start', $data) }}" method="POST" onsubmit="return confirm('ยืนยันการเริ่มจัดส่งรอบขนส่งนี้?')">
                @csrf
                <button type="submit" class="btn btn-primary btn-block py-2 font-weight-bold shadow-sm">
                    <i class="fas fa-play mr-1"></i> เริ่มนำส่ง (Start Delivery)
                </button>
            </form>
        </div>
    @endif

    @if($data->status === \App\Models\Trip::STATUS_IN_TRANSIT && $summary['remaining_count'] === 0)
        <div class="mb-3">
            <form action="{{ route('driver.trips.submit', $data) }}" method="POST" onsubmit="return confirm('ยืนยันการส่งยอดและปิดรอบจัดส่ง?')">
                @csrf
                <button type="submit" class="btn btn-success btn-block py-2 font-weight-bold shadow-sm">
                    <i class="fas fa-check-circle mr-1"></i> ส่งยอดและปิดรอบ (Submit Route)
                </button>
            </form>
        </div>
    @endif

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

    @php
        $pendingItems = $items->filter(function($item) {
            return in_array($item->delivery_status, [
                \App\Models\TripItem::DELIVERY_STATUS_WAITING,
                \App\Models\TripItem::DELIVERY_STATUS_PICKED_UP,
                \App\Models\TripItem::DELIVERY_STATUS_IN_TRANSIT
            ]);
        });

        $completedItems = $items->filter(function($item) {
            return in_array($item->delivery_status, [
                \App\Models\TripItem::DELIVERY_STATUS_DELIVERED,
                \App\Models\TripItem::DELIVERY_STATUS_FAILED,
                \App\Models\TripItem::DELIVERY_STATUS_RETURNED
            ]);
        });
    @endphp

    <div class="mb-4">
        @if($items->isEmpty())
            <div class="card border-0 bg-light">
                <div class="card-body text-center text-muted py-4 small">
                    <i class="fas fa-box-open fa-2x mb-2 text-light"></i>
                    <div>ยังไม่มีพัสดุในรอบนี้</div>
                </div>
            </div>
        @else
            {{-- Active / Pending Section --}}
            <h6 class="font-weight-bold mb-3 text-primary">
                <i class="fas fa-hourglass-half mr-1"></i>
                รายการที่ต้องจัดส่ง ({{ $pendingItems->count() }})
            </h6>

            @forelse($pendingItems as $item)
                @include('driver.trips._parcel-card', ['item' => $item, 'isActive' => true])
            @empty
                <div class="card border-0 bg-light mb-3">
                    <div class="card-body text-center text-muted py-3 small">
                        <i class="fas fa-check-double text-success fa-lg mb-1"></i>
                        <div>จัดส่งเสร็จสิ้นครบถ้วนแล้ว!</div>
                    </div>
                </div>
            @endforelse

            {{-- Completed Section --}}
            @if($completedItems->isNotEmpty())
                <div class="completed-section-header d-flex justify-content-between align-items-center mt-4 mb-3" data-toggle="collapse" data-target="#completed-parcels-list" aria-expanded="false" aria-controls="completed-parcels-list">
                    <div class="font-weight-bold text-muted">
                        <i class="fas fa-check-circle text-success mr-1"></i>
                        ดำเนินการแล้ว ({{ $completedItems->count() }})
                    </div>
                    <span class="toggle-icon text-muted">
                        <i class="fas fa-chevron-down"></i>
                    </span>
                </div>

                <div class="collapse" id="completed-parcels-list">
                    @foreach($completedItems as $item)
                        @include('driver.trips._parcel-card', ['item' => $item, 'isActive' => false])
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
