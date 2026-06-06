@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <section class="content-header">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="font-weight-bold mb-0">รอบขนส่ง {{ $data->code }}</h5>
                        <small class="text-muted">{{ optional($data->trip_date)->format('Y-m-d') }} | {{ $data->status_label }}</small>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('admin.trips.index') }}" class="btn bg-secondary"><i class="fas fa-arrow-left"></i> กลับ</a>
                        <a href="{{ route('admin.trips.driver', $data) }}" class="btn bg-success"><i class="fas fa-mobile-alt"></i> Driver View</a>
                        <a href="{{ route('admin.trips.labels', $data) }}" class="btn bg-dark"><i class="fas fa-qrcode"></i> พิมพ์ Label</a>
                        <a href="{{ route('admin.trips.items.export.csv', $data) }}" class="btn bg-info"><i class="fas fa-file-csv"></i> รายการพัสดุ CSV</a>
                        <a href="{{ route('admin.trips.cod.export.csv', $data) }}" class="btn bg-warning"><i class="fas fa-file-csv"></i> COD CSV</a>
                        @if(! $readOnly)
                            <a href="{{ route('admin.trips.edit', $data) }}" class="btn bg-info"><i class="fas fa-edit"></i> แก้ไข</a>
                            <a href="{{ route('admin.trips.assign', $data) }}" class="btn bg-success"><i class="fas fa-plus"></i> เพิ่มพัสดุเข้ารอบ</a>
                        @endif
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

        <div class="row">
            <div class="col-md-2 col-6">
                <div class="small-box bg-info"><div class="inner"><h3>{{ number_format($data->total_parcels) }}</h3><p>พัสดุทั้งหมด</p></div></div>
            </div>
            <div class="col-md-2 col-6">
                <div class="small-box bg-success"><div class="inner"><h3>{{ number_format($summary['delivered_count']) }}</h3><p>ส่งสำเร็จ</p></div></div>
            </div>
            <div class="col-md-2 col-6">
                <div class="small-box bg-danger"><div class="inner"><h3>{{ number_format($summary['failed_count']) }}</h3><p>ส่งไม่สำเร็จ</p></div></div>
            </div>
            <div class="col-md-2 col-6">
                <div class="small-box bg-warning"><div class="inner"><h3>{{ number_format($summary['returned_count']) }}</h3><p>ส่งคืน</p></div></div>
            </div>
            <div class="col-md-2 col-6">
                <div class="small-box bg-primary"><div class="inner"><h3>{{ number_format($data->total_cod_amount, 2) }}</h3><p>COD รวม</p></div></div>
            </div>
            <div class="col-md-2 col-6">
                <div class="small-box bg-secondary"><div class="inner"><h3>{{ number_format($summary['remaining_cod'], 2) }}</h3><p>COD คงเหลือ</p></div></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">ต้นทุนและกำไรโดยประมาณ</h3>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 col-12">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ number_format($financialSummary['revenue'], 2) }}</h3>
                                <p>รายรับค่าขนส่ง</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>{{ number_format($financialSummary['total_cost'], 2) }}</h3>
                                <p>ต้นทุนรวม</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="small-box {{ $financialSummary['estimated_profit'] >= 0 ? 'bg-success' : 'bg-danger' }}">
                            <div class="inner">
                                <h3>{{ number_format($financialSummary['estimated_profit'], 2) }}</h3>
                                <p>กำไรโดยประมาณ</p>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-muted small mb-3">
                    รายรับคำนวณแบบอนุรักษ์นิยมจากผลรวม `parcel_pice` ของพัสดุในรอบนี้ หักค่าใช้จ่ายที่บันทึกด้านล่าง
                </p>

                @if(! $readOnly)
                    <form action="{{ route('admin.trips.costs.store', $data) }}" method="POST" class="mb-3">
                        @csrf
                        <div class="row">
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
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn bg-success btn-block mb-3"><i class="fas fa-plus"></i> เพิ่มค่าใช้จ่าย</button>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="alert alert-secondary">รอบขนส่งที่เสร็จสิ้นหรือยกเลิกแล้ว แก้ไขค่าใช้จ่ายไม่ได้</div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead>
                            <tr>
                                <th>ประเภท</th>
                                <th>รายละเอียด</th>
                                <th class="text-right">จำนวนเงิน</th>
                                <th>ผู้บันทึก</th>
                                <th style="width: 90px;"></th>
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
                                    <td colspan="5" class="text-center text-muted">ยังไม่มีค่าใช้จ่าย</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h3 class="card-title">ข้อมูลรอบขนส่ง</h3>
                    </div>
                    <div class="col-md-6 text-right">
                        @if($data->status === \App\Models\Trip::STATUS_DRAFT)
                            <form action="{{ route('admin.trips.assign-status', $data) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn bg-info btn-sm"><i class="fas fa-user-check"></i> มอบหมายรอบ</button>
                            </form>
                        @endif
                        @if($data->status === \App\Models\Trip::STATUS_ASSIGNED)
                            <form action="{{ route('admin.trips.start', $data) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn bg-primary btn-sm"><i class="fas fa-play"></i> เริ่มจัดส่ง</button>
                            </form>
                        @endif
                        @if(in_array($data->status, [\App\Models\Trip::STATUS_DRAFT, \App\Models\Trip::STATUS_ASSIGNED], true))
                            <form action="{{ route('admin.trips.cancel', $data) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการยกเลิกรอบขนส่ง?')">
                                @csrf
                                <button type="submit" class="btn bg-danger btn-sm"><i class="fas fa-times"></i> ยกเลิก</button>
                            </form>
                        @endif
                        @if($data->status === \App\Models\Trip::STATUS_IN_TRANSIT)
                            <form action="{{ route('admin.trips.complete', $data) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการปิดรอบขนส่ง?')">
                                @csrf
                                <button type="submit" class="btn bg-success btn-sm"><i class="fas fa-check"></i> ปิดรอบขนส่ง</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3"><strong>พนักงานขับรถ:</strong> {{ $data->driver_name ?: '-' }}</div>
                    <div class="col-md-3"><strong>เบอร์โทร:</strong> {{ $data->driver_mobile ?: '-' }}</div>
                    <div class="col-md-3"><strong>ทะเบียนรถ:</strong> {{ $data->car_id ?: '-' }}</div>
                    <div class="col-md-3"><strong>พื้นที่:</strong> {{ $data->area_name ?: '-' }}</div>
                    <div class="col-md-3"><strong>เก็บแล้ว:</strong> {{ number_format($data->collected_amount, 2) }}</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">รายการพัสดุในรอบ</h3>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-bordered table-sm">
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
                            <th style="min-width: 280px;">ดำเนินการ</th>
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
                                <td><span class="badge badge-info">{{ $item->delivery_status_label }}</span></td>
                                <td><span class="badge badge-secondary">{{ $item->payment_status_label }}</span></td>
                                <td>
                                    @if($receiver)
                                        <a href="{{ route('admin.parcels.tracking', $receiver) }}" class="btn bg-secondary btn-xs mb-2"><i class="fas fa-history"></i> ดูประวัติ</a>
                                    @endif
                                    @if($readOnly)
                                        <span class="text-muted">อ่านอย่างเดียว</span>
                                    @else
                                        <form action="{{ route('admin.trip-items.delivery-status', $item) }}" method="POST" class="mb-2">
                                            @csrf
                                            <div class="input-group input-group-sm">
                                                <select name="delivery_status" class="form-control">
                                                    @foreach($deliveryStatusLabels as $status => $label)
                                                        <option value="{{ $status }}" {{ $item->delivery_status === $status ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="text" name="failed_reason" class="form-control" placeholder="เหตุผล/หมายเหตุ">
                                                <input type="text" name="note" class="form-control" placeholder="หมายเหตุ">
                                                <div class="input-group-append">
                                                    <button type="submit" class="btn bg-info">บันทึก</button>
                                                </div>
                                            </div>
                                        </form>
                                        <form action="{{ route('admin.trip-items.payment-status', $item) }}" method="POST" class="mb-2">
                                            @csrf
                                            <div class="input-group input-group-sm">
                                                <select name="payment_status" class="form-control">
                                                    @foreach($paymentStatusLabels as $status => $label)
                                                        <option value="{{ $status }}" {{ $item->payment_status === $status ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="number" step="0.01" min="0" name="collected_amount" value="{{ $item->collected_amount }}" class="form-control" placeholder="ยอดเงิน">
                                                <input type="text" name="note" class="form-control" placeholder="หมายเหตุ">
                                                <div class="input-group-append">
                                                    <button type="submit" class="btn bg-success">บันทึก</button>
                                                </div>
                                            </div>
                                        </form>
                                        @if(in_array($data->status, [\App\Models\Trip::STATUS_DRAFT, \App\Models\Trip::STATUS_ASSIGNED], true))
                                            <form action="{{ route('admin.trip-items.remove', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('ลบพัสดุออกจากรอบ?')">
                                                @csrf
                                                <button type="submit" class="btn bg-danger btn-xs"><i class="fas fa-trash"></i> ลบออกจากรอบ</button>
                                            </form>
                                        @endif
                                    @endif
                                    @if($receiver && $receiver->statusLogs->count())
                                        <details class="mt-2">
                                            <summary class="small">ประวัติสถานะ</summary>
                                            @foreach($receiver->statusLogs->sortByDesc('created_at') as $log)
                                                <small class="d-block">{{ $log->created_at }}: {{ $log->from_status ?: '-' }} → {{ $log->to_status }} {{ $log->note }}</small>
                                            @endforeach
                                        </details>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">ยังไม่มีพัสดุในรอบนี้</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection
