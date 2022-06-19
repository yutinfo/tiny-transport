<script>
    function hendleData() {
        const orderCreateForm = $("#order_create").serializeArray();
        const localStorageKeys = Object.keys(localStorage);
        let orderCreateObj={};
        let receivers = {}

        if(localStorageKeys.length>=1){
            localStorageKeys.forEach((e,index) => {
               receivers[index]=JSON.parse(localStorage.getItem(e));

            });
        }
        orderCreateForm.forEach((e,index) => {
            orderCreateObj[e.name]=e.value??"";
        });
        orderCreateObj["receive_province_text"]= $("#receive_province :selected").val()!=""?$("#receive_province :selected").text():"";
        orderCreateObj["receive_amphure_text"]= $("#receive_amphure :selected").val()!=""?$("#receive_amphure :selected").text():"";
        orderCreateObj["receive_district_text"]= $("#receive_district :selected").val()!=""?$("#receive_district :selected").text():"";

        orderCreateObj["sender_province_text"]= $("#sender_province :selected").val()!=""?$("#sender_province :selected").text():"";
        orderCreateObj["sender_amphure_text"]= $("#sender_amphure :selected").val()!=""?$("#sender_amphure :selected").text():"";
        orderCreateObj["sender_district_text"]= $("#sender_district :selected").val()!=""?$("#sender_district :selected").text():"";

        if(Object.keys(receivers).length>0){
            const receiversObj = Object.assign({}, receivers);
            orderCreateObj['receivers']=receiversObj;
            return orderCreateObj;
        }else{
            return  orderCreateObj;
        }

    }
    function saveForm(params) {
        const datas = hendleData();
        const url = $("#order_create").attr('action');

        $.ajax({
			url: url,
			method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json',
            },
            data: datas,
			success: function(response) {

			}
		})
		.done(function(data) {
			$(".msg-alert-success-show-text").html("");
                $(".msg-alert-success-show-text").append("บันทึกข้อมูลสำเร็จ <br />")
			$(".msg-alert-success").show("slow");
			$("html, body").animate({
				scrollTop: 0
			}, "fast");
            $("#order_create")[0].reset();
            setTimeout(() => {
                $(".btn-save,#new_receiver").prop('disabled',false)
                window.location = "{{route('ta-admin.orders.index')}}";
            }, 2000);

		})
        .fail(function (jqXHR, textStatus, error) {
			$(".msg-alert-danger-show-text").html("");
            $(".msg-alert-danger-show-text").append(jqXHR.status+" "+error+" "+jqXHR.responseText.substring(1,100)+" <br />")
			$(".msg-alert-danger").show("slow");
			$("html, body").animate({
				scrollTop: 0
			}, "fast");
		})
        ;

    }
    function orderValidator(params) {

        let isPickupTypeChecked = $('#pickup_type').is(':checked');
        let isPaymentTypeChecked = false;
        const localStorageKeys = Object.keys(localStorage);
        if($("input:radio[name='payment_type']").is(":checked")){
            isPaymentTypeChecked = true
        }


		const fullValidate = [
            "sender_name",
			"sender_mobile",
			"receive_name",
			"receive_mobile",
			"receive_address",
			"receive_province",
			"receive_amphure",
			"receive_district",
			"receive_zip_code",
			"parcel_description",
			"parcel_pice",
		];
		const validateWithOutAddress = [
            "receive_province",
            "sender_name",
			"sender_mobile",
			"receive_name",
			"receive_mobile",
			"parcel_description",
			"parcel_pice",
		];
        const validateWithOutReceiver = [
            "sender_name",
			"sender_mobile",
			"receive_name",
			"receive_mobile",
			"receive_address",
			"receive_province",
			"receive_amphure",
			"receive_district",
			"receive_zip_code",
			"parcel_description",
			"parcel_pice",
		]
		let validate = [];
		params.forEach((e, index) => {
			if (e.value == "") {
                if(localStorageKeys.length>=1){
                    if ($.inArray(e.name, validateWithOutReceiver) !== -1) {
						let nameText = document.getElementsByName(e.name);
						validate.push(nameText[0].getAttribute('data-name'));
					}
                }else if (isPickupTypeChecked) {
					if ($.inArray(e.name, validateWithOutAddress) !== -1) {
						let nameText = document.getElementsByName(e.name);
						validate.push(nameText[0].getAttribute('data-name'));
					}
				} else {
					if ($.inArray(e.name, fullValidate) !== -1) {
						let nameText = document.getElementsByName(e.name);
						validate.push(nameText[0].getAttribute('data-name'));
					}
				}

			}
		});
        if(!isPaymentTypeChecked && localStorageKeys.length==0){
            validate.push($("input[name='payment_type']").data('name'));
        }

		if (validate.length > 0) {
			$(".msg-alert-danger-show-text").html("");
			validate.forEach((e,index ) => {
				$(".msg-alert-danger-show-text").append((index+1)+". "+e + " จำเป็นต้องกรอก <br />")
			});
			$(".msg-alert-danger").show("slow");
			$("html, body").animate({
				scrollTop: 0
			}, "fast");
            setTimeout(() => {
            $(".btn-save,#new_receiver").prop('disabled',false)
        }, 2000);
            return false;
		}else{

            return true;
        };
	}
    function receiverValidator(params) {
        let isPickupTypeChecked = $('#pickup_type').is(':checked');
        let isPaymentTypeChecked = false;
        if($("input:radio[name='payment_type']").is(":checked")){
            isPaymentTypeChecked = true
        }

		const fullValidate = [
			"receive_name",
			"receive_mobile",
			"receive_address",
			"receive_province",
			"receive_amphure",
			"receive_district",
			"receive_zip_code",
			"parcel_description",
			"parcel_pice",
		];
		const validateWithOutAddress = [
			"receive_name",
			"receive_mobile",
			"parcel_description",
			"parcel_pice",
		]
		let validate = [];
		params.forEach((e, index) => {
			if (e.value == "") {
				if (isPickupTypeChecked) {
					if ($.inArray(e.name, validateWithOutAddress) !== -1) {
						let nameText = document.getElementsByName(e.name);
						validate.push(nameText[0].getAttribute('data-name'));
					}
				} else {
					if ($.inArray(e.name, fullValidate) !== -1) {
						let nameText = document.getElementsByName(e.name);
						validate.push(nameText[0].getAttribute('data-name'));
					}
				}

			}
		});
        if(!isPaymentTypeChecked){
            validate.push($("input[name='payment_type']").data('name'));
        }


		if (validate.length > 0) {
			$(".msg-alert-danger-show-text").html("");
			validate.forEach(e => {
				$(".msg-alert-danger-show-text").append(e + " จำเป็นต้องกรอก <br />")
			});
			$(".msg-alert-danger").show("slow");
			$("html, body").animate({
				scrollTop: 0
			}, "fast");
            setTimeout(() => {
            $(".btn-save,#new_receiver").prop('disabled',false)
        }, 2000);
            return false;
		}else{
            setTimeout(() => {
            $(".btn-save,#new_receiver").prop('disabled',false)
        }, 2000);
            return true;
        };
	}

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
						    ` <option data-zipcode="${e.zip_code}" value="${e.id}">${e.name_th}</option>`
					    ]);
                    }else{
                        $('#sender_' + type).append([
                            ` <option value="${e.id}">${e.name_th}</option>`
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
				$('#receive_' + type).html("");
				let datas = response.data;
				$('#receive_' + type).append([
					` <option value="">เลือก</option>`
				]);

				datas.forEach(e => {
                    if(type=="district"){
                        $('#receive_' + type).append([
						    ` <option data-zipcode="${e.zip_code}" data-id="${e.id}" value="${e.id}">${e.name_th}</option>`
					    ]);
                    }else{
                        $('#receive_' + type).append([
						    ` <option value="${e.id}">${e.name_th}</option>`
					    ]);
                    }

				});
			}
		})
		.done(function(data) {
			$("#receive_" + type).select2();
		});
}

