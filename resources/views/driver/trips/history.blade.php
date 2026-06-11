@extends('layouts.driver')

@section('content')
<div class="driver-appbar">
    <strong><i class="fas fa-history mr-2"></i>ประวัติงาน</strong>
</div>

<div class="driver-content">
    <h6 class="font-weight-bold mb-3 text-muted">
        <i class="fas fa-clipboard-check mr-1 text-primary"></i>
        รอบที่ดำเนินการแล้ว ({{ $trips->total() }})
    </h6>

    @forelse($trips as $trip)
        <a href="{{ route('driver.trips.show', $trip) }}" class="driver-trip-card driver-trip-card--history d-flex align-items-center justify-content-between text-reset mb-3 status-{{ $trip->status }}">
            <div class="flex-grow-1">
                <div class="d-flex align-items-center mb-2">
                    <span class="trip-code font-weight-bold text-primary mr-2">{{ $trip->code }}</span>
                    <span class="badge {{ $trip->status_badge_class }}">{{ $trip->status_label }}</span>
                </div>
                <div class="trip-meta d-flex flex-wrap text-muted small">
                    <span class="mr-3"><i class="far fa-calendar-alt mr-1"></i> {{ optional($trip->trip_date)->format('Y-m-d') }}</span>
                    <span class="mr-3"><i class="fas fa-box mr-1"></i> {{ number_format($trip->trip_items_count) }} พัสดุ</span>
                    @if($trip->area_name)
                        <span><i class="fas fa-map-marker-alt mr-1"></i> {{ $trip->area_name }}</span>
                    @endif
                </div>
            </div>
            <div class="trip-arrow text-muted pl-2">
                <i class="fas fa-chevron-right"></i>
            </div>
        </a>
    @empty
        <div class="text-center text-muted py-5">
            <i class="fas fa-clock fa-3x mb-3 text-light"></i>
            <div>ยังไม่มีประวัติงาน</div>
        </div>
    @endforelse

    <div class="mt-4">
        {{ $trips->links() }}
    </div>
</div>
@endsection
