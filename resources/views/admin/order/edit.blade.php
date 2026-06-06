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
    @include('admin.order.edit-form-component.edit-style')
@endpush()

@section('content')
    <div class="container-fluid">
        <form id="order_update" action="{{route('admin.orders.update',$data->id)}}" method="POST">
            @method("PUT")
    <section class="content-header">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-sm-6">
                        <div class="d-flex flex-row">
                            <h5 class="font-weight-bold">
                               แก้ไขข้อมูลออเดอร์ &nbsp;
                                </h5>
                            | &nbsp; &nbsp;
                            <ol class="breadcrumb ">
                                <li class="breadcrumb-item"><a href="#"> <small> หน้าหลัก</small></a></li>
                                <li class="breadcrumb-item "> <small>รายการจัดการออเดอร์</small></li>
                                <li class="breadcrumb-item active"> <small> แก้ไข </small></li>
                                <li class="breadcrumb-item active"> <small> {{$data->id}} </small></li>
                            </ol>

                        </div>
                    </div>
                    <div class="col-sm-6 text-right">
                        <div class="col-sm-12 text-right">

                            <button type="submit" class="btn btn-success" >
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

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        @include('layouts.alert-message')
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                       @include('admin.order.edit-form-component.sender')
                       @include('admin.order.edit-form-component.driver')


                    </div>
                    <div class="col-md-6">
                        @foreach ($data->receivers as $data_item)
                         @include('admin.order.edit-form-component.receiver',[
                             'data_item'=>$data_item
                         ])
                        @endforeach

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

                            <button type="submit" class="btn btn-success" >
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
@if(session()->has('errors'))
<script>

    $(function(){
        $(".msg-alert-danger-show-text").html("");
            $(".msg-alert-danger-show-text").append("{{$errors}}")
			$(".msg-alert-danger").show("slow");
			$("html, body").animate({
				scrollTop: 0
			}, "fast");
    })
</script>
@endif

@if(session()->has('message'))
<script>

    $(function(){
        $(".msg-alert-success-show-text").html("");
            $(".msg-alert-success-show-text").append("เพิ่มข้อมูลสำเร็จ")
			$(".msg-alert-success").show("slow");
			$("html, body").animate({
				scrollTop: 0
			}, "fast");
            setTimeout(() => {
                window.location.href = "{{route('admin.orders.index')}}";
            }, 2000);
    })
</script>
@endif

    @include('admin.order.edit-form-component.edit-script')
@endpush()


