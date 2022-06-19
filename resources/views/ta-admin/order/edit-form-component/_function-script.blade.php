<script>


function openSenderSubData(params, type) {
	let url = '';
	switch (type) {
		case "amphure":
			url = "{{route('api.amphure.index')}}" + "?province_id=" + params;
			break;
		case "district":
			url = "{{route('api.district.index')}}" + "?amphure_id=" + params;
			break;

		default:
			break;
	}
	$.ajax({
			url: url,
			method: 'GET',
			dataType: 'JSON',
			contentType: false,
			cache: false,
			processData: false,
			success: function(response) {
				$('#sender_' + type).html("");
				let datas = response.data;
				$('#sender_' + type).append([
					` <option value="">เลือก</option>`
				]);
				datas.forEach(e => {
                    if(type=="district"){
                        $('#sender_' + type).append([
						    ` <option data-zipcode="${e.zip_code}" data-id="${e.id}" value="${e.name_th}">${e.name_th}</option>`
					    ]);
                    }else{
                        $('#sender_' + type).append([
						    ` <option data-id="${e.id}" value="${e.name_th}">${e.name_th}</option>`
					    ]);
                    }


				});
			}
		})
		.done(function(data) {
			$("#sender_" + type).select2();
		});
}

function openReceiveSubData(params, type) {
	let url = '';
	switch (type) {
		case "amphure":
			url = "{{route('api.amphure.index')}}" + "?province_id=" + params;
			break;
		case "district":
			url = "{{route('api.district.index')}}" + "?amphure_id=" + params;
			break;

		default:
			break;
	}
	$.ajax({
			url: url,
			method: 'GET',
			dataType: 'JSON',
			contentType: false,
			cache: false,
			processData: false,
			success: function(response) {
				$('.receive_' + type).html("");
				let datas = response.data;
				$('.receive_' + type).append([
					` <option value="">เลือก</option>`
				]);
				datas.forEach(e => {
                    if(type=="district"){
                        $('.receive_' + type).append([
						    ` <option data-zipcode="${e.zip_code}" data-id="${e.id}" value="${e.name_th}">${e.name_th}</option>`
					    ]);
                    }else{
                        $('.receive_' + type).append([
						    ` <option data-id="${e.id}" value="${e.name_th}">${e.name_th}</option>`
					    ]);
                    }

				});
			}
		})
		.done(function(data) {
			$(".receive_" + type).select2();
		});
}

</script>
