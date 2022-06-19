<script>
    window.history.pushState({}, document.title, window.location.pathname);
       $(function() {
    	$('#select_province').select2();
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

    	$("#order_table").DataTable({
    			"ordering": false,
    			"responsive": false,
    			"searching": false,
    			"lengthChange": true,
    			"autoWidth": true,
    			"dom": 'Bfrtip',
    			"buttons": [{
    					extend: 'csv',
                        charset: 'utf-8',
    				},{
                        extend: 'excel',
                        text: 'Excel',

                    },
    				{
    					extend: 'print',
                        charset: 'utf-8',
    				}
    			],

    		})
    		.buttons()
    		.container()
    		.appendTo('#order_table_wrapper .col-md-6:eq(0)');


// $("#view_report").click(function(e){
//     e.preventDefault();
//     const generateReport = $("#generate_report").serializeArray();
//     let province_id
//     let start_date

//     generateReport.forEach((e, index) => {
//        switch (e.name) {
//            case 'select_province':
//             province_id = e.value
//                break;
//             case 'db_date':
//             start_date = e.value
//                break;
//            default:
//                break;
//        }
//     });

//     const datasString=`?start_date=${start_date}&province_id=${province_id}`



// });
    });
</script>
