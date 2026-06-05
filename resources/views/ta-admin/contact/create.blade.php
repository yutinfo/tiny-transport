@extends('layouts.app')

@section('third_party_stylesheets')
<link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
@endsection

@section('third_party_scripts')
<script src="/plugins/select2/js/select2.full.min.js"></script>
@endsection

@section('content')
    <div class="container-fluid">
        <form action="{{route('ta-admin.contacts.store')}}" method="post">
            @include('ta-admin.contact.form-component.header', ['title' => 'บันทึกข้อมูลผู้ส่ง/ผู้รับ', 'mode' => 'สร้าง'])

            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            @include('layouts.alert-message')
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            @include('ta-admin.contact.form-component.form')
                        </div>
                    </div>
                </div>
            </section>
            @csrf
        </form>
    </div>
@endsection

@push('page_scripts')
    @include('ta-admin.contact.form-component.script')
@endpush
