@extends('layouts.app')

@php
    $tripDateLabel = optional($data->trip_date)->format('Y-m-d');
    $overviewCards = [
        ['value' => number_format($data->total_parcels), 'label' => 'พัสดุทั้งหมด'],
        ['value' => number_format($summary['delivered_count']), 'label' => 'ส่งสำเร็จ'],
        ['value' => number_format($summary['failed_count']), 'label' => 'ส่งไม่สำเร็จ'],
        ['value' => number_format($summary['returned_count']), 'label' => 'ส่งคืน'],
        ['value' => number_format($data->total_cod_amount, 2), 'label' => 'COD รวม'],
        ['value' => number_format($summary['remaining_cod'], 2), 'label' => 'COD คงเหลือ'],
    ];
    $financialCards = [
        ['value' => number_format($financialSummary['revenue'], 2), 'label' => 'รายรับค่าขนส่ง', 'accent' => 'info'],
        ['value' => number_format($financialSummary['total_cost'], 2), 'label' => 'ต้นทุนรวม', 'accent' => 'warning'],
        ['value' => number_format($financialSummary['estimated_profit'], 2), 'label' => 'กำไรโดยประมาณ', 'accent' => $financialSummary['estimated_profit'] >= 0 ? 'success' : 'danger'],
    ];
@endphp

