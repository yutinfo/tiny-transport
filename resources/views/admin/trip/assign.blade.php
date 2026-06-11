@extends('layouts.app')

@section('third_party_stylesheets')
<link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
@endsection

@section('third_party_scripts')
<script src="/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
@endsection

@section('content')
<div class="container-fluid">
    <section class="content-header">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="font-weight-bold mb-0">เพิ่มพัสดุเข้ารอบ {{ $data->code }}</h5>
                        <small class="text-muted">{{ optional($data->trip_date)->format('Y-m-d') }} | {{ $data->status_label }}</small>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('admin.trips.show', $data) }}" class="btn bg-secondary"><i class="fas fa-arrow-left"></i> กลับรายละเอียด</a>
                    </div>
                </div>
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
            <div class="card-header">
                <h3 class="card-title">ค้นหาพัสดุที่รอจัดส่ง</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2"><div class="form-group"><label>วันที่เริ่ม</label><input type="date" id="filter_date_from" value="{{ $selected['date_from'] ?? '' }}" class="form-control"></div></div>
                    <div class="col-md-2"><div class="form-group"><label>วันที่สิ้นสุด</label><input type="date" id="filter_date_to" value="{{ $selected['date_to'] ?? '' }}" class="form-control"></div></div>
                    <div class="col-md-2"><div class="form-group"><label>จังหวัด</label><input type="text" id="filter_province_name" value="{{ $selected['province_name'] ?? '' }}" class="form-control"></div></div>
                    <div class="col-md-2"><div class="form-group"><label>อำเภอ</label><input type="text" id="filter_amphures_name" value="{{ $selected['amphures_name'] ?? '' }}" class="form-control"></div></div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>ชำระเงิน</label>
                            <select id="filter_payment_type" class="form-control">
                                <option value="">ทั้งหมด</option>
                                <option value="immediately" {{ ($selected['payment_type'] ?? '') === 'immediately' ? 'selected' : '' }}>จ่ายทันที</option>
                                <option value="on_delivery" {{ ($selected['payment_type'] ?? '') === 'on_delivery' ? 'selected' : '' }}>เก็บปลายทาง</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>รูปแบบจัดส่ง</label>
                            <select id="filter_parcel_pickup_type" class="form-control">
                                <option value="">ทั้งหมด</option>
                                <option value="pickup" {{ ($selected['parcel_pickup_type'] ?? '') === 'pickup' ? 'selected' : '' }}>รับเอง</option>
                                <option value="delivery" {{ ($selected['parcel_pickup_type'] ?? '') === 'delivery' ? 'selected' : '' }}>จัดส่ง</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-8"><div class="form-group mb-0"><label>ค้นหา</label><input type="text" id="filter_keyword" value="{{ $selected['keyword'] ?? '' }}" class="form-control" placeholder="รหัสพัสดุ ผู้รับ เบอร์โทร หรือรหัสออเดอร์"></div></div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button" id="apply_filters" class="btn bg-info mr-2"><i class="fas fa-search"></i> ค้นหา</button>
                        <button type="button" id="reset_filters" class="btn bg-secondary"><i class="fas fa-redo"></i> ล้าง</button>
                    </div>
                </div>
            </div>
        </div>

        <form id="assign_form" action="{{ route('admin.trips.assign-items', $data) }}" method="POST">
            @csrf
            <div id="selected_ids_inputs"></div>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">รายการพัสดุที่เลือกได้</h3>
                    <div class="card-tools">
                        <span class="mr-2">เลือก <strong id="selected_count">0</strong> รายการ</span>
                        <button type="submit" class="btn bg-success btn-sm"><i class="fas fa-plus"></i> เพิ่มพัสดุที่เลือก</button>
                    </div>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-striped table-bordered table-sm" id="assign_table" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width:30px"><input type="checkbox" id="select_all_page"></th>
                                <th>รหัสพัสดุ</th>
                                <th>ออเดอร์</th>
                                <th>ผู้ฝาก</th>
                                <th>ผู้รับ</th>
                                <th>เบอร์ผู้รับ</th>
                                <th>ปลายทาง</th>
                                <th>ชำระเงิน</th>
                                <th>รูปแบบ</th>
                                <th class="text-right">ราคา</th>
                                <th>วันที่สร้าง</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </form>
    </section>
</div>
@endsection

@push('page_scripts')
<script>
$(function () {
    // Track selected ids across pages so a checkbox on page 1 survives paging to page 2.
    const selected = new Set();

    function refreshCount() {
        $('#selected_count').text(selected.size);
    }

    function filterPayload(d) {
        d.date_from = $('#filter_date_from').val();
        d.date_to = $('#filter_date_to').val();
        d.province_name = $('#filter_province_name').val();
        d.amphures_name = $('#filter_amphures_name').val();
        d.payment_type = $('#filter_payment_type').val();
        d.parcel_pickup_type = $('#filter_parcel_pickup_type').val();
        d.keyword = $('#filter_keyword').val();
    }

    const table = $('#assign_table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        order: [[1, 'asc']],
        ajax: {
            url: @json(route('admin.trips.assign.data', $data)),
            data: filterPayload
        },
        columns: [
            { data: 'select', orderable: false, searchable: false },
            { data: 'parcel_code' },
            { data: 'order_code', orderable: false, searchable: false },
            { data: 'customer_name', orderable: false, searchable: false },
            { data: 'receive_name', orderable: false },
            { data: 'receive_mobile', orderable: false },
            { data: 'destination', orderable: false, searchable: false },
            { data: 'payment_type', orderable: false, searchable: false },
            { data: 'parcel_pickup_type', orderable: false, searchable: false },
            { data: 'parcel_pice', className: 'text-right', searchable: false },
            { data: 'created_at', searchable: false }
        ],
        language: @include('admin.partials._datatables-th'),
        drawCallback: function () {
            // Re-check boxes whose ids are in the selection set after each draw.
            $('#assign_table tbody .row-select').each(function () {
                this.checked = selected.has(this.value);
            });
            $('#select_all_page').prop('checked', false);
        }
    });

    $('#assign_table tbody').on('change', '.row-select', function () {
        if (this.checked) {
            selected.add(this.value);
        } else {
            selected.delete(this.value);
        }
        refreshCount();
    });

    $('#select_all_page').on('change', function () {
        const checked = this.checked;
        $('#assign_table tbody .row-select').each(function () {
            this.checked = checked;
            if (checked) {
                selected.add(this.value);
            } else {
                selected.delete(this.value);
            }
        });
        refreshCount();
    });

    $('#apply_filters').on('click', function () {
        table.ajax.reload();
    });

    $('#filter_keyword').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            table.ajax.reload();
        }
    });

    $('#reset_filters').on('click', function () {
        $('#filter_date_from, #filter_date_to, #filter_province_name, #filter_amphures_name, #filter_keyword').val('');
        $('#filter_payment_type, #filter_parcel_pickup_type').val('');
        table.ajax.reload();
    });

    // On submit, materialise the selection set into hidden order_receive_ids[] inputs.
    $('#assign_form').on('submit', function () {
        const container = $('#selected_ids_inputs').empty();
        selected.forEach(function (id) {
            container.append('<input type="hidden" name="order_receive_ids[]" value="' + id + '">');
        });
    });
});
</script>
@endpush
