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
    $isWaived = $item->payment_status === \App\Models\TripItem::PAYMENT_STATUS_WAIVED;
    $hasCod = (float) $item->cod_amount > 0;
    $codSettled = ! $hasCod || $isPaid || $isWaived;
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
                {{-- COD must be collected BEFORE the parcel can be marked delivered --}}
                @if($hasCod)
                    <div class="driver-cod-box mb-2">
                        @if($isPaid)
                            <div class="text-center text-success small py-1"><i class="fas fa-check-circle mr-1"></i> เก็บเงิน COD แล้ว {{ number_format($item->collected_amount, 2) }} บาท</div>
                        @else
                            <form action="{{ route('driver.trip-items.payment-status', $item) }}" method="POST">
                                @csrf
                                <input type="hidden" name="payment_status" value="{{ \App\Models\TripItem::PAYMENT_STATUS_PAID }}">
                                <div class="driver-cod-title"><i class="fas fa-hand-holding-usd mr-1"></i> เก็บเงิน COD ก่อนส่งมอบ</div>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend"><span class="input-group-text">฿</span></div>
                                    <input type="number" step="0.01" min="0" name="collected_amount" value="{{ $item->cod_amount }}" class="form-control">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-success"><i class="fas fa-money-bill-wave"></i> เก็บเงิน</button>
                                    </div>
                                </div>
                            </form>
                        @endif
                    </div>
                @endif

                {{-- happy path: deliver (gated by COD) --}}
                @if($codSettled)
                    <form action="{{ route('driver.trip-items.delivery-status', $item) }}" method="POST">
                        @csrf
                        <input type="hidden" name="delivery_status" value="{{ \App\Models\TripItem::DELIVERY_STATUS_DELIVERED }}">
                        <input type="hidden" name="note" value="ส่งสำเร็จ">
                        <button type="submit" class="btn btn-success btn-block driver-full-btn">
                            <i class="fas fa-check"></i> ส่งสำเร็จ
                        </button>
                    </form>
                @else
                    <button type="button" class="btn btn-success btn-block driver-full-btn" disabled title="เก็บเงิน COD ก่อน">
                        <i class="fas fa-lock"></i> ส่งสำเร็จ
                    </button>
                    <div class="driver-cod-hint small text-center mt-1"><i class="fas fa-info-circle mr-1"></i> เก็บเงิน COD ครบก่อน จึงจะกด “ส่งสำเร็จ” ได้</div>
                @endif

                <div class="driver-outcome-group mt-2">
                    {{-- failed: retryable — parcel returns to the pool for a future trip --}}
                    <form action="{{ route('driver.trip-items.delivery-status', $item) }}" method="POST">
                        @csrf
                        <input type="hidden" name="delivery_status" value="{{ \App\Models\TripItem::DELIVERY_STATUS_FAILED }}">
                        <label class="driver-outcome-label driver-outcome-label--fail"><i class="fas fa-redo mr-1"></i> ส่งไม่สำเร็จ <span class="text-muted">(ส่งรอบหน้าได้)</span></label>
                        <div class="input-group input-group-sm">
                            <select name="failed_reason" class="form-control" required>
                                <option value="">-- เหตุผล --</option>
                                @foreach($failedReasons as $reason)
                                    <option value="{{ $reason }}">{{ $reason }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="note" class="form-control" placeholder="หมายเหตุ">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-danger">บันทึก</button>
                            </div>
                        </div>
                    </form>

                    {{-- returned: terminal — parcel goes back to the warehouse --}}
                    <form action="{{ route('driver.trip-items.delivery-status', $item) }}" method="POST" class="mt-2"
                          onsubmit="return confirm('ยืนยันตีกลับ — ส่งคืนคลัง?\nพัสดุนี้จะถูกปิดงานและจะไม่ถูกจัดส่งซ้ำ');">
                        @csrf
                        <input type="hidden" name="delivery_status" value="{{ \App\Models\TripItem::DELIVERY_STATUS_RETURNED }}">
                        <label class="driver-outcome-label driver-outcome-label--return"><i class="fas fa-undo mr-1"></i> ตีกลับ <span class="text-muted">(ส่งคืนคลัง · จบงาน)</span></label>
                        <div class="input-group input-group-sm">
                            <select name="failed_reason" class="form-control" required>
                                <option value="">-- เหตุผลตีกลับ --</option>
                                @foreach($returnReasons as $reason)
                                    <option value="{{ $reason }}">{{ $reason }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="note" class="form-control" placeholder="หมายเหตุ">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-warning">ตีกลับ</button>
                            </div>
                        </div>
                    </form>
                </div>
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

                @if($hasCod)
                    <div class="text-center small py-1 bg-light rounded {{ $isPaid ? 'text-success' : 'text-muted' }}">
                        @if($isPaid)
                            <i class="fas fa-check-circle"></i> เก็บเงินแล้ว {{ number_format($item->collected_amount, 2) }} บาท
                        @else
                            ยังไม่ได้เก็บเงิน COD
                        @endif
                    </div>
                @endif
            @endif
        @endif
    </div>
</div>
