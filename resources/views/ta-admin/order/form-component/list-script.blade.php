
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
			let url = "{{route('ta-admin.orderreceive.delete',999999999999)}}";
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
				data: {
					"id": id
				},
				success: function(response) {
					Swal.fire('ลบสำเร็จ!','','success')
					setTimeout(() => {
						location.reload();
					}, '1500');

				},
				error: function(xhr) {
					console.log(xhr.responseText);

				}
			});
		} else if (result.isDenied) {
			return false;
		}
	})

}
$(function() {


	$("#order_table").DataTable({
		"responsive": true,
		"lengthChange": false,
		"autoWidth": false,
		"dom": 'Bfrtip',
		"buttons": [{
				extend: 'csv',
                charset: 'utf-8',
				exportOptions: {
					columns: ':not(:last-child)',
				}
			},{
				extend: 'excel',
                text: 'Excel',
                exportOptions: {
					columns: ':not(:last-child)',
				}
			},
			{
				extend: 'print',
                charset: 'utf-8',
				exportOptions: {
					columns: ':not(:last-child)',
				}
			}
		]
	}).buttons().container().appendTo('#order_table_wrapper .col-md-6:eq(0)');

});
$(function(){
    $('input[name="select_date"]').daterangepicker({
    		singleDatePicker: true,
    		showDropdowns: true,
    		minYear: 1901,
            autoApply: true,
    		maxDate: new Date(),
    		locale: {
    			format: 'DD/MM/YYYY'
    		},
    		maxYear: parseInt(moment().format('YYYY'), 10)
    	}, function(start, end, label) {
    		$('input[name="select_date"]').val(start.format('YYYY-MM-DD'))
            $('input[name="db_date"]').val(start.format('YYYY/MM/DD'))

    	});
});
  </script>