@section('content')
<div class="container-fluid ta-page-shell">
    <section class="ta-page-header-card">
        <div class="card-body">
            <div class="ta-page-header-row">
                <div>
                    <span class="ta-page-kicker"><i class="fas fa-truck" aria-hidden="true"></i> Trips</span>
                    <h1 class="ta-page-title">รอบขนส่ง {{ $data->code }}</h1>
                    <p class="ta-page-subtitle">จัดการข้อมูลรอบ พัสดุ และสถานะการจัดส่งจากมุมมองเดียว</p>
                </div>
                <div class="ta-page-actions">
                    <a href="{{ route('admin.trips.index') }}" class="btn btn-default"><i class="fas fa-arrow-left"></i> กลับ</a>
                    <a href="{{ route('admin.trips.driver', $data) }}" class="btn bg-success"><i class="fas fa-mobile-alt"></i> Driver View</a>
                    <a href="{{ route('admin.trips.labels', $data) }}" class="btn btn-default"><i class="fas fa-qrcode"></i> พิมพ์ Label</a>
                    <a href="{{ route('admin.trips.items.export.csv', $data) }}" class="btn btn-default"><i class="fas fa-file-csv"></i> รายการพัสดุ CSV</a>
                    <a href="{{ route('admin.trips.cod.export.csv', $data) }}" class="btn btn-default"><i class="fas fa-file-csv"></i> COD CSV</a>
                    @if(! $readOnly)
                        <a href="{{ route('admin.trips.edit', $data) }}" class="btn bg-info"><i class="fas fa-edit"></i> แก้ไข</a>
                        <a href="{{ route('admin.trips.assign', $data) }}" class="btn bg-primary"><i class="fas fa-plus"></i> เพิ่มพัสดุเข้ารอบ</a>
                    @endif
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
    </section>

    <section class="card ta-state-card">
        <div class="card-header">
            <div>
                <h3 class="ta-section-title">ภาพรวมรอบขนส่ง</h3>
                <p class="ta-section-subtitle">สรุปปริมาณพัสดุ ผลการจัดส่ง และยอด COD ของรอบนี้</p>
            </div>
        </div>
        <div class="card-body">
            <div class="ta-kpi-grid">
                @foreach($overviewCards as $card)
                    <article class="ta-kpi-card">
                        <span class="ta-kpi-card__value">{{ $card['value'] }}</span>
                        <span class="ta-kpi-card__label">{{ $card['label'] }}</span>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <div class="ta-form-layout ta-trip-summary-layout">
        <div class="ta-form-main">
            <section class="card ta-form-section">
                <div class="card-header">
                    <div>
                        <h3 class="ta-section-title">ต้นทุนและกำไรโดยประมาณ</h3>
                        <p class="ta-section-subtitle">ติดตามรายรับ ต้นทุน และอัปเดตค่าใช้จ่ายที่เกิดขึ้นระหว่างรอบขนส่ง</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($financialCards as $card)
                            <div class="col-md-4 col-sm-12 mb-3">
                                <div class="card dashboard-metric dashboard-metric--{{ $card['accent'] }}">
                                    <div class="card-body">
                                        <div class="dashboard-metric__head">
                                            <div>
                                                <span class="dashboard-metric__label">{{ $card['label'] }}</span>
                                                <span class="dashboard-metric__value">{{ $card['value'] }}</span>
                                            </div>
                                            <span class="dashboard-metric__icon" aria-hidden="true">
                                                <i class="fas fa-chart-line"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <p class="text-muted small mb-3">
                        รายรับคำนวณแบบอนุรักษ์นิยมจากผลรวม `parcel_pice` ของพัสดุในรอบนี้ หักค่าใช้จ่ายที่บันทึกด้านล่าง
                    </p>

                    @if(! $readOnly)
                        <form action="{{ route('admin.trips.costs.store', $data) }}" method="POST" class="ta-trip-cost-form">
                            @csrf
                            <div class="ta-form-grid">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="cost_type">ประเภทค่าใช้จ่าย</label>
                                        <select name="type" id="cost_type" class="form-control" required>
                                            @foreach($costTypeLabels as $type => $label)
                                                <option value="{{ $type }}" {{ old('type') === $type ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="cost_description">รายละเอียด</label>
                                        <input type="text" name="description" id="cost_description" value="{{ old('description') }}" class="form-control" maxlength="255">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="cost_amount">จำนวนเงิน</label>
                                        <input type="number" step="0.01" min="0.01" name="amount" id="cost_amount" value="{{ old('amount') }}" class="form-control text-right" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group ta-trip-cost-form__submit">
                                        <label class="d-block">&nbsp;</label>
                                        <button type="submit" class="btn bg-success btn-block"><i class="fas fa-plus"></i> เพิ่มค่าใช้จ่าย</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-secondary mb-0">รอบขนส่งที่เสร็จสิ้นหรือยกเลิกแล้ว แก้ไขค่าใช้จ่ายไม่ได้</div>
                    @endif
                </div>
            </section>

            <section class="card ta-table-card">
                <div class="card-header">
                    <div>
                        <h3 class="ta-section-title">รายการค่าใช้จ่าย</h3>
                        <p class="ta-section-subtitle">ตรวจสอบรายการต้นทุนที่ใช้คำนวณกำไรของรอบขนส่งนี้</p>
                    </div>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>ประเภท</th>
                                <th>รายละเอียด</th>
                                <th class="text-right">จำนวนเงิน</th>
                                <th>ผู้บันทึก</th>
                                <th class="text-center" style="width: 110px;">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data->costs as $cost)
                                <tr>
                                    <td>{{ $cost->type_label }}</td>
                                    <td>{{ $cost->description ?: '-' }}</td>
                                    <td class="text-right">{{ number_format($cost->amount, 2) }}</td>
                                    <td>{{ $cost->created_by ?: '-' }}</td>
                                    <td class="text-center">
                                        @if(! $readOnly)
                                            <form action="{{ route('admin.trip-costs.destroy', $cost) }}" method="POST" onsubmit="return confirm('ลบค่าใช้จ่ายนี้?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn bg-danger btn-xs"><i class="fas fa-trash"></i> ลบ</button>
                                            </form>
                                        @else
                                            <span class="text-muted small">อ่านอย่างเดียว</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="ta-empty-state py-3">
                                            <div class="ta-empty-state__icon"><i class="fas fa-receipt"></i></div>
                                            <div>ยังไม่มีค่าใช้จ่าย</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

        </div>

        <div class="ta-form-sidebar">
            <section class="card ta-state-card">
                <div class="card-header">
                    <div>
                        <h3 class="ta-section-title">ข้อมูลรอบและการดำเนินการ</h3>
                        <p class="ta-section-subtitle">สถานะรอบ ข้อมูลคนขับ และ action หลักสำหรับเจ้าหน้าที่</p>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="ta-info-list mb-0">
                        <div class="ta-info-list__item">
                            <dt>รหัสรอบ</dt>
                            <dd>{{ $data->code }}</dd>
                        </div>
                        <div class="ta-info-list__item">
                            <dt>วันที่จัดส่ง</dt>
                            <dd>{{ $tripDateLabel ?: '-' }}</dd>
                        </div>
                        <div class="ta-info-list__item">
                            <dt>สถานะ</dt>
                            <dd><span class="badge {{ $data->status_badge_class }}">{{ $data->status_label }}</span></dd>
                        </div>
                        <div class="ta-info-list__item">
                            <dt>พนักงานขับรถ</dt>
                            <dd>{{ $data->driver_name ?: '-' }}</dd>
                        </div>
                        <div class="ta-info-list__item">
                            <dt>เบอร์โทร</dt>
                            <dd>{{ $data->driver_mobile ?: '-' }}</dd>
                        </div>
                        <div class="ta-info-list__item">
                            <dt>ทะเบียนรถ</dt>
                            <dd>{{ $data->car_id ?: '-' }}</dd>
                        </div>
                        <div class="ta-info-list__item">
                            <dt>พื้นที่</dt>
                            <dd>{{ $data->area_name ?: '-' }}</dd>
                        </div>
                        <div class="ta-info-list__item">
                            <dt>เก็บแล้ว</dt>
                            <dd>{{ number_format($data->collected_amount, 2) }}</dd>
                        </div>
                    </dl>

                    <hr>

                    <div class="ta-page-actions">
                        @if($data->status === \App\Models\Trip::STATUS_DRAFT)
                            <form action="{{ route('admin.trips.assign-status', $data) }}" method="POST" onsubmit="return confirm('ยืนยันการมอบหมายรอบขนส่งนี้?')">
                                @csrf
                                <button type="submit" class="btn bg-info"><i class="fas fa-user-check"></i> มอบหมายรอบ</button>
                            </form>
                        @endif
                        @if($data->status === \App\Models\Trip::STATUS_ASSIGNED)
                            <form action="{{ route('admin.trips.start', $data) }}" method="POST" onsubmit="return confirm('ยืนยันการเริ่มจัดส่งรอบขนส่งนี้?')">
                                @csrf
                                <button type="submit" class="btn bg-primary"><i class="fas fa-play"></i> เริ่มจัดส่ง</button>
                            </form>
                        @endif
                        @if(in_array($data->status, [\App\Models\Trip::STATUS_DRAFT, \App\Models\Trip::STATUS_ASSIGNED], true))
                            <form action="{{ route('admin.trips.cancel', $data) }}" method="POST" onsubmit="return confirm('ยืนยันการยกเลิกรอบขนส่ง?')">
                                @csrf
                                <button type="submit" class="btn bg-danger"><i class="fas fa-times"></i> ยกเลิก</button>
                            </form>
                        @endif
                        @if($data->status === \App\Models\Trip::STATUS_IN_TRANSIT)
                            <form action="{{ route('admin.trips.complete', $data) }}" method="POST" onsubmit="return confirm('ยืนยันการบังคับปิดรอบขนส่ง (พนักงานขับรถยังไม่ได้กดส่งยอด)?')">
                                @csrf
                                <button type="submit" class="btn bg-warning text-white"><i class="fas fa-exclamation-triangle"></i> บังคับปิดรอบขนส่ง</button>
                            </form>
                        @endif
                        @if($data->status === \App\Models\Trip::STATUS_PENDING_VERIFICATION)
                            <form action="{{ route('admin.trips.complete', $data) }}" method="POST" onsubmit="return confirm('ยืนยันการปิดรอบขนส่งและยืนยันยอดเงิน?')">
                                @csrf
                                <button type="submit" class="btn bg-success"><i class="fas fa-check-double"></i> ยืนยันและปิดรอบขนส่ง</button>
                            </form>
                        @endif
                    </div>
                </div>
            </section>
        </div>
    </div>

    <section class="card ta-table-card ta-trip-full-width-section">
        <div class="card-header">
            <div>
                <h3 class="ta-section-title">รายการพัสดุในรอบ</h3>
                <p class="ta-section-subtitle">อัปเดตสถานะจัดส่ง การเก็บเงิน และดูประวัติการเคลื่อนไหวของพัสดุแต่ละชิ้น</p>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-bordered mb-0 ta-trip-items-table">
                <thead>
                    <tr>
                        <th>รหัสพัสดุ</th>
                        <th>ออเดอร์</th>
                        <th>ผู้รับ</th>
                        <th>ที่อยู่</th>
                        <th class="text-right">COD</th>
                        <th class="text-right">เก็บแล้ว</th>
                        <th>จัดส่ง</th>
                        <th>ชำระเงิน</th>
                        <th style="min-width: 320px;">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        @php($receiver = $item->orderReceive)
                        <tr>
                            <td>{{ $item->parcel_code }}</td>
                            <td>{{ $item->order->code ?? '-' }}</td>
                            <td>
                                {{ $receiver->receive_name ?? '-' }}
                                <small class="d-block text-muted">{{ $receiver->receive_mobile ?? '' }}</small>
                            </td>
                            <td>
                                {{ $receiver->receive_address ?? '' }}
                                <small class="d-block text-muted">{{ $receiver->district_name }} {{ $receiver->amphures_name }} {{ $receiver->province_name }} {{ $receiver->zip_code }}</small>
                            </td>
                            <td class="text-right">{{ number_format($item->cod_amount, 2) }}</td>
                            <td class="text-right">{{ number_format($item->collected_amount, 2) }}</td>
                            <td><span class="badge {{ $item->delivery_status_badge_class }}">{{ $item->delivery_status_label }}</span></td>
                            <td><span class="badge {{ $item->payment_status_badge_class }}">{{ $item->payment_status_label }}</span></td>
                            <td>
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

                                        @if(in_array($data->status, [\App\Models\Trip::STATUS_DRAFT, \App\Models\Trip::STATUS_ASSIGNED], true))
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
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="ta-empty-state">
                                    <div class="ta-empty-state__icon"><i class="fas fa-box-open"></i></div>
                                    <div>ยังไม่มีพัสดุในรอบนี้</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
