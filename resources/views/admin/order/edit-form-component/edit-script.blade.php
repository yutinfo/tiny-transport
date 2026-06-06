<script>
    let receiver =1;
</script>
@include("admin.order.edit-form-component._function-script")
<script>

$(function() {

    // $(window).bind("beforeunload", function(){
    //     return confirm("คุณทำการบันทึกข้อมูลแล้วหรือยัง");
    // });

	$.ajax({
			url: "{{route('api.province.index')}}",
			method: 'GET',
			dataType: 'JSON',
			contentType: false,
			cache: false,
			processData: false,
			success: function(response) {
				let datas = response.data;
				$('#sender_province, .receive_province').append([
					` <option value="">เลือก</option>`
				]);
				datas.forEach(e => {
					$('#sender_province, .receive_province').append([
						` <option data-id="${e.id}" value="${e.name_th}">${e.name_th}</option>`
					]);
				});
			}
		})
		.done(function(data) {
			$('#sender_province, .receive_province').select2();
		});

	$('#sender_province').on('change', function() {
		$("#sender_amphure #sender_district").html("");
		const province_text = $(this).find(":selected").html();
		const province_id = $(this).find(":selected").data('id');
		if (!province_id) {
			$("#sender_amphure #sender_district").html("");
			return false;
		}
		openSenderSubData(province_id, 'amphure')
	});
	$('#sender_amphure').on('change', function() {
		const amphure_text = $(this).find(":selected").html();
		const amphure_id = $(this).find(":selected").data('id');
		if (!amphure_id) {
			$("#sender_district").html("");
			return false;
		}
		openSenderSubData(amphure_id, 'district')
	});
	$('#sender_district').on('change', function() {
		const district_text = $(this).find(":selected").html();
		const district_id = $(this).find(":selected").data('id');
        const sender_zip_code = $(this).find(":selected").data('zipcode');
        $('#sender_zip_code').val(sender_zip_code);
	});

	$('.receive_province').on('change', function() {
		$(".receive_amphure .receive_district").html("");
		const province_text = $(this).find(":selected").html();
		const province_id = $(this).find(":selected").data('id');
		if (!province_id) {
			$(".receive_amphure .receive_district").html("");
			return false;
		}
        let dataId = $('.receive_province').data('id');
        $('.receive_province'+dataId).val(province_id);
		openReceiveSubData(province_id, 'amphure')
	});
	$('.receive_amphure').on('change', function() {
		const amphure_text = $(this).find(":selected").html();
		const amphure_id = $(this).find(":selected").data('id');
		if (!amphure_id) {
			$(".receive_district").html("");
			return false;
		}
		openReceiveSubData(amphure_id, 'district')
	});
	$('.receive_district').on('change', function() {
		const district_text = $(this).find(":selected").html();
		const district_id = $(this).find(":selected").data('id');
        const receive_zip_code = $(this).find(":selected").data('zipcode');
        const parentId = $(this).find(":selected").parents().data('item-id');
        $('input[name="receive_zip_code['+parentId+'][]"]').val(receive_zip_code);

	});


});





</script>
