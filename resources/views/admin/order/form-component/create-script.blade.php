<script>
    let receiver =1;
</script>
@include("admin.order.form-component._function-script")
<script>

$(function() {
    clearStorage();


	$.ajax({
			url: "{{route('api.province.index')}}",
			method: 'GET',
			dataType: 'JSON',
			contentType: false,
			cache: false,
			processData: false,
			success: function(response) {
				let datas = response.data;
				$('#sender_province,#receive_province').append([
					` <option value="">เลือก</option>`
				]);
				datas.forEach(e => {
					$('#sender_province,#receive_province').append([
						` <option value="${e.id}">${e.name_th}</option>`
					]);
				});
			}
		})
		.done(function(data) {
			$('#sender_province,#receive_province').select2();
		});

		$('#sender_province').on('change', function() {
			$("#sender_amphure ,#sender_district").html("");
            $("#sender_zip_code").val("");
			const province_text = $(this).find(":selected").html();
			const province_id = $(this).find(":selected").val();
		if (!province_id) {
			$("#sender_amphure ,#sender_district").html("");
			return false;
		}
		openSenderSubData(province_id, 'amphure')
	});
		$('#sender_amphure').on('change', function() {
            $("#sender_district").html("");
            $("#sender_zip_code").val("");
			const amphure_text = $(this).find(":selected").html();
			const amphure_id = $(this).find(":selected").val();
		if (!amphure_id) {
			$("#sender_district").html("");
			return false;
		}
		openSenderSubData(amphure_id, 'district')
	});
		$('#sender_district').on('change', function() {
			const district_text = $(this).find(":selected").html();
			const district_id = $(this).find(":selected").val();
	        const sender_zip_code = $(this).find(":selected").data('zipcode') || "";
	        $('#sender_zip_code').val(sender_zip_code);
		});

		$('#receive_province').on('change', function() {
			$("#receive_amphure ,#receive_district").html("");
            $("#receive_zip_code").val("");
			const province_text = $(this).find(":selected").html();
			const province_id = $(this).find(":selected").val();
		if (!province_id) {
			$("#receive_amphure ,#receive_district").html("");
			return false;
		}
		openReceiveSubData(province_id, 'amphure')
	});
		$('#receive_amphure').on('change', function() {
            $("#receive_district").html("");
            $("#receive_zip_code").val("");
			const amphure_text = $(this).find(":selected").html();
			const amphure_id = $(this).find(":selected").val();
		if (!amphure_id) {
			$("#receive_district").html("");
			return false;
		}
		openReceiveSubData(amphure_id, 'district')
	});
		$('#receive_district').on('change', function() {
			const district_text = $(this).find(":selected").html();
			const district_id = $(this).find(":selected").val();
	        const receive_zip_code = $(this).find(":selected").data('zipcode') || "";
	        $('#receive_zip_code').val(receive_zip_code);

		});

    $('#pickup_type').on('change', function() {
       if(!$('#pickup_type').is(':checked')){
        $('.varidate-receive-address').show();
	        }else{
	        $('.varidate-receive-address').hide();
	       }
	});

        $('#order_create').on('input change', 'input, select, textarea', function() {
            $(this).removeClass('is-invalid').attr('aria-invalid', 'false');
            $(this).next('.js-validation-feedback').remove();
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).next('.select2').find('.select2-selection').removeClass('is-invalid');
                $(this).next('.select2').next('.js-validation-feedback').remove();
            }
        });

        bindContactLookup();

	});

$(function() {
	$("#new_receiver").click(function(e) {
        $(".btn-save,#new_receiver").prop('disabled',true)
		$(".msg-alert-danger").hide();
		const orderCreate = $("#order_create").serializeArray();
			if (receiverValidator(orderCreate)) {
	            createReceiversDataTable(orderCreate)
	            showReceiversDataTable()
                $(".btn-save,#new_receiver").prop('disabled',false)
			}

		});
});

$(function() {
	$("#order_create").on('submit', function(e){
        e.preventDefault();
        $(".btn-save,#new_receiver").prop('disabled',true)

        $(".msg-alert-danger").hide("slow");
		const orderCreate = $("#order_create").serializeArray();
        // receiverValidator(orderCreate)
		if (orderValidator(orderCreate)) {
           saveForm($("#order_create").serialize());

		}

	});
});




</script>
