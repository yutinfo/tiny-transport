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
                                <div class="col-sm-12 text-right">
                                    <div class="row">

                                        <div class="form-group  col-sm-6">

                                        <label for="select_province" class="sr-only">จังหวัด</label>
                                        <select class="form-control" id="select_province" name="select_province">
                                            <option value="">เลือกจังหวัด</option>
                                            @foreach ($province as $item)
                                            @if(count($selected[0])>1)

                                            <option {{$item['id']==$selected[0]['select_province']?'selected':""}} value="{{$item['id']}}">{{$item['name_th']}}</option>
                                            @else
                                            <option value="{{$item['id']}}">{{$item['name_th']}}</option>
                                            @endif



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
                                                @if(count($selected[0])>=1)
                                                <input type="hidden" name="db_date" value="{{$selected[0]['db_date']}}">
                                                <input type="text" name="select_date" class="form-control float-right" id="select_date" value="{{$selected[0]['select_date']}}" placeholder="เลือกวัน">
                                                @else
                                                <input type="hidden" name="db_date" value="{{\Carbon\Carbon::now()->format("Y-m-d")}}">
                                                <input type="text" name="select_date" class="form-control float-right" id="select_date" value="" placeholder="เลือกวัน">
                                                @endif

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
