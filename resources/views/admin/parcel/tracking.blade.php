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

                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">แจ้งเตือนลูกค้า (Stub)</h3>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success py-2">{{ session('success') }}</div>
                        @endif

                        <form action="{{ route('admin.parcels.notifications.store', $parcel) }}" method="POST" class="mb-4">
                            @csrf
                            <div class="form-group mb-2">
                                <label for="channel" class="small">ช่องทาง</label>
                                <select name="channel" id="channel" class="form-control form-control-sm" required>
                                    <option value="sms">SMS</option>
                                    <option value="line">LINE</option>
                                    <option value="email">Email</option>
                                    <option value="manual">Manual Log (บันทึกมือ)</option>
                                </select>
                            </div>
                            <div class="form-group mb-2">
                                <label for="recipient" class="small">ผู้รับ (เบอร์โทร/ไอดี/อีเมล)</label>
                                <input type="text" name="recipient" id="recipient" value="{{ old('recipient', $parcel->receive_mobile) }}" class="form-control form-control-sm" required>
                            </div>
                            <div class="form-group mb-2">
                                <label for="message" class="small">ข้อความ</label>
                                <textarea name="message" id="message" rows="2" class="form-control form-control-sm" required placeholder="พิมพ์ข้อความที่ต้องการแจ้งเตือน..."></textarea>
                            </div>
                            <button type="submit" class="btn bg-primary btn-sm btn-block"><i class="fas fa-paper-plane"></i> บันทึกและส่ง (Mock)</button>
                        </form>

                        <h6 class="font-weight-bold">ประวัติการแจ้งเตือน</h6>
                        @forelse($notifications as $notify)
                            <div class="border-bottom py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="badge badge-dark text-uppercase">{{ $notify->channel }}</span>
                                        @if($notify->status === 'sent')
                                            <span class="badge badge-success">ส่งแล้ว</span>
                                        @elseif($notify->status === 'failed')
                                            <span class="badge badge-danger">ล้มเหลว</span>
                                        @elseif($notify->status === 'pending')
                                            <span class="badge badge-warning">กำลังส่ง</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $notify->status }}</span>
                                        @endif
                                    </div>
                                    <small class="text-muted">{{ optional($notify->created_at)->format('Y-m-d H:i') }}</small>
                                </div>
                                <div class="small mt-1 text-wrap" style="word-break: break-all;"><strong>ถึง:</strong> {{ $notify->recipient }}</div>
                                <div class="small mt-1">{{ $notify->message }}</div>
                                @if($notify->created_by)
                                    <small class="text-muted d-block mt-1">โดย {{ $notify->created_by }}</small>
                                @endif
                            </div>
                        @empty
                            <div class="text-center text-muted small py-3">ยังไม่มีประวัติการส่งแจ้งเตือน</div>
                        @endforelse
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
