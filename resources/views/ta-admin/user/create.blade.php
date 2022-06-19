@extends('layouts.app')

@section('third_party_stylesheets')
<link rel="stylesheet" href="/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
@endsection

@section('content')
    <div class="container-fluid">
        <form action="{{route('ta-admin.users.store')}}" method="post">
    <section class="content-header">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-sm-6">
                        <div class="d-flex flex-row">
                            <h5 class="font-weight-bold">
                                บันทึกข้อมูลผู้ใช้ &nbsp;
                                </h5>
                            | &nbsp; &nbsp;
                            <ol class="breadcrumb ">
                                <li class="breadcrumb-item"><a href="#"> <small> หน้าหลัก</small></a></li>
                                <li class="breadcrumb-item "> <small> ผู้ใช้งาน</small></li>
                                <li class="breadcrumb-item active"> <small> สร้าง</small></li>
                            </ol>

                        </div>
                    </div>
                    <div class="col-sm-6 text-right">
                        <div class="col-sm-12 text-right">
                            <button type="submit" class="btn  bg-success" >
                                <i class="fas fa-save"></i> บันทึก
                            </button>
                            <a class="btn  bg-danger" href="{{route('ta-admin.users.index')}}">
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
                       @include('ta-admin.user.form-component.info')
                    </div>
                    <div class="col-md-6">
                        @include('ta-admin.user.form-component.auth')
                    </div>

                </div>
                <!-- /.row -->
            </div><!-- /.container-fluid -->
        </section>
        @csrf
    </form>
    </div>
@endsection

@push('page_scripts')
@if(count($errors)>=1)
<script>

    $(function(){
        $(".msg-alert-danger-show-text").html("");
        let error = JSON.parse(`{!!$errors!!}`);

        for (const [key, value] of Object.entries(error)) {
            $(".msg-alert-danger-show-text").append(value +"<br />")
        }

			$(".msg-alert-danger").show("slow");
			$("html, body").animate({
				scrollTop: 0
			}, "fast");
    })
</script>
@endif

@if(session()->has('success'))
<script>

    $(function(){
        $(".msg-alert-success-show-text").html("");
            $(".msg-alert-success-show-text").append("เพิ่มข้อมูลสำเร็จ")
			$(".msg-alert-success").show("slow");
			$("html, body").animate({
				scrollTop: 0
			}, "fast");
            setTimeout(() => {
                window.location.href = "{{route('ta-admin.users.index')}}";
            }, 2000);
    })
</script>
@endif


@endpush()