function createReceiversDataTable(params) {
    let isPickupTypeChecked = $('#pickup_type').is(':checked');
    let pickupType;
    let paymentType;
    let dataArray={};
        if($("input:radio[name='payment_type']").is(":checked")){
            paymentType = $("input:radio[name='payment_type']").val();
            dataArray['payment_type']=paymentType??"";
        }
        if(isPickupTypeChecked){
            pickupType =  $('#pickup_type').val();
            dataArray["pickup_type"]=pickupType??"";
        }

        dataArray["receive_province_text"]= $("#receive_province :selected").val()!=""?$("#receive_province :selected").text():"";
        dataArray["receive_amphure_text"]= $("#receive_amphure :selected").val()!=""?$("#receive_amphure :selected").text():"";
        dataArray["receive_district_text"]= $("#receive_district :selected").val()!=""?$("#receive_district :selected").text():"";


    params.forEach((e,index) => {
        dataArray[e.name]=e.value??"";
    });
    localStorage.setItem("receive["+receiver+"]", JSON.stringify(dataArray));
    clearFormReceiver();
    receiver++;
}
async function showReceiversDataTable() {
    const localStorageKeys = Object.keys(localStorage);
    $('#table_tbody_receivers').html("");
   await localStorageKeys.forEach((e,index) => {
        const data = localStorage.getItem(e);
        const dataObj = JSON.parse(data);
        const pickupType = enumt('payment_type',dataObj['payment_type']??"0");
         $('#table_tbody_receivers').append([
				`<tr id="tr-${index+1}">
                 <td >${index+1}</td>
                <td >${dataObj['receive_name']}</td>
                <td >${dataObj['receive_province_text']}</td>
                <td >${dataObj['parcel_description']}</td>
                <td >${dataObj['parcel_pice']} (${pickupType})</td>
                <td class="text-center"><a href="javascript:removeReceiver('${e}','tr-${index+1}')" class="text-danger" ><i class="fas fa-trash-alt"></i></a></td>
                </tr>`
		]);
    });
    $('#table_receivers').show('fast');

}
function enumt(key,value) {
    const datas={
        "payment_type":{
            "1":"จ่ายทันที",
            "2":"เก็บเงินปลายทาง",
        }
    };
    if (datas[key][value]) {
        return datas[key][value];
    }
    return "-"
}
function clearStorage() {
    const localStorageKeys = Object.keys(localStorage);
    localStorageKeys.forEach((e) => {
        localStorage.removeItem(e);
    });

}
function removeReceiver(key,id){
    localStorage.removeItem(key);
    showReceiversDataTable()
}

function clearFormReceiver() {
    $("#receive_name,#receive_mobile,#receive_address,#receive_zip_code,#parcel_description,#parcel_pice").val("");
    $("#pickup_type").prop("checked",false)
    $("#payment_type1,#payment_type2").prop("checked",false)
    $("#receive_province,#receive_amphure,#receive_district").val(null).trigger('change');
}
</script>
