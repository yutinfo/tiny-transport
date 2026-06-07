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
<div class="container-fluid ta-page-shell">
    <section class="ta-page-header-card">
        <div class="card-body">
            <div class="ta-page-header-row">
                <div>
                    <span class="ta-page-kicker"><i class="fas fa-shipping-fast" aria-hidden="true"></i> Parcel Tracking</span>
                    <h1 class="ta-page-title">ประวัติพัสดุ {{ $parcel->parcel_code }}</h1>
                    <p class="ta-page-subtitle">ออเดอร์ {{ $order->code ?? '-' }} · ติดตามสถานะจัดส่งและบันทึกการแจ้งเตือนลูกค้า</p>
                </div>
                <div class="ta-page-actions">
                    @if($currentTrip)
                        <a href="{{ route('admin.trips.show', $currentTrip) }}" class="btn btn-default"><i class="fas fa-truck"></i> รอบ {{ $currentTrip->code }}</a>
                    @endif
                    <a href="{{ url()->previous() }}" class="btn btn-default"><i class="fas fa-arrow-left"></i> กลับ</a>
                </div>
            </div>
        </div>
    </section>

    <div class="ta-form-layout">
        <div class="ta-form-main">
            <section class="card ta-form-section">
                <div class="card-header">
                    <div>
                        <h3 class="ta-section-title">ไทม์ไลน์สถานะ</h3>
                        <p class="ta-section-subtitle">ลำดับเหตุการณ์การจัดส่งของพัสดุชิ้นนี้</p>
                    </div>
                </div>
                <div class="card-body">
                    @if($logs->count())
                        <div class="ta-timeline">
                            @foreach($logs as $log)
                                @php
                                    $fromLabel = $deliveryStatusLabels[$log->from_status] ?? ($log->from_status ?: '-');
                                    $toLabel = $deliveryStatusLabels[$log->to_status] ?? $log->to_status;
                                @endphp
                                <article class="ta-timeline__item">
                                    <div class="d-flex flex-wrap justify-content-between align-items-start mb-2">
                                        <div>
                                            <strong>{{ $fromLabel }} &rarr; {{ $toLabel }}</strong>
                                            <div class="mt-2"><span class="badge badge-primary">{{ $toLabel }}</span></div>
                                        </div>
                                        <small class="text-muted">{{ optional($log->created_at)->format('Y-m-d H:i') }}</small>
                                    </div>
                                    @if($log->note)
                                        <div>{{ $log->note }}</div>
                                    @endif
                                    <div class="text-muted mt-2">
                                        โดย {{ $log->created_by ?: '-' }}
                                        @if($log->trip)
                                            | รอบ {{ $log->trip->code }}
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="ta-empty-state">
                            <div class="ta-empty-state__icon"><i class="fas fa-history"></i></div>
                            <div>ยังไม่มีประวัติสถานะ</div>
                        </div>
                    @endif
                </div>
            </section>
        </div>

        <div class="ta-form-sidebar">
            <section class="card ta-state-card">
                <div class="card-header">
                    <div>
                        <h3 class="ta-section-title">ข้อมูลพัสดุ</h3>
                        <p class="ta-section-subtitle">ภาพรวมข้อมูลจัดส่งและสถานะล่าสุด</p>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="ta-info-list mb-0">
                        <div class="ta-info-list__item">
                            <dt>รหัสพัสดุ</dt>
                            <dd>{{ $parcel->parcel_code }}</dd>
                        </div>
                        <div class="ta-info-list__item">
                            <dt>รหัสออเดอร์</dt>
                            <dd>{{ $order->code ?? '-' }}</dd>
                        </div>
                        <div class="ta-info-list__item">
                            <dt>ผู้ฝาก</dt>
                            <dd>{{ $order->customer_name ?? '-' }}<br><small class="text-muted">{{ $order->customer_mobile ?? '' }}</small></dd>
                        </div>
                        <div class="ta-info-list__item">
                            <dt>ผู้รับ</dt>
                            <dd>{{ $parcel->receive_name ?? '-' }}<br><small class="text-muted">{{ $parcel->receive_mobile ?? '' }}</small></dd>
                        </div>
                        <div class="ta-info-list__item">
                            <dt>ปลายทาง</dt>
                            <dd>{{ $destinationAddress ?: '-' }}</dd>
                        </div>
                        <div class="ta-info-list__item">
                            <dt>จัดส่ง</dt>
                            <dd><span class="badge badge-info">{{ $currentDeliveryLabel }}</span></dd>
                        </div>
                        <div class="ta-info-list__item">
                            <dt>ชำระเงิน</dt>
                            <dd><span class="badge badge-secondary">{{ $currentPaymentLabel }}</span></dd>
                        </div>
                        <div class="ta-info-list__item">
                            <dt>รอบปัจจุบัน</dt>
                            <dd>{{ $currentTrip->code ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </section>

            <section class="card ta-state-card">
                <div class="card-header">
                    <div>
                        <h3 class="ta-section-title">แจ้งเตือนลูกค้า</h3>
                        <p class="ta-section-subtitle">บันทึกช่องทางและข้อความที่ใช้แจ้งสถานะกับลูกค้า</p>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success py-2">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('admin.parcels.notifications.store', $parcel) }}" method="POST" class="mb-4">
                        @csrf
                        <div class="form-group">
                            <label for="channel" class="small">ช่องทาง</label>
                            <select name="channel" id="channel" class="form-control form-control-sm" required>
                                <option value="sms">SMS</option>
                                <option value="line">LINE</option>
                                <option value="email">Email</option>
                                <option value="manual">Manual Log (บันทึกมือ)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="recipient" class="small">ผู้รับ (เบอร์โทร/ไอดี/อีเมล)</label>
                            <input type="text" name="recipient" id="recipient" value="{{ old('recipient', $parcel->receive_mobile) }}" class="form-control form-control-sm" required>
                        </div>
                        <div class="form-group">
                            <label for="message" class="small">ข้อความ</label>
                            <textarea name="message" id="message" rows="3" class="form-control form-control-sm" required placeholder="พิมพ์ข้อความที่ต้องการแจ้งเตือน..."></textarea>
                        </div>
                        <button type="submit" class="btn bg-primary btn-block"><i class="fas fa-paper-plane"></i> บันทึกและส่ง (Mock)</button>
                    </form>

                    <h6 class="font-weight-bold">ประวัติการแจ้งเตือน</h6>
                    @forelse($notifications as $notify)
                        <article class="ta-timeline__item mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge badge-secondary text-uppercase">{{ $notify->channel }}</span>
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
                            <div class="small mt-2 text-wrap" style="word-break: break-all;"><strong>ถึง:</strong> {{ $notify->recipient }}</div>
                            <div class="small mt-1">{{ $notify->message }}</div>
                            @if($notify->created_by)
                                <small class="text-muted d-block mt-1">โดย {{ $notify->created_by }}</small>
                            @endif
                        </article>
                    @empty
                        <div class="ta-empty-state py-3">
                            <div>ยังไม่มีประวัติการส่งแจ้งเตือน</div>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
