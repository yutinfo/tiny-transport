@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <section class="content-header">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="d-flex flex-row">
                                    <h5 class="font-weight-bold">
                                        จัดการผู้ใช้
                                    </h5>
                                    <ol class="breadcrumb ">
                                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"> <small> หน้าหลัก</small></a></li>
                                        <li class="breadcrumb-item active"> <small> จัดการผู้ใช้</small></li>
                                    </ol>

                                </div>
                            </div>
                            <div class="col-sm-6 text-right">
                                <a class="btn  bg-success" href="{{route('admin.users.create')}}">
                                    <i class="fas fa-plus"></i> เพิ่มรายการ
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">รายการผู้ใช้งานระบบ</h3>
                </div>

                <div class="card-body p-0 table-responsive">
                    <table class="table table-striped table-bordered  ">
                        <thead>
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>วันที่ลงทะเบียน</th>
                                <th>Username</th>
                                <th>ชื่อ - นามสกุล</th>
                                <th>อีเมล</th>
                                <th>บทบาท</th>
                                <th>สถานะ</th>

                                <th style="width: 10%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $key=> $value)


                            <tr>
                                <td>{{$key+1}}.</td>
                                <td>{{thaiDateFullmonth($value["created_at"])}}</td>
                                <td>{{$value["username"]}}</td>
                                <td>{{$value["name"] ." " .$value["last_name"]}}</td>
                                <td>{{$value["email"]}}</td>
                                <td>{{$value["role_name"]}}</td>
                                <td>{{$value["status"]}}</td>
                                <td>
                                    <a href="{{route('admin.users.edit',$value["id"])}}" class="btn  bg-info btn-xs"><i class="fas fa-edit"></i> แก้ไข </a>
                            </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>



    </div>
    </section>
@endsection
