@extends('layouts.app')

@push('page_css')
<style>
    .driver-trip-page .info-box {
        min-height: 76px;
    }
    .driver-trip-page .parcel-card {
        border-left: 4px solid #17a2b8;
    }
    .driver-trip-page .parcel-actions .btn,
    .driver-trip-page .parcel-actions .form-control {
        margin-bottom: 8px;
    }
    @media (max-width: 575.98px) {
        .driver-trip-page .content-header .btn {
            width: 100%;
            margin-top: 8px;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid driver-trip-page">
    <section class="content-header">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-7">
                        <h5 class="font-weight-bold mb-0">Driver View {{ $data->code }}</h5>
                        <small class="text-muted">{{ optional($data->trip_date)->format('Y-m-d') }} | {{ $data->status_label }}</small>
                    </div>
                    <div class="col-md-5 text-right">
                        <a href="{{ route('admin.trips.show', $data) }}" class="btn bg-secondary"><i class="fas fa-arrow-left"></i> กลับรายละเอียด</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @if($readOnly)
            <div class="alert alert-warning">รอบขนส่งนี้เสร็จสิ้นหรือยกเลิกแล้ว แสดงผลแบบอ่านอย่างเดียว</div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-6"><strong>พนักงานขับรถ:</strong><br>{{ $data->driver_name ?: '-' }}</div>
                    <div class="col-md-3 col-6"><strong>เบอร์โทร:</strong><br>{{ $data->driver_mobile ?: '-' }}</div>
                    <div class="col-md-3 col-6"><strong>ทะเบียนรถ:</strong><br>{{ $data->car_id ?: '-' }}</div>
                    <div class="col-md-3 col-6"><strong>พื้นที่:</strong><br>{{ $data->area_name ?: '-' }}</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-2 col-md-4 col-6">
                <div class="info-box"><span class="info-box-icon bg-info"><i class="fas fa-box"></i></span><div class="info-box-content"><span class="info-box-text">พัสดุ</span><span class="info-box-number">{{ number_format($summary['total_parcels']) }}</span></div></div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="info-box"><span class="info-box-icon bg-success"><i class="fas fa-check"></i></span><div class="info-box-content"><span class="info-box-text">ส่งสำเร็จ</span><span class="info-box-number">{{ number_format($summary['delivered_count']) }}</span></div></div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="info-box"><span class="info-box-icon bg-danger"><i class="fas fa-times"></i></span><div class="info-box-content"><span class="info-box-text">ส่งไม่สำเร็จ</span><span class="info-box-number">{{ number_format($summary['failed_count']) }}</span></div></div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="info-box"><span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span><div class="info-box-content"><span class="info-box-text">คงเหลือ</span><span class="info-box-number">{{ number_format($summary['remaining_count']) }}</span></div></div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="info-box"><span class="info-box-icon bg-primary"><i class="fas fa-money-bill"></i></span><div class="info-box-content"><span class="info-box-text">COD รวม</span><span class="info-box-number">{{ number_format($summary['total_cod_amount'], 2) }}</span></div></div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="info-box"><span class="info-box-icon bg-secondary"><i class="fas fa-cash-register"></i></span><div class="info-box-content"><span class="info-box-text">เก็บแล้ว</span><span class="info-box-number">{{ number_format($summary['collected_amount'], 2) }}</span></div></div>
            </div>
        </div>

        <div class="row">
            @forelse($items as $item)
                @php
                    $receiver = $item->orderReceive;
                    $address = collect([
                        $receiver->receive_address ?? null,
                        $receiver->district_name ?? null,
                        $receiver->amphures_name ?? null,
                        $receiver->province_name ?? null,
                        $receiver->zip_code ?? null,
                    ])->filter()->implode(' ');
                    $mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($address);
                    $isDelivered = $item->delivery_status === \App\Models\TripItem::DELIVERY_STATUS_DELIVERED;
                    $isPaid = $item->payment_status === \App\Models\TripItem::PAYMENT_STATUS_PAID;
                @endphp
                <div class="col-xl-4 col-lg-6 col-md-6">
                    <div class="card parcel-card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h3 class="card-title font-weight-bold">{{ $item->parcel_code }}</h3>
                                    <small class="d-block text-muted">{{ $item->order->code ?? '-' }}</small>
                                </div>
                                <div class="text-right">
                                    <span class="badge badge-info">{{ $item->delivery_status_label }}</span>
                                    <span class="badge badge-secondary">{{ $item->payment_status_label }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="font-weight-bold mb-1">{{ $receiver->receive_name ?? '-' }}</h6>
                            <div class="mb-2">
                                @if($receiver && $receiver->receive_mobile)
                                    <a href="tel:{{ $receiver->receive_mobile }}">{{ $receiver->receive_mobile }}</a>
                                @else
                                    <span class="text-muted">ไม่มีเบอร์โทร</span>
                                @endif
                            </div>
                            <p class="mb-2">{{ $address ?: '-' }}</p>
                            <div class="mb-3">
                                <strong>COD:</strong> {{ number_format($item->cod_amount, 2) }}
                                <span class="text-muted">| เก็บแล้ว {{ number_format($item->collected_amount, 2) }}</span>
                            </div>

                            <div class="parcel-actions">
                                <a href="tel:{{ $receiver->receive_mobile ?? '' }}" class="btn bg-info btn-sm {{ empty($receiver->receive_mobile) ? 'disabled' : '' }}"><i class="fas fa-phone"></i> โทร</a>
                                <a href="{{ $mapsUrl }}" target="_blank" rel="noopener" class="btn bg-primary btn-sm"><i class="fas fa-map-marker-alt"></i> เปิดแผนที่</a>
                                <a href="{{ route('admin.parcels.tracking', $receiver) }}" class="btn bg-secondary btn-sm"><i class="fas fa-history"></i> ดูประวัติ</a>

                                @if($readOnly)
                                    <div class="text-muted mt-2">อ่านอย่างเดียว</div>
                                @else
                                    <form action="{{ route('admin.driver.trip-items.delivery-status', $item) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="delivery_status" value="{{ \App\Models\TripItem::DELIVERY_STATUS_DELIVERED }}">
                                        <input type="hidden" name="note" value="ส่งสำเร็จ">
                                        <button type="submit" class="btn bg-success btn-sm"><i class="fas fa-check"></i> ส่งสำเร็จ</button>
                                    </form>

                                    <form action="{{ route('admin.driver.trip-items.delivery-status', $item) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="delivery_status" value="{{ \App\Models\TripItem::DELIVERY_STATUS_RETURNED }}">
                                        <input type="hidden" name="note" value="ตีกลับ">
                                        <button type="submit" class="btn bg-warning btn-sm"><i class="fas fa-undo"></i> ตีกลับ</button>
                                    </form>

                                    <form action="{{ route('admin.driver.trip-items.delivery-status', $item) }}" method="POST" class="mt-2">
                                        @csrf
                                        <input type="hidden" name="delivery_status" value="{{ \App\Models\TripItem::DELIVERY_STATUS_FAILED }}">
                                        <div class="input-group input-group-sm">
                                            <select name="failed_reason" class="form-control" required>
                                                @foreach($failedReasons as $reason)
                                                    <option value="{{ $reason }}">{{ $reason }}</option>
                                                @endforeach
                                            </select>
                                            <input type="text" name="note" class="form-control" placeholder="หมายเหตุ">
                                            <div class="input-group-append">
                                                <button type="submit" class="btn bg-danger"><i class="fas fa-times"></i> ส่งไม่สำเร็จ</button>
                                            </div>
                                        </div>
                                    </form>

                                    @if((float) $item->cod_amount > 0)
                                        @if($isDelivered && ! $isPaid)
                                            <form action="{{ route('admin.driver.trip-items.payment-status', $item) }}" method="POST" class="mt-2">
                                                @csrf
                                                <input type="hidden" name="payment_status" value="{{ \App\Models\TripItem::PAYMENT_STATUS_PAID }}">
                                                <div class="input-group input-group-sm">
                                                    <input type="number" step="0.01" min="0" name="collected_amount" value="{{ $item->cod_amount }}" class="form-control">
                                                    <div class="input-group-append">
                                                        <button type="submit" class="btn bg-success"><i class="fas fa-money-bill-wave"></i> เก็บเงินแล้ว</button>
                                                    </div>
                                                </div>
                                            </form>
                                        @elseif(! $isDelivered)
                                            <small class="d-block text-muted mt-2">เก็บเงิน COD ได้หลังส่งสำเร็จ</small>
                                        @else
                                            <small class="d-block text-success mt-2">เก็บเงินแล้ว</small>
                                        @endif
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center text-muted">ยังไม่มีพัสดุในรอบนี้</div>
                    </div>
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection
