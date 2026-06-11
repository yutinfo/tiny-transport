<div class="ta-trip-item-actions">
    <div class="ta-trip-item-actions__header">
        @if($receiver)
            <a href="{{ route('admin.parcels.tracking', $receiver) }}" class="btn btn-default btn-xs"><i class="fas fa-history"></i> ดูประวัติ</a>
        @endif
        @if($readOnly)
            <span class="text-muted small align-self-center">อ่านอย่างเดียว</span>
        @endif
    </div>

    @if(! $readOnly)
        <details class="ta-trip-item-details">
            <summary>อัปเดตสถานะจัดส่ง</summary>
            <div class="ta-trip-item-details__body">
                <form action="{{ route('admin.trip-items.delivery-status', $item) }}" method="POST">
                    @csrf
                    <div class="ta-trip-item-form-grid">
                        <div class="ta-trip-item-form-grid__full">
                            <select name="delivery_status" class="form-control form-control-sm">
                                @foreach($deliveryStatusLabels as $status => $label)
                                    <option value="{{ $status }}" {{ $item->delivery_status === $status ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <input type="text" name="failed_reason" class="form-control form-control-sm" placeholder="เหตุผล/หมายเหตุ">
                        </div>
                        <div>
                            <input type="text" name="note" class="form-control form-control-sm" placeholder="หมายเหตุ">
                        </div>
                    </div>
                    <div class="ta-trip-item-action-footer">
                        <button type="submit" class="btn bg-info btn-xs"><i class="fas fa-save"></i> บันทึกสถานะ</button>
                    </div>
                </form>
            </div>
        </details>

        <details class="ta-trip-item-details">
            <summary>อัปเดตการชำระเงิน</summary>
            <div class="ta-trip-item-details__body">
                <form action="{{ route('admin.trip-items.payment-status', $item) }}" method="POST">
                    @csrf
                    <div class="ta-trip-item-form-grid">
                        <div>
                            <select name="payment_status" class="form-control form-control-sm">
                                @foreach($paymentStatusLabels as $status => $label)
                                    <option value="{{ $status }}" {{ $item->payment_status === $status ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <input type="number" step="0.01" min="0" name="collected_amount" value="{{ $item->collected_amount }}" class="form-control form-control-sm text-right" placeholder="ยอดเงิน">
                        </div>
                        <div class="ta-trip-item-form-grid__full">
                            <input type="text" name="note" class="form-control form-control-sm" placeholder="หมายเหตุ">
                        </div>
                    </div>
                    <div class="ta-trip-item-action-footer">
                        <button type="submit" class="btn bg-success btn-xs"><i class="fas fa-save"></i> บันทึกการชำระ</button>
                    </div>
                </form>
            </div>
        </details>

        @if(in_array($trip->status, [\App\Models\Trip::STATUS_DRAFT, \App\Models\Trip::STATUS_ASSIGNED], true))
            <div class="ta-trip-item-actions__remove">
                <form action="{{ route('admin.trip-items.remove', $item) }}" method="POST" onsubmit="return confirm('ลบพัสดุออกจากรอบ?')">
                    @csrf
                    <button type="submit" class="btn bg-danger btn-xs"><i class="fas fa-trash"></i> ลบออกจากรอบ</button>
                </form>
            </div>
        @endif
    @endif

    @if($receiver && $receiver->statusLogs->count())
        <details class="ta-trip-item-details">
            <summary>ประวัติสถานะ</summary>
            <div class="ta-trip-item-details__body">
                @foreach($receiver->statusLogs->sortByDesc('created_at') as $log)
                    <small class="d-block">{{ $log->created_at }}: {{ $log->from_status ?: '-' }} → {{ $log->to_status }} {{ $log->note }}</small>
                @endforeach
            </div>
        </details>
    @endif
</div>
