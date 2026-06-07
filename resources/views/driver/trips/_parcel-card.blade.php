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

<div class="driver-parcel-card mb-3 {{ ($isActive ?? true) ? 'driver-parcel-card--active' : 'driver-parcel-card--completed' }}">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
            <h6 class="font-weight-bold mb-0 text-primary">{{ $item->parcel_code }}</h6>
            <small class="text-muted">{{ $item->order->code ?? '-' }}</small>
        </div>
        <div class="text-right">
            <span class="badge {{ $item->delivery_status_badge_class }}">{{ $item->delivery_status_label }}</span>
            <span class="badge {{ $item->payment_status_badge_class }}">{{ $item->payment_status_label }}</span>
        </div>
    </div>

    <div class="mb-2 small">
        <div class="font-weight-bold">{{ $receiver->receive_name ?? '-' }}</div>
        @if($receiver && $receiver->receive_mobile)
            <div><a href="tel:{{ $receiver->receive_mobile }}"><i class="fas fa-phone-alt"></i> {{ $receiver->receive_mobile }}</a></div>
        @else
            <div class="text-muted">ไม่มีเบอร์โทร</div>
        @endif
        <div class="text-muted mt-1">{{ $address ?: '-' }}</div>
    </div>

    <div class="mb-3 small">
        <strong>COD:</strong> {{ number_format($item->cod_amount, 2) }} บาท
        <span class="text-muted">| เก็บแล้ว {{ number_format($item->collected_amount, 2) }} บาท</span>
    </div>

    @if($isActive ?? true)
        <div class="driver-action-grid">
            <a href="tel:{{ $receiver->receive_mobile ?? '' }}" class="btn btn-outline-info btn-sm {{ empty($receiver->receive_mobile) ? 'disabled' : '' }}">
                <i class="fas fa-phone"></i> โทร
            </a>
            <a href="{{ $mapsUrl }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-map-marker-alt"></i> แผนที่
            </a>
        </div>
    @endif

    <div class="mt-2">
        @if($readOnly)
            @if($item->trip->status === \App\Models\Trip::STATUS_ASSIGNED)
                <div class="text-center text-muted small py-1 bg-light rounded"><i class="fas fa-lock mr-1"></i> รอบริเริ่มจัดส่ง</div>
            @elseif($item->trip->status === \App\Models\Trip::STATUS_PENDING_VERIFICATION)
                <div class="text-center text-warning small py-1 bg-light rounded"><i class="fas fa-hourglass-half mr-1"></i> ส่งยอดแล้ว รอตรวจสอบ</div>
            @else
                <div class="text-center text-muted small py-1 bg-light rounded">อ่านอย่างเดียว</div>
            @endif
        @else
            @if($isActive ?? true)
                <div class="d-flex gap-2">
                    <form action="{{ route('driver.trip-items.delivery-status', $item) }}" method="POST" class="flex-fill mr-1 d-inline-block" style="width: 49%">
                        @csrf
                        <input type="hidden" name="delivery_status" value="{{ \App\Models\TripItem::DELIVERY_STATUS_DELIVERED }}">
                        <input type="hidden" name="note" value="ส่งสำเร็จ">
                        <button type="submit" class="btn btn-success btn-sm btn-block driver-full-btn">
                            <i class="fas fa-check"></i> ส่งสำเร็จ
                        </button>
                    </form>

                    <form action="{{ route('driver.trip-items.delivery-status', $item) }}" method="POST" class="flex-fill d-inline-block" style="width: 49%">
                        @csrf
                        <input type="hidden" name="delivery_status" value="{{ \App\Models\TripItem::DELIVERY_STATUS_RETURNED }}">
                        <input type="hidden" name="note" value="ตีกลับ">
                        <button type="submit" class="btn btn-warning btn-sm btn-block driver-full-btn">
                            <i class="fas fa-undo"></i> ตีกลับ
                        </button>
                    </form>
                </div>

                <form action="{{ route('driver.trip-items.delivery-status', $item) }}" method="POST" class="mt-2">
                    @csrf
                    <input type="hidden" name="delivery_status" value="{{ \App\Models\TripItem::DELIVERY_STATUS_FAILED }}">
                    <div class="input-group input-group-sm">
                        <select name="failed_reason" class="form-control" required>
                            <option value="">-- เหตุผลไม่สำเร็จ --</option>
                            @foreach($failedReasons as $reason)
                                <option value="{{ $reason }}">{{ $reason }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="note" class="form-control" placeholder="หมายเหตุ">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-danger">ส่งไม่สำเร็จ</button>
                        </div>
                    </div>
                </form>
            @else
                @if($item->delivery_status === \App\Models\TripItem::DELIVERY_STATUS_FAILED || $item->delivery_status === \App\Models\TripItem::DELIVERY_STATUS_RETURNED)
                    <div class="bg-light rounded p-2 mb-2 small text-muted border">
                        @if($item->failed_reason)
                            <div><strong>เหตุผล:</strong> {{ $item->failed_reason }}</div>
                        @endif
                        @if($item->note)
                            <div><strong>หมายเหตุ:</strong> {{ $item->note }}</div>
                        @endif
                    </div>
                @endif
            @endif

            @if((float) $item->cod_amount > 0)
                <div class="mt-2">
                    @if($isDelivered && ! $isPaid)
                        <form action="{{ route('driver.trip-items.payment-status', $item) }}" method="POST">
                            @csrf
                            <input type="hidden" name="payment_status" value="{{ \App\Models\TripItem::PAYMENT_STATUS_PAID }}">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">ยอดเก็บเงิน</span>
                                </div>
                                <input type="number" step="0.01" min="0" name="collected_amount" value="{{ $item->cod_amount }}" class="form-control">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-success"><i class="fas fa-money-bill-wave"></i> เก็บเงิน</button>
                                </div>
                            </div>
                        </form>
                    @elseif(! $isDelivered)
                        <div class="text-center text-muted small py-1 bg-light rounded">เก็บเงิน COD ได้หลังส่งสำเร็จ</div>
                    @else
                        <div class="text-center text-success small py-1 bg-light rounded"><i class="fas fa-check-circle"></i> เก็บเงินแล้ว</div>
                    @endif
                </div>
            @endif
        @endif
    </div>
</div>
