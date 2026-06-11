@extends('layouts.driver')

@section('content')
@php
    $thMonths = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    $now = now();
    $hour = (int) $now->format('G');
    $greeting = $hour < 12 ? 'สวัสดีตอนเช้า' : ($hour < 17 ? 'สวัสดีตอนบ่าย' : ($hour < 20 ? 'สวัสดีตอนเย็น' : 'สวัสดีตอนค่ำ'));
    $dateLabel = $now->day . ' ' . $thMonths[(int) $now->format('n')] . ' ' . ((int) $now->format('Y') + 543);
@endphp
<div class="driver-hero-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <div class="driver-welcome">{{ $greeting }} · {{ $dateLabel }}</div>
            <div class="driver-name font-weight-bold">{{ auth()->user()->name }}</div>
        </div>
        <div class="driver-avatar">
            <i class="fas fa-user-circle fa-2x text-white-50"></i>
        </div>
    </div>

    <div class="driver-kpi mt-3">
        <div class="driver-kpi-item">
            <div class="driver-kpi-value">{{ number_format($summary['active_trips']) }}</div>
            <div class="driver-kpi-label">รอบที่ต้องส่ง</div>
        </div>
        <div class="driver-kpi-sep"></div>
        <div class="driver-kpi-item">
            <div class="driver-kpi-value">{{ number_format($summary['total_parcels']) }}</div>
            <div class="driver-kpi-label">พัสดุ</div>
        </div>
        <div class="driver-kpi-sep"></div>
        <div class="driver-kpi-item">
            <div class="driver-kpi-value">฿{{ number_format($summary['cod_to_collect']) }}</div>
            <div class="driver-kpi-label">COD ต้องเก็บ</div>
        </div>
    </div>
</div>

<div class="driver-content">
    <div class="driver-section-head d-flex align-items-center justify-content-between mb-3">
        <span class="driver-section-title">รอบขนส่งของคุณ</span>
        <span class="driver-count-chip">{{ $trips->total() }} รอบ</span>
    </div>

    @forelse($trips as $trip)
        @php
            $total = (int) $trip->trip_items_count;
            $done = (int) ($trip->delivered_count ?? 0);
            $pct = $total > 0 ? round($done * 100 / $total) : 0;
        @endphp
        <a href="{{ route('driver.trips.show', $trip) }}" class="driver-trip-card driver-trip-card--rich text-reset mb-3 status-{{ $trip->status }}">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="trip-code font-weight-bold text-primary">{{ $trip->code }}</span>
                <span class="badge {{ $trip->status_badge_class }}">{{ $trip->status_label }}</span>
            </div>
            <div class="trip-meta d-flex flex-wrap small mb-3">
                <span class="mr-3"><i class="far fa-calendar-alt mr-1"></i> {{ optional($trip->trip_date)->format('Y-m-d') }}</span>
                <span class="mr-3"><i class="fas fa-box mr-1"></i> {{ number_format($total) }} พัสดุ</span>
                @if($trip->area_name)
                    <span><i class="fas fa-map-marker-alt mr-1"></i> {{ $trip->area_name }}</span>
                @endif
            </div>
            <div class="driver-progress-label d-flex align-items-center justify-content-between">
                <span><i class="fas fa-truck-loading mr-1"></i> จัดส่งแล้ว</span>
                <span class="font-weight-bold">{{ $done }} / {{ $total }}</span>
            </div>
            <div class="driver-progress">
                <div class="driver-progress-bar" style="width: {{ $pct }}%"></div>
            </div>
        </a>
    @empty
        <div class="text-center text-muted py-5">
            <i class="fas fa-clipboard-list fa-3x mb-3 text-light"></i>
            <div>ยังไม่มีรอบขนส่งที่ได้รับมอบหมาย</div>
        </div>
    @endforelse

    <div class="mt-4">
        {{ $trips->links() }}
    </div>
</div>
@endsection
