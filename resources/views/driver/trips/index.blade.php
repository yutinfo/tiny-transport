@extends('layouts.driver')
 
@section('content')
<div class="driver-topbar">
    <div>
        <strong>งานของฉัน</strong>
        <div class="small text-muted">{{ auth()->user()->name }}</div>
    </div>
    <form action="{{ route('login.logout') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-secondary">ออก</button>
    </form>
</div>
 
<div class="driver-content">
    @forelse($trips as $trip)
        <a href="{{ route('driver.trips.show', $trip) }}" class="driver-trip-card d-block text-reset mb-2">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>{{ $trip->code }}</strong>
                    <div class="small text-muted">{{ optional($trip->trip_date)->format('Y-m-d') }}</div>
                </div>
                <span class="badge {{ $trip->status_badge_class }}">{{ $trip->status_label }}</span>
            </div>
            <div class="mt-2 small">
                <i class="fas fa-box"></i> {{ number_format($trip->trip_items_count) }} พัสดุ
                @if($trip->area_name)
                    <span class="ml-2"><i class="fas fa-map-marker-alt"></i> {{ $trip->area_name }}</span>
                @endif
            </div>
        </a>
    @empty
        <div class="text-center text-muted py-5">ยังไม่มีรอบขนส่งที่ได้รับมอบหมาย</div>
    @endforelse
 
    {{ $trips->links() }}
</div>
@endsection
