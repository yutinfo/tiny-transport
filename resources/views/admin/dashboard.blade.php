@extends('layouts.app')

@section('third_party_stylesheets')
<link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
<link rel="stylesheet" href="/plugins/daterangepicker/daterangepicker.css">
<link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
@endsection

@section('third_party_scripts')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="/plugins/select2/js/select2.full.min.js"></script>
<script src="/plugins/moment/moment.min.js"></script>
<script src="/plugins/daterangepicker/daterangepicker.js"></script>
<script src="/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="/plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>


<script src="/plugins/jszip/jszip.min.js"></script>
<script src="/plugins/pdfmake/pdfmake.min.js"></script>
<script src="/plugins/pdfmake/vfs_fonts.js"></script>
@endsection

@push('page_css')
        <style>
        .report-body{
            min-height: 500px;
        }
        .select2-container .select2-selection--single {
        height: 36px;
    }
    .display-none{
            display:none;
        }
        </style>
@endpush

@section('content')
{{-- {{dd($selected)}} --}}
<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-sm-8">
                            <div class="d-flex flex-row">
                                <h5 class="font-weight-bold">
                                    หน้าหลัก
                                </h5>
                                <ol class="breadcrumb ">
                                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"> <small> หน้าหลัก</small></a></li>
                                    <li class="breadcrumb-item active"> <small>แดชบอร์ด</small></li>
                                </ol>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-dolly-flatbed"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">จำนวนพัสดุ</span>
                        <span class="info-box-number">{{Arr::get($data,'count_id',0)}}</span>
                    </div>

                </div>

            </div>

            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="far fa-flag"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">ยอดเงินทั้งหมด</span>
                        <span class="info-box-number">{{number_format(Arr::get($data,'sum_parcel_pice',0),2)}}</span>
                    </div>

                </div>

            </div>

            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-money-bill-alt"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">จ่ายทันที</span>
                        <span class="info-box-number">{{number_format(Arr::get($data,'parcel_pice_immediately',0),2)}}</span>
                    </div>

                </div>

            </div>

            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box">
                    <span class="info-box-icon bg-danger"><i class="fas fa-money-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">เก็บเงินปลายทาง</span>
                        <span class="info-box-number">{{number_format(Arr::get($data,'parcel_pice_on_delivery',0),2)}}</span>
                    </div>

                </div>

            </div>

        </div>

        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h3 class="card-title">KPI การขนส่ง</h3>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('admin.trips.export.csv', request()->only(['date_from', 'date_to', 'driver_name', 'status'])) }}" class="btn bg-success btn-sm">
                            <i class="fas fa-file-csv"></i> ส่งออก CSV
                        </a>
                    </div>
                </div>
            </div>
            <form action="{{ route('admin.dashboard') }}" method="GET">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="date_from">วันที่เริ่ม</label>
                                <input type="date" name="date_from" id="date_from" value="{{ $operationFilters['date_from'] }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="date_to">วันที่สิ้นสุด</label>
                                <input type="date" name="date_to" id="date_to" value="{{ $operationFilters['date_to'] }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="driver_name">พนักงานขับรถ</label>
                                <input type="text" name="driver_name" id="driver_name" value="{{ $operationFilters['driver_name'] }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status">สถานะรอบ</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">ทั้งหมด</option>
                                    @foreach($tripStatusLabels as $status => $label)
                                        <option value="{{ $status }}" {{ $operationFilters['status'] === $status ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn bg-info"><i class="fas fa-search"></i> ค้นหา</button>
                    <a href="{{ route('admin.dashboard') }}" class="btn bg-secondary"><i class="fas fa-redo"></i> ล้าง</a>
                </div>
            </form>
        </div>

        <div class="row">
            <div class="col-lg-3 col-md-4 col-6">
                <div class="small-box bg-info"><div class="inner"><h3>{{ number_format($operationKpis['trips_count']) }}</h3><p>รอบขนส่ง</p></div><div class="icon"><i class="fas fa-truck"></i></div></div>
            </div>
            <div class="col-lg-3 col-md-4 col-6">
                <div class="small-box bg-primary"><div class="inner"><h3>{{ number_format($operationKpis['assigned_count']) }}</h3><p>พัสดุเข้ารอบ</p></div><div class="icon"><i class="fas fa-boxes"></i></div></div>
            </div>
            <div class="col-lg-3 col-md-4 col-6">
                <div class="small-box bg-success"><div class="inner"><h3>{{ number_format($operationKpis['delivered_count']) }}</h3><p>ส่งสำเร็จ</p></div><div class="icon"><i class="fas fa-check"></i></div></div>
            </div>
            <div class="col-lg-3 col-md-4 col-6">
                <div class="small-box bg-danger"><div class="inner"><h3>{{ number_format($operationKpis['failed_count']) }}</h3><p>ส่งไม่สำเร็จ</p></div><div class="icon"><i class="fas fa-times"></i></div></div>
            </div>
            <div class="col-lg-3 col-md-4 col-6">
                <div class="small-box bg-warning"><div class="inner"><h3>{{ number_format($operationKpis['returned_count']) }}</h3><p>ตีกลับ</p></div><div class="icon"><i class="fas fa-undo"></i></div></div>
            </div>
            <div class="col-lg-3 col-md-4 col-6">
                <div class="small-box bg-secondary"><div class="inner"><h3>{{ number_format($operationKpis['waiting_transit_count']) }}</h3><p>รอ/กำลังจัดส่ง</p></div><div class="icon"><i class="fas fa-clock"></i></div></div>
            </div>
            <div class="col-lg-3 col-md-4 col-6">
                <div class="small-box bg-teal"><div class="inner"><h3>{{ number_format($operationKpis['remaining_cod_amount'], 2) }}</h3><p>ยอด COD คงเหลือ</p></div><div class="icon"><i class="fas fa-wallet"></i></div></div>
            </div>
            <div class="col-lg-3 col-md-4 col-6">
                <div class="small-box bg-dark"><div class="inner"><h3>{{ number_format($operationKpis['delivery_success_rate'], 2) }}%</h3><p>อัตราส่งสำเร็จ</p></div><div class="icon"><i class="fas fa-percentage"></i></div></div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">สถานะจัดส่ง</h3></div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-bordered mb-0">
                            <tbody>
                                @foreach($deliveryStatusLabels as $status => $label)
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td class="text-right">{{ number_format($deliveryBreakdown[$status] ?? 0) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">สรุป COD</h3></div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-bordered mb-0">
                            <tbody>
                                <tr><td>ยอด COD รวม</td><td class="text-right">{{ number_format($codSummary['total_cod_amount'], 2) }}</td></tr>
                                <tr><td>ยอดเก็บแล้ว</td><td class="text-right">{{ number_format($codSummary['collected_amount'], 2) }}</td></tr>
                                <tr><td>ยอด COD คงเหลือ</td><td class="text-right">{{ number_format($codSummary['remaining_cod_amount'], 2) }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">รอบขนส่งตามสถานะ</h3></div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-bordered mb-0">
                            <tbody>
                                @foreach($tripStatusLabels as $status => $label)
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td class="text-right">{{ number_format($tripsByStatus[$status] ?? 0) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">รอบขนส่งล่าสุด</h3></div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-bordered table-sm mb-0">
                    <thead>
                        <tr>
                            <th>วันที่</th>
                            <th>รหัสรอบ</th>
                            <th>พนักงานขับรถ</th>
                            <th>ทะเบียนรถ</th>
                            <th>พัสดุ</th>
                            <th>ส่งสำเร็จ/ไม่สำเร็จ/คงเหลือ</th>
                            <th class="text-right">เก็บ COD</th>
                            <th>สถานะ</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTrips as $trip)
                            @php
                                $delivered = $trip->tripItems->where('delivery_status', \App\Models\TripItem::DELIVERY_STATUS_DELIVERED)->count();
                                $failed = $trip->tripItems->where('delivery_status', \App\Models\TripItem::DELIVERY_STATUS_FAILED)->count();
                                $returned = $trip->tripItems->where('delivery_status', \App\Models\TripItem::DELIVERY_STATUS_RETURNED)->count();
                                $remaining = max(0, $trip->tripItems->count() - $delivered - $failed - $returned);
                            @endphp
                            <tr>
                                <td>{{ optional($trip->trip_date)->format('Y-m-d') }}</td>
                                <td>{{ $trip->code }}</td>
                                <td>{{ $trip->driver_name ?: '-' }}</td>
                                <td>{{ $trip->car_id ?: '-' }}</td>
                                <td>{{ number_format($trip->tripItems->count()) }}</td>
                                <td>{{ number_format($delivered) }} / {{ number_format($failed) }} / {{ number_format($remaining) }}</td>
                                <td class="text-right">{{ number_format($trip->collected_amount, 2) }}</td>
                                <td><span class="badge badge-info">{{ $trip->status_label }}</span></td>
                                <td class="text-right"><a href="{{ route('admin.trips.show', $trip) }}" class="btn bg-primary btn-xs"><i class="fas fa-eye"></i> ดู</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted">ไม่พบรอบขนส่งในช่วงวันที่เลือก</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card card-default">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-sm text-left">
                                <h3 class="card-title">รายงาน</h3>

                            </div>
                            <div class="col-sm-6 text-right">
                                <form action="{{route('admin.dashboard')}}" method="GET"  id="generate_report">
                                    @csrf
                                    @php
                                        $legacySelected = $selected[0] ?? [];
                                    @endphp
                                <div class="col-sm-12 text-right">
                                    <div class="row">

                                        <div class="form-group  col-sm-6">

                                        <label for="select_province" class="sr-only">จังหวัด</label>
                                        <select class="form-control" id="select_province" name="select_province">
                                            <option value="">เลือกจังหวัด</option>
                                            @foreach ($province as $item)
                                            <option {{ $item['id'] == Arr::get($legacySelected, 'select_province') ? 'selected' : '' }} value="{{$item['id']}}">{{$item['name_th']}}</option>

                                            @endforeach
                                        </select>
                                        </div>

                                        <div class="form-group  col-sm-6">
                                            <div class="input-group">
                                                <label for="select_date" class="sr-only">วันที่รายงาน</label>
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="far fa-calendar-alt"></i>
                                                    </span>
                                                </div>
                                                <input type="hidden" name="db_date" value="{{ Arr::get($legacySelected, 'db_date', \Carbon\Carbon::now()->format('Y-m-d')) }}">
                                                <input type="text" name="select_date" class="form-control float-right" id="select_date" value="{{ Arr::get($legacySelected, 'select_date', '') }}" placeholder="เลือกวัน">

                                                <button class="btn bg-info ml-sm-2" id="view_report">
                                                    <i class="far fa-chart-bar"></i> ดูรายงาน
                                                </button>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                </form>
                            </div>
                        </div>
                    </div>

                        <div class="card-body p-0">
                            <div class="table-responsive ta-table-panel">
                                <table class="table table-striped table-bordered  dataTable dtr-inline " id="order_table">
                                    <thead>
                                        <tr>

                                            <th>วันที่</th>
                                            <th>รหัสพัสดุ</th>
                                            <th>ข้อมูลพัสดุ</th>
                                            <th>ชื่อผู้ฝาก</th>
                                            <th>ชื่อผู้รับ</th>
                                            <th>จังหวัดจัดส่ง</th>

                                            <th>จำนวนเงิน</th>
                                            <th>รูปแบบการชำระเงิน</th>
                                            <th>รูปแบบการจัดส่ง</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @php
                                            $immediately_total=0;
                                            $on_delivery_total=0;
                                        @endphp
                                        @foreach ($dataTable as $key=> $value)
                                        @php

                                            if($value['payment_type_id']=='immediately'){
                                                $immediately_total+=$value["parcel_pice"];
                                            }else{
                                                $on_delivery_total+=$value["parcel_pice"];
                                            }
                                        @endphp
                                        <tr>

                                            <td>{{$value["created_at"]}}</td>
                                            <td>{{$value["parcel_code"]}}</td>
                                            <td>{{$value["parcel_description"]}}</td>
                                            <td>{{$value["customer_name"]}}</td>
                                            <td>{{$value["receive_name"]}}</td>
                                            <td>{{$value["province_name"]}}</td>
                                            <td class="text-right">{{number_format($value["parcel_pice"],2)}}</td>
                                            <td>{{$value["payment_type"]}}</td>
                                            <td>{{$value["parcel_pickup_type"]}}</td>

                                        </tr>
                                        @if ($value == end($dataTable))
                                        <tr class="display-none" >
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td class="text-right">{{number_format($immediately_total,2)}}</td>
                                            <th>
                                               รวมจ่ายทันที
                                            </th>
                                            <td></td>
                                            <td></td>

                                        </tr>
                                        <tr class="display-none" >
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>

                                            <td></td>
                                            <td></td>
                                            <td class="text-right text-underline ">{{number_format( $on_delivery_total,2)}}</td>
                                            <th>
                                               รวมเก็บเงินปลายทาง
                                            </th>
                                            <td></td>
                                            <td></td>

                                        </tr>
                                        @endif
                                        @endforeach
                                    </tbody>


                                </table>
                            </div>

                        </div>
                </div>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection

@push('page_scripts')
    @include('admin.dashboard.dashboard-script')
@endpush()
