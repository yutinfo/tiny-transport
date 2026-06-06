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
                                        ข้อมูลผู้ส่ง/ผู้รับ &nbsp;
                                    </h5>
                                    | &nbsp; &nbsp;
                                    <ol class="breadcrumb ">
                                        <li class="breadcrumb-item"><a href="#"> <small> หน้าหลัก</small></a></li>
                                        <li class="breadcrumb-item active"> <small> ข้อมูลผู้ส่ง/ผู้รับ</small></li>
                                    </ol>
                                </div>
                            </div>
                            <div class="col-sm-6 text-right">
                                <a class="btn  bg-success" href="{{route('admin.contacts.create')}}">
                                    <i class="fas fa-plus"></i> เพิ่มรายการ
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    @include('layouts.alert-message')
                </div>
            </div>

            <div class="card">
                <form action="{{route('admin.contacts.index')}}" method="GET">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group mb-md-0">
                                    <label>ค้นหา</label>
                                    <input type="text" name="keyword" value="{{Arr::get($selected, 'keyword')}}" class="form-control" placeholder="ชื่อ หรือ เบอร์โทรศัพท์">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-md-0">
                                    <label>ประเภท</label>
                                    <select name="type" class="form-control">
                                        <option value="">ทั้งหมด</option>
                                        @foreach ($typeLabels as $type => $label)
                                            <option value="{{$type}}" {{Arr::get($selected, 'type') == $type ? 'selected' : ''}}>{{$label}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn bg-info mr-2">
                                    <i class="fas fa-search"></i> ค้นหา
                                </button>
                                <a href="{{route('admin.contacts.index')}}" class="btn bg-secondary">
                                    <i class="fas fa-redo"></i> ล้าง
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">รายการข้อมูลผู้ส่ง/ผู้รับ</h3>
                </div>

                <div class="card-body p-0 table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>ประเภท</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>เบอร์โทรศัพท์</th>
                                <th>ที่อยู่</th>
                                <th>จังหวัด</th>
                                <th style="width: 14%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $key => $value)
                                <tr>
                                    <td>{{$data->firstItem() + $key}}.</td>
                                    <td>{{$value->type_label}}</td>
                                    <td>{{$value->name}}</td>
                                    <td>{{$value->mobile}}</td>
                                    <td>
                                        {{$value->address}}
                                        @if($value->district_name || $value->amphure_name || $value->province_name || $value->zip_code)
                                            <small class="d-block text-muted">
                                                {{$value->district_name}} {{$value->amphure_name}} {{$value->province_name}} {{$value->zip_code}}
                                            </small>
                                        @endif
                                    </td>
                                    <td>{{$value->province_name}}</td>
                                    <td class="text-right">
                                        <a href="{{route('admin.contacts.edit', $value->id)}}" class="btn bg-info btn-xs">
                                            <i class="fas fa-edit"></i> แก้ไข
                                        </a>
                                        <form action="{{route('admin.contacts.destroy', $value->id)}}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบข้อมูลนี้?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn bg-danger btn-xs">
                                                <i class="fas fa-trash-alt"></i> ลบ
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">ไม่พบข้อมูล</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{$data->links()}}
                </div>
            </div>
        </section>
    </div>
@endsection

@push('page_scripts')
@if(session()->has('success'))
<script>
    $(function(){
        $(".msg-alert-success-show-text").html("").append("ดำเนินการสำเร็จ");
        $(".msg-alert-success").show("slow");
    })
</script>
@endif
@endpush
