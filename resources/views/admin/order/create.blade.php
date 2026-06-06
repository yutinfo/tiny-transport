@extends('layouts.app')

@section('third_party_stylesheets')
<link rel="stylesheet" href="/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
<link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
@endsection

@section('third_party_scripts')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="/plugins/select2/js/select2.full.min.js"></script>
@endsection


@push('page_css')
    @include('admin.order.form-component.create-style')
@endpush()

@section('content')
    <div class="container-fluid">
        <form id="order_create" action="{{route('admin.orders.store')}}" method="post">
    <section class="content-header">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-sm-6">
                        <div class="d-flex flex-row">
                            <h5 class="font-weight-bold">
                                บันทึกข้อมูลออเดอร์ &nbsp;
                                </h5>
                            | &nbsp; &nbsp;
                            <ol class="breadcrumb ">
                                <li class="breadcrumb-item"><a href="#"> <small> หน้าหลัก</small></a></li>
                                <li class="breadcrumb-item "> <small> รายการจัดการออเดอร์</small></li>
                                <li class="breadcrumb-item active"> <small> สร้าง</small></li>
                            </ol>

                        </div>
                    </div>
                    <div class="col-sm-6 text-right">
                        <div class="col-sm-12 text-right">
                            <button type="button" class="btn  btn-primary  btn-responsive" id="new_receiver"> <i
                                class="fas fa-plus"></i> เพิ่มพัสดุและผู้รับ</button>
                            <button type="submit" class="btn btn-success btn-responsive btn-save" >
                                <i class="fas fa-save"></i> บันทึก
                            </button>

                            </a>
                            <a class="btn  bg-danger btn-responsive" href="{{route('admin.orders.index')}}">
                                <i class="fas fa-trash-alt"></i> ยกเลิก
                            </a>
                        </div>
                    </div>
                </div>
            </div>
                </div>
            </div><!-- /.container-fluid -->
    </section>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        @include('layouts.alert-message')
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                       @include('admin.order.form-component.sender')
                       @include('admin.order.form-component.driver')

                       @include('admin.order.form-component.receivers-table')
                    </div>
                    <div class="col-md-6">
                        @include('admin.order.form-component.receiver')
                    </div>
                </div>
                <!-- /.row -->
            </div><!-- /.container-fluid -->
        </section>

        <section class="content-header">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-sm-6">

                    </div>
                    <div class="col-sm-6 text-right">
                        <div class="col-sm-12 text-right">

                            <button type="submit" class="btn btn-success btn-save" >
                                <i class="fas fa-save"></i> บันทึก
                            </button>

                            </a>
                            <a class="btn  bg-danger" href="{{route('admin.orders.index')}}">
                                <i class="fas fa-trash-alt"></i> ยกเลิก
                            </a>
                        </div>
                    </div>
                </div>
            </div>
                </div>
            </div><!-- /.container-fluid -->
    </section>
    @csrf
</form>
    </div>
@endsection

@push('page_scripts')
    @include('admin.order.form-component.create-script')
@endpush()


