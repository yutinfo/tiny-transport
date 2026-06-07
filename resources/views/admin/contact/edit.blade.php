@extends('layouts.app')

@section('third_party_stylesheets')
<link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
@endsection

@section('third_party_scripts')
<script src="/plugins/select2/js/select2.full.min.js"></script>
@endsection

@section('content')
<div class="container-fluid ta-page-shell">
    <form action="{{ route('admin.contacts.update', $data->id) }}" method="post" class="ta-page-shell">
        @method('PUT')
        @include('admin.contact.form-component.header', ['title' => 'แก้ไขข้อมูลผู้ส่ง/ผู้รับ', 'mode' => 'แก้ไข'])

        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    @include('layouts.alert-message')
                </div>
            </div>
        </section>

        @include('admin.contact.form-component.form')

        <div class="ta-sticky-savebar">
            <div class="ta-sticky-savebar__inner">
                <div class="ta-sticky-savebar__text">
                    <strong>พร้อมอัปเดตข้อมูลผู้ติดต่อ</strong>
                    <span>การเปลี่ยนแปลงจะสะท้อนในแบบฟอร์มเลือกผู้ติดต่อทันที</span>
                </div>
                <div class="ta-page-actions">
                    <a class="btn btn-default" href="{{ route('admin.contacts.index') }}">ยกเลิก</a>
                    <button type="submit" class="btn bg-success">
                        <i class="fas fa-save"></i> บันทึก
                    </button>
                </div>
            </div>
        </div>
        @csrf
    </form>
</div>
@endsection

@push('page_scripts')
    @include('admin.contact.form-component.script')
@endpush
