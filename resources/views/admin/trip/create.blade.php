@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <section class="content-header">
        <div class="card">
            <div class="card-header">
                <h5 class="font-weight-bold mb-0">สร้างรอบขนส่ง</h5>
            </div>
        </div>
    </section>
    <section class="content">
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        <div class="card">
            <form action="{{ route('admin.trips.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    @include('admin.trip.form')
                </div>
                <div class="card-footer text-right">
                    <a href="{{ route('admin.trips.index') }}" class="btn bg-secondary">กลับ</a>
                    <button type="submit" class="btn bg-success"><i class="fas fa-save"></i> บันทึก</button>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection
