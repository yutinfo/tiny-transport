@extends('layouts.app')
 
@section('content')
<div class="container-fluid">
    <section class="content-header">
        <div class="card">
            <div class="card-header">
                <h5 class="font-weight-bold mb-0">งานขนส่งของฉัน</h5>
            </div>
        </div>
    </section>
    <section class="content">
        @forelse($trips as $trip)
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>{{ $trip->code }}</strong>
                            <div class="text-muted small">{{ optional($trip->trip_date)->format('Y-m-d') }}</div>
                        </div>
                        <a href="{{ route('driver.trips.show', $trip) }}" class="btn btn-sm bg-primary">เปิด</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center text-muted">ยังไม่มีรอบขนส่งที่ได้รับมอบหมาย</div>
            </div>
        @endforelse
    </section>
</div>
@endsection
