@extends('layouts.app')

@section('content')
<div class="container-fluid ta-page-shell">
    <section class="ta-search-hero">
        <span class="ta-page-kicker"><i class="fas fa-search-location" aria-hidden="true"></i> Parcel Search</span>
        <h1 class="ta-search-hero__title">ค้นหาพัสดุ</h1>
        <p class="ta-search-hero__subtitle">ค้นหาด้วยรหัสพัสดุเพื่อเปิดหน้าประวัติ ติดตามสถานะ และเข้าถึงข้อมูลออเดอร์อย่างรวดเร็ว</p>
        <form action="{{ route('admin.parcels.search') }}" method="GET">
            <div class="input-group">
                <input type="text" name="q" value="{{ $keyword }}" class="form-control" placeholder="กรอกรหัสพัสดุ เช่น P2026...">
                <div class="input-group-append">
                    <button type="submit" class="btn bg-primary"><i class="fas fa-search"></i> ค้นหา</button>
                </div>
            </div>
        </form>
    </section>

    <section class="content">
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
    </section>

    @if($keyword !== '')
        <section class="card ta-table-card">
            <div class="card-header">
                <div>
                    <h3 class="ta-section-title">ผลการค้นหา</h3>
                    <p class="ta-section-subtitle">ผลลัพธ์สำหรับรหัส <strong>{{ $keyword }}</strong></p>
                </div>
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
                                    <div class="ta-table-actions">
                                        <a href="{{ route('admin.parcels.tracking', $parcel) }}" class="btn btn-default btn-xs"><i class="fas fa-history"></i> ดูประวัติ</a>
                                        <a href="{{ route('admin.parcels.code', $parcel->parcel_code) }}" class="btn bg-info btn-xs"><i class="fas fa-external-link-alt"></i> เปิด</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="ta-empty-state">
                                        <div class="ta-empty-state__icon"><i class="fas fa-box-open"></i></div>
                                        <div>ไม่พบรหัสพัสดุ {{ $keyword }}</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</div>
@endsection
