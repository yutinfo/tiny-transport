
<script>
    window.history.pushState({}, document.title, window.location.pathname);

    function dt(id, name) {
        Swal.fire({
            title: "คุณต้องการลบรายการของลูกค้า " + name + " หรือไม่?",
            showDenyButton: true,
            showCancelButton: false,
            confirmButtonText: 'ลบ',
            denyButtonText: `ยกเลิก`,
        }).then((result) => {
            if (result.isConfirmed) {
                let url = "{{ route('admin.orderreceive.delete', 999999999999) }}";
                url = url.replace("999999999999", id);
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: url,
                    type: 'delete',
                    dataType: "JSON",
                    data: { "id": id },
                    success: function(response) {
                        Swal.fire('ลบสำเร็จ!', '', 'success');
                        window.ordersTable && window.ordersTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                    }
                });
            } else if (result.isDenied) {
                return false;
            }
        });
    }

    $(function() {
        window.ordersTable = $("#order_table").DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            lengthChange: true,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            autoWidth: false,
            order: [[1, 'desc']],
            ajax: {
                url: @json(route('admin.orders.data')),
                data: function(d) {
                    d.db_date = $('#db_date').val();
                },
                dataSrc: function(json) {
                    if (json.summary) {
                        $('#summary-immediately').text(Number(json.summary.immediately_total).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                        $('#summary-on-delivery').text(Number(json.summary.on_delivery_total).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                    }
                    return json.data;
                }
            },
            columns: [
                { data: 'row', orderable: false, searchable: false },
                { data: 'created_at' },
                { data: 'parcel_code' },
                { data: 'parcel_description', orderable: false, searchable: false },
                { data: 'customer_name', orderable: false },
                { data: 'receive_name', orderable: false },
                { data: 'province_name', orderable: false, searchable: false },
                { data: 'parcel_pice', className: 'text-right', searchable: false },
                { data: 'payment_type', orderable: false, searchable: false },
                { data: 'parcel_pickup_type', orderable: false, searchable: false },
                { data: 'actions', orderable: false, searchable: false }
            ],
            language: @include('admin.partials._datatables-th')
        });
    });

    $(function() {
        $('input[name="select_date"]').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            minYear: 1901,
            autoApply: true,
            maxDate: new Date(),
            locale: { format: 'DD/MM/YYYY' },
            maxYear: parseInt(moment().format('YYYY'), 10)
        }, function(start, end, label) {
            $('input[name="select_date"]').val(start.format('YYYY-MM-DD'));
            $('#db_date').val(start.format('YYYY-MM-DD'));
            window.ordersTable && window.ordersTable.ajax.reload();
        });
    });
</script>
