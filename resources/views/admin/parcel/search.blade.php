@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <section class="content-header">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="font-weight-bold mb-0">ค้นหาพัสดุ</h5>
                        <small class="text-muted">ค้นหาด้วยรหัสพัสดุ parcel_code</small>
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
            <div class="card-body">
                <form action="{{ route('admin.parcels.search') }}" method="GET">
                    <div class="input-group">
                        <input type="text" name="q" value="{{ $keyword }}" class="form-control" placeholder="กรอกรหัสพัสดุ เช่น P2026...">
                        <div class="input-group-append">
                            <button type="submit" class="btn bg-primary"><i class="fas fa-search"></i> ค้นหา</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($keyword !== '')
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ผลการค้นหา</h3>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-bordered table-striped mb-0">
                        <thead>
                            <tr>
                                <th>รหัสพัสดุ</th>
                                <th>ออเดอร์</th>
                                <th>ผู้รับ</th>
                                <th>ปลายทาง</th>
                                <th>สถานะ</th>
                                <th style="width: 180px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($parcels as $parcel)
                                <tr>
                                    <td>{{ $parcel->parcel_code }}</td>
                                    <td>{{ $parcel->order->code ?? '-' }}</td>
                                    <td>
                                        {{ $parcel->receive_name ?: '-' }}
                                        <small class="d-block text-muted">{{ $parcel->receive_mobile }}</small>
                                    </td>
                                    <td>{{ trim(($parcel->receive_address ?? '') . ' ' . ($parcel->district_name ?? '') . ' ' . ($parcel->amphures_name ?? '') . ' ' . ($parcel->province_name ?? '') . ' ' . ($parcel->zip_code ?? '')) }}</td>
                                    <td>{{ \App\Models\TripItem::deliveryStatusLabel($parcel->delivery_status ?: \App\Models\TripItem::DELIVERY_STATUS_WAITING) }}</td>
                                    <td>
                                        <a href="{{ route('admin.parcels.tracking', $parcel) }}" class="btn bg-secondary btn-xs"><i class="fas fa-history"></i> ดูประวัติ</a>
                                        <a href="{{ route('admin.parcels.code', $parcel->parcel_code) }}" class="btn bg-info btn-xs"><i class="fas fa-external-link-alt"></i> เปิด</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">ไม่พบรหัสพัสดุ {{ $keyword }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </section>
</div>
@endsection
