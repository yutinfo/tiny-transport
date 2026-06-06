@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <section class="content-header">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="font-weight-bold mb-0">เพิ่มพัสดุเข้ารอบ {{ $data->code }}</h5>
                        <small class="text-muted">{{ optional($data->trip_date)->format('Y-m-d') }} | {{ $data->status_label }}</small>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('admin.trips.show', $data) }}" class="btn bg-secondary"><i class="fas fa-arrow-left"></i> กลับรายละเอียด</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">ค้นหาพัสดุที่รอจัดส่ง</h3>
            </div>
            <form action="{{ route('admin.trips.assign', $data) }}" method="GET">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2"><div class="form-group"><label>วันที่เริ่ม</label><input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control"></div></div>
                        <div class="col-md-2"><div class="form-group"><label>วันที่สิ้นสุด</label><input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control"></div></div>
                        <div class="col-md-2"><div class="form-group"><label>จังหวัด</label><input type="text" name="province_name" value="{{ request('province_name') }}" class="form-control"></div></div>
                        <div class="col-md-2"><div class="form-group"><label>อำเภอ</label><input type="text" name="amphures_name" value="{{ request('amphures_name') }}" class="form-control"></div></div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>ชำระเงิน</label>
                                <select name="payment_type" class="form-control">
                                    <option value="">ทั้งหมด</option>
                                    <option value="immediately" {{ request('payment_type') === 'immediately' ? 'selected' : '' }}>จ่ายทันที</option>
                                    <option value="on_delivery" {{ request('payment_type') === 'on_delivery' ? 'selected' : '' }}>เก็บปลายทาง</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>รูปแบบจัดส่ง</label>
                                <select name="parcel_pickup_type" class="form-control">
                                    <option value="">ทั้งหมด</option>
                                    <option value="pickup" {{ request('parcel_pickup_type') === 'pickup' ? 'selected' : '' }}>รับเอง</option>
                                    <option value="delivery" {{ request('parcel_pickup_type') === 'delivery' ? 'selected' : '' }}>จัดส่ง</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8"><div class="form-group mb-0"><label>ค้นหา</label><input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control" placeholder="รหัสพัสดุ ผู้รับ เบอร์โทร หรือรหัสออเดอร์"></div></div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn bg-info mr-2"><i class="fas fa-search"></i> ค้นหา</button>
                            <a href="{{ route('admin.trips.assign', $data) }}" class="btn bg-secondary"><i class="fas fa-redo"></i> ล้าง</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <form action="{{ route('admin.trips.assign-items', $data) }}" method="POST">
            @csrf
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">รายการพัสดุที่เลือกได้</h3>
                    <div class="card-tools">
                        <button type="submit" class="btn bg-success btn-sm"><i class="fas fa-plus"></i> เพิ่มพัสดุที่เลือก</button>
                    </div>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-striped table-bordered table-sm">
                        <thead>
                            <tr>
                                <th><input type="checkbox" onclick="$('input[name=&quot;order_receive_ids[]&quot;]').prop('checked', this.checked)"></th>
                                <th>รหัสพัสดุ</th>
                                <th>ออเดอร์</th>
                                <th>ผู้ฝาก</th>
                                <th>ผู้รับ</th>
                                <th>เบอร์ผู้รับ</th>
                                <th>ปลายทาง</th>
                                <th>ชำระเงิน</th>
                                <th>รูปแบบ</th>
                                <th class="text-right">ราคา</th>
                                <th>วันที่สร้าง</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($candidates as $receiver)
                                <tr>
                                    <td><input type="checkbox" name="order_receive_ids[]" value="{{ $receiver->id }}"></td>
                                    <td>{{ $receiver->parcel_code }}</td>
                                    <td>{{ $receiver->order->code ?? '-' }}</td>
                                    <td>{{ $receiver->order->customer_name ?? '-' }}</td>
                                    <td>{{ $receiver->receive_name }}</td>
                                    <td>{{ $receiver->receive_mobile }}</td>
                                    <td>{{ $receiver->district_name }} {{ $receiver->amphures_name }} {{ $receiver->province_name }} {{ $receiver->zip_code }}</td>
                                    <td>{{ $receiver->payment_type }}</td>
                                    <td>{{ $receiver->parcel_pickup_type }}</td>
                                    <td class="text-right">{{ number_format($receiver->parcel_pice, 2) }}</td>
                                    <td>{{ optional($receiver->created_at)->format('Y-m-d') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="11" class="text-center text-muted">ไม่พบพัสดุที่เลือกได้</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $candidates->links() }}
                </div>
            </div>
        </form>
    </section>
</div>
@endsection
