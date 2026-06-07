@extends('layouts.app')

@section('content')
<div class="container-fluid ta-page-shell">
    <section class="ta-page-header-card">
        <div class="card-body">
            <div class="ta-page-header-row">
                <div>
                    <span class="ta-page-kicker"><i class="fas fa-address-book" aria-hidden="true"></i> Contacts</span>
                    <h1 class="ta-page-title">ข้อมูลผู้ส่ง/ผู้รับ</h1>
                    <p class="ta-page-subtitle">ค้นหา จัดหมวด และจัดการข้อมูลผู้ติดต่อสำหรับการฝากส่งและรับพัสดุ</p>
                </div>
                <div class="ta-page-actions">
                    <a class="btn bg-success" href="{{ route('admin.contacts.create') }}">
                        <i class="fas fa-plus"></i> เพิ่มรายการ
                    </a>
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
    </section>

    <section class="card ta-toolbar-card">
        <div class="card-body">
            <form action="{{ route('admin.contacts.index') }}" method="GET">
                <div class="ta-toolbar-grid">
                    <div class="ta-col-span-5">
                        <label for="contact_keyword">ค้นหา</label>
                        <input type="text" name="keyword" id="contact_keyword" value="{{ Arr::get($selected, 'keyword') }}" class="form-control" placeholder="ชื่อ หรือ เบอร์โทรศัพท์">
                    </div>
                    <div class="ta-col-span-4">
                        <label for="contact_filter_type">ประเภท</label>
                        <select name="type" id="contact_filter_type" class="form-control">
                            <option value="">ทั้งหมด</option>
                            @foreach ($typeLabels as $type => $label)
                                <option value="{{ $type }}" {{ Arr::get($selected, 'type') == $type ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ta-col-span-3 ta-toolbar-actions">
                        <button type="submit" class="btn bg-info">
                            <i class="fas fa-search"></i> ค้นหา
                        </button>
                        <a href="{{ route('admin.contacts.index') }}" class="btn btn-default">
                            <i class="fas fa-redo"></i> ล้าง
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="card ta-table-card">
        <div class="card-header">
            <div>
                <h3 class="ta-section-title">รายการข้อมูลผู้ส่ง/ผู้รับ</h3>
                <p class="ta-section-subtitle">แสดงข้อมูลที่อยู่ เบอร์โทรศัพท์ และประเภทผู้ติดต่อพร้อมตัวเลือกแก้ไข</p>
            </div>
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
                            <td>{{ $data->firstItem() + $key }}.</td>
                            <td><span class="badge badge-secondary">{{ $value->type_label }}</span></td>
                            <td>{{ $value->name }}</td>
                            <td>{{ $value->mobile }}</td>
                            <td>
                                {{ $value->address }}
                                @if($value->district_name || $value->amphure_name || $value->province_name || $value->zip_code)
                                    <small class="d-block text-muted">
                                        {{ $value->district_name }} {{ $value->amphure_name }} {{ $value->province_name }} {{ $value->zip_code }}
                                    </small>
                                @endif
                            </td>
                            <td>{{ $value->province_name }}</td>
                            <td class="text-right">
                                <div class="ta-table-actions">
                                    <a href="{{ route('admin.contacts.edit', $value->id) }}" class="btn bg-info btn-xs">
                                        <i class="fas fa-edit"></i> แก้ไข
                                    </a>
                                    <form action="{{ route('admin.contacts.destroy', $value->id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบข้อมูลนี้?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn bg-danger btn-xs">
                                            <i class="fas fa-trash-alt"></i> ลบ
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="ta-empty-state">
                                    <div class="ta-empty-state__icon"><i class="fas fa-address-book"></i></div>
                                    <div>ไม่พบข้อมูล</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $data->links() }}
        </div>
    </section>
</div>
@endsection

@push('page_scripts')
@if(session()->has('success'))
<script>
    $(function() {
        $(".msg-alert-success-show-text").html("").append("ดำเนินการสำเร็จ");
        $(".msg-alert-success").show("slow");
    })
</script>
@endif
@endpush
