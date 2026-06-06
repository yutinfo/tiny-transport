@extends('layouts.app')

@section('content')
@php
    $destinationAddress = collect([
        $parcel->receive_address,
        $parcel->district_name,
        $parcel->amphures_name,
        $parcel->province_name,
        $parcel->zip_code,
    ])->filter()->implode(' ');
    $currentDeliveryStatus = $currentTripItem->delivery_status ?? $parcel->delivery_status;
    $currentPaymentStatus = $currentTripItem->payment_status ?? $parcel->payment_status;
    $currentDeliveryLabel = $deliveryStatusLabels[$currentDeliveryStatus] ?? ($currentDeliveryStatus ?: '-');
    $currentPaymentLabel = $paymentStatusLabels[$currentPaymentStatus] ?? ($currentPaymentStatus ?: '-');
@endphp
<div class="container-fluid">
    <section class="content-header">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="font-weight-bold mb-0">ประวัติพัสดุ {{ $parcel->parcel_code }}</h5>
                        <small class="text-muted">ออเดอร์ {{ $order->code ?? '-' }}</small>
                    </div>
                    <div class="col-md-6 text-right">
                        @if($currentTrip)
                            <a href="{{ route('admin.trips.show', $currentTrip) }}" class="btn bg-primary"><i class="fas fa-truck"></i> รอบ {{ $currentTrip->code }}</a>
                        @endif
                        <a href="{{ url()->previous() }}" class="btn bg-secondary"><i class="fas fa-arrow-left"></i> กลับ</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">ข้อมูลพัสดุ</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">รหัสพัสดุ</dt>
                            <dd class="col-sm-7">{{ $parcel->parcel_code }}</dd>
                            <dt class="col-sm-5">รหัสออเดอร์</dt>
                            <dd class="col-sm-7">{{ $order->code ?? '-' }}</dd>
                            <dt class="col-sm-5">ผู้ฝาก</dt>
                            <dd class="col-sm-7">{{ $order->customer_name ?? '-' }}<br><small class="text-muted">{{ $order->customer_mobile ?? '' }}</small></dd>
                            <dt class="col-sm-5">ผู้รับ</dt>
                            <dd class="col-sm-7">{{ $parcel->receive_name ?? '-' }}<br><small class="text-muted">{{ $parcel->receive_mobile ?? '' }}</small></dd>
                            <dt class="col-sm-5">ปลายทาง</dt>
                            <dd class="col-sm-7">{{ $destinationAddress ?: '-' }}</dd>
                            <dt class="col-sm-5">จัดส่ง</dt>
                            <dd class="col-sm-7"><span class="badge badge-info">{{ $currentDeliveryLabel }}</span></dd>
                            <dt class="col-sm-5">ชำระเงิน</dt>
                            <dd class="col-sm-7"><span class="badge badge-secondary">{{ $currentPaymentLabel }}</span></dd>
                            <dt class="col-sm-5">รอบปัจจุบัน</dt>
                            <dd class="col-sm-7">{{ $currentTrip->code ?? '-' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">ไทม์ไลน์สถานะ</h3>
                    </div>
                    <div class="card-body">
                        @if($logs->count())
                            <div class="timeline mb-0">
                                @foreach($logs as $log)
                                    @php
                                        $fromLabel = $deliveryStatusLabels[$log->from_status] ?? ($log->from_status ?: '-');
                                        $toLabel = $deliveryStatusLabels[$log->to_status] ?? $log->to_status;
                                    @endphp
                                    <div class="time-label">
                                        <span class="bg-info">{{ optional($log->created_at)->format('Y-m-d H:i') }}</span>
                                    </div>
                                    <div>
                                        <i class="fas fa-shipping-fast bg-blue"></i>
                                        <div class="timeline-item">
                                            <span class="time"><i class="fas fa-clock"></i> {{ optional($log->created_at)->format('H:i') }}</span>
                                            <h3 class="timeline-header">
                                                {{ $fromLabel }} &rarr; <strong>{{ $toLabel }}</strong>
                                            </h3>
                                            <div class="timeline-body">
                                                <div><span class="badge badge-info">{{ $toLabel }}</span></div>
                                                @if($log->note)
                                                    <div class="mt-2">{{ $log->note }}</div>
                                                @endif
                                                <div class="text-muted mt-2">
                                                    โดย {{ $log->created_by ?: '-' }}
                                                    @if($log->trip)
                                                        | รอบ {{ $log->trip->code }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                <div>
                                    <i class="fas fa-check bg-gray"></i>
                                </div>
                            </div>
                        @else
                            <div class="text-center text-muted py-4">ยังไม่มีประวัติสถานะ</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
