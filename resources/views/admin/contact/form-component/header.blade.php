<section class="content-header">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="d-flex flex-row">
                            <h5 class="font-weight-bold">
                                {{$title}}
                            </h5>
                            <ol class="breadcrumb ">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"> <small> หน้าหลัก</small></a></li>
                                <li class="breadcrumb-item "> <small> ข้อมูลผู้ส่ง/ผู้รับ</small></li>
                                <li class="breadcrumb-item active"> <small> {{$mode}}</small></li>
                            </ol>
                        </div>
                    </div>
                    <div class="col-sm-6 text-right">
                        <button type="submit" class="btn bg-success">
                            <i class="fas fa-save"></i> บันทึก
                        </button>
                        <a class="btn bg-danger" href="{{route('admin.contacts.index')}}">
                            <i class="fas fa-trash-alt"></i> ยกเลิก
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
