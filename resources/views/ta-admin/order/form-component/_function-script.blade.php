<script>
    const RECEIVER_STORAGE_PREFIX = 'ta_order_create_receiver_';
    const SENDER_REQUIRED_FIELDS = [
        "sender_name",
        "sender_mobile",
    ];
    const RECEIVER_REQUIRED_FIELDS = [
        "receive_name",
        "receive_mobile",
        "parcel_description",
        "parcel_pice",
    ];
    const RECEIVER_ADDRESS_FIELDS = [
        "receive_address",
        "receive_province",
        "receive_amphure",
        "receive_district",
        "receive_zip_code",
    ];
    const RECEIVER_PAYLOAD_FIELDS = [
        ...RECEIVER_REQUIRED_FIELDS,
        ...RECEIVER_ADDRESS_FIELDS,
        "payment_type",
        "pickup_type",
    ];

    function receiverStorageKeys() {
        return Object.keys(localStorage)
            .filter((key) => key.indexOf(RECEIVER_STORAGE_PREFIX) === 0)
            .sort((first, second) => Number(first.replace(RECEIVER_STORAGE_PREFIX, '')) - Number(second.replace(RECEIVER_STORAGE_PREFIX, '')));
    }

    function hasQueuedReceivers() {
        return receiverStorageKeys().length > 0;
    }

    function formValues(params) {
        let values = {};
        params.forEach((field) => {
            values[field.name] = field.value ?? "";
        });
        return values;
    }

    function hasCurrentReceiverPayload(values) {
        return RECEIVER_PAYLOAD_FIELDS.some((fieldName) => $.trim(values[fieldName] ?? "") !== "");
    }

    function clearValidationState() {
        $(".is-invalid").removeClass("is-invalid");
        $(".select2-selection.is-invalid").removeClass("is-invalid");
        $(".js-validation-feedback").remove();
    }

    function fieldLabel(fieldName) {
        const field = $('[name="' + fieldName + '"]').first();
        return field.data('name') || fieldName;
    }

    function addFieldError(fieldName, message) {
        const field = $('[name="' + fieldName + '"]').first();
        if (!field.length) {
            return;
        }

        field.addClass('is-invalid').attr('aria-invalid', 'true');

        if (field.hasClass('select2-hidden-accessible')) {
            const select2 = field.next('.select2');
            select2.find('.select2-selection').addClass('is-invalid');
            select2.after('<div class="invalid-feedback js-validation-feedback d-block">' + message + '</div>');
            return;
        }

        field.after('<div class="invalid-feedback js-validation-feedback d-block">' + message + '</div>');
    }

    function focusFirstInvalidField() {
        const firstInvalid = $('.is-invalid').first();
        if (!firstInvalid.length) {
            return;
        }

        if (firstInvalid.hasClass('select2-selection')) {
            firstInvalid.closest('.select2').prev('select').select2('open');
            return;
        }

        firstInvalid.trigger('focus');
    }

    function showValidationSummary(messages) {
        $(".msg-alert-danger-show-text").html("");
        messages.forEach((message, index) => {
            $(".msg-alert-danger-show-text").append((index + 1) + ". " + message + " <br />");
        });
        $(".msg-alert-danger").show("slow");
        $("html, body").animate({
            scrollTop: 0
        }, "fast");
    }

    function showServerValidationErrors(errors) {
        clearValidationState();
        let messages = [];

        Object.keys(errors).forEach((fieldKey) => {
            const message = errors[fieldKey][0];
            const fieldName = fieldKey.split('.').pop();
            messages.push(message);
            addFieldError(fieldName, message);
        });

        showValidationSummary(messages);
        focusFirstInvalidField();
    }

    function validateOrderFields(params, options) {
        clearValidationState();

        const values = formValues(params);
        const isPickupTypeChecked = $('#pickup_type').is(':checked');
        const requiredFields = [];
        let messages = [];

        if (options.includeSender) {
            requiredFields.push(...SENDER_REQUIRED_FIELDS);
        }

        if (options.includeReceiver) {
            requiredFields.push(...RECEIVER_REQUIRED_FIELDS);

            if (!isPickupTypeChecked) {
                requiredFields.push(...RECEIVER_ADDRESS_FIELDS);
            }
        }

        [...new Set(requiredFields)].forEach((fieldName) => {
            if ($.trim(values[fieldName] ?? "") === "") {
                const message = fieldLabel(fieldName) + " จำเป็นต้องกรอก";
                messages.push(message);
                addFieldError(fieldName, message);
            }
        });

        ["sender_mobile", "receive_mobile"].forEach((fieldName) => {
            if ($.trim(values[fieldName] ?? "") !== "" && !/^\d{9,10}$/.test(values[fieldName])) {
                const message = fieldLabel(fieldName) + " รูปแบบไม่ถูกต้อง";
                messages.push(message);
                addFieldError(fieldName, message);
            }
        });

        if (options.includeReceiver && $.trim(values.parcel_pice ?? "") !== "" && Number(values.parcel_pice) <= 0) {
            const message = fieldLabel("parcel_pice") + " ต้องมากกว่า 0";
            messages.push(message);
            addFieldError("parcel_pice", message);
        }

        if (options.includeReceiver && !isPickupTypeChecked && $.trim(values.receive_zip_code ?? "") !== "" && !/^\d{5}$/.test(values.receive_zip_code)) {
            const message = fieldLabel("receive_zip_code") + " ต้องเป็นตัวเลข 5 หลัก";
            messages.push(message);
            addFieldError("receive_zip_code", message);
        }

        if (options.requirePayment && !$("input:radio[name='payment_type']:checked").length) {
            const message = ($("input[name='payment_type']").first().data('name') || "ช่องทางการชำระเงิน") + " จำเป็นต้องเลือก";
            messages.push(message);
        }

        if (messages.length > 0) {
            showValidationSummary(messages);
            focusFirstInvalidField();
            $(".btn-save,#new_receiver").prop('disabled', false);
            return false;
        }

        return true;
    }

	    function hendleData() {
	        const orderCreateForm = $("#order_create").serializeArray();
	        const localStorageKeys = receiverStorageKeys();
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
                if (jqXHR.status === 422 && jqXHR.responseJSON && jqXHR.responseJSON.errors) {
                    showServerValidationErrors(jqXHR.responseJSON.errors);
                    $(".btn-save,#new_receiver").prop('disabled', false);
                    return;
                }

				$(".msg-alert-danger-show-text").html("");
	            $(".msg-alert-danger-show-text").append(jqXHR.status+" "+error+" "+jqXHR.responseText.substring(1,100)+" <br />")
				$(".msg-alert-danger").show("slow");
				$("html, body").animate({
					scrollTop: 0
				}, "fast");
                $(".btn-save,#new_receiver").prop('disabled', false);
			})
        ;

    }
    function legacyOrderValidator(params) {

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
    function legacyReceiverValidator(params) {
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

function normalizeMobile(value) {
    return String(value || '').replace(/\D/g, '');
}

function contactTypeLabel(type) {
    const labels = {
        sender: 'ผู้ส่ง',
        receiver: 'ผู้รับ',
        both: 'ผู้ส่ง/ผู้รับ',
    };

    return labels[type] || 'ผู้ติดต่อ';
}

function contactMobileField(contactType) {
    return $('[name="' + (contactType === 'sender' ? 'sender_mobile' : 'receive_mobile') + '"]');
}

function contactLookupFeedback(contactType) {
    const field = contactMobileField(contactType);
    const formGroup = field.closest('.form-group');
    let feedback = formGroup.find('.js-contact-lookup-feedback');

    if (!feedback.length) {
        formGroup.css('position', 'relative');
        feedback = $('<small class="form-text js-contact-lookup-feedback"></small>');
        formGroup.append(feedback);
    }

    return feedback;
}

function contactSuggestionList(contactType) {
    const field = contactMobileField(contactType);
    const formGroup = field.closest('.form-group');
    let list = formGroup.find('.js-contact-suggestion-list');

    if (!list.length) {
        formGroup.css('position', 'relative');
        list = $('<div class="list-group shadow-sm js-contact-suggestion-list" style="display:none; position:absolute; left:0; right:0; z-index:1050; max-height:260px; overflow:auto;"></div>');
        formGroup.append(list);
    }

    return list;
}

function hideContactSuggestions(contactType) {
    contactSuggestionList(contactType).hide().html('');
}

function escapeHtml(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function renderContactSuggestions(contactType, contacts, term) {
    const list = contactSuggestionList(contactType);
    list.html('');

    if (!contacts.length) {
        hideContactSuggestions(contactType);
        setContactLookupState(contactType, 'missing', 'ไม่พบข้อมูลที่ขึ้นต้นด้วย ' + term);
        return;
    }

    contacts.forEach(function(contact) {
        const address = [contact.district_name, contact.amphure_name, contact.province_name, contact.zip_code]
            .filter(Boolean)
            .join(' ');
        const item = $(
            '<button type="button" class="list-group-item list-group-item-action text-left">' +
                '<div class="d-flex justify-content-between align-items-center">' +
                    '<strong>' + escapeHtml(contact.name) + '</strong>' +
                    '<span class="badge badge-light">' + escapeHtml(contactTypeLabel(contact.type)) + '</span>' +
                '</div>' +
                '<div class="small text-muted">' + escapeHtml(contact.mobile) + '</div>' +
                (address ? '<div class="small text-muted text-truncate">' + escapeHtml(address) + '</div>' : '') +
            '</button>'
        );

        item.on('mousedown', function(event) {
            event.preventDefault();
            fillOrderContact(contactType, contact);
            setContactLookupState(contactType, 'found', 'เลือกข้อมูลจากรายการ' + contactTypeLabel(contact.type) + ' แล้ว');
            hideContactSuggestions(contactType);
        });

        list.append(item);
    });

    list.show();
    setContactLookupState(contactType, 'found', 'พบ ' + contacts.length + ' รายการ เลือกจาก dropdown ได้เลย');
}

function setContactLookupState(contactType, state, message) {
    const field = contactMobileField(contactType);
    const feedback = contactLookupFeedback(contactType);

    field.removeClass('border-info border-success border-warning border-danger');
    feedback.removeClass('text-info text-success text-warning text-danger');

    if (!state) {
        feedback.html('');
        return;
    }

    const states = {
        searching: {
            fieldClass: 'border-info',
            textClass: 'text-info',
            icon: '<i class="fas fa-spinner fa-spin"></i>',
        },
        found: {
            fieldClass: 'border-success',
            textClass: 'text-success',
            icon: '<i class="fas fa-check-circle"></i>',
        },
        missing: {
            fieldClass: 'border-warning',
            textClass: 'text-warning',
            icon: '<i class="fas fa-search"></i>',
        },
        error: {
            fieldClass: 'border-danger',
            textClass: 'text-danger',
            icon: '<i class="fas fa-exclamation-circle"></i>',
        },
    };

    const config = states[state];
    field.addClass(config.fieldClass);
    feedback.addClass(config.textClass).html(config.icon + ' ' + message);
}

function clearContactLookupState(contactType) {
    setContactLookupState(contactType, null, '');
}

function highlightContactFields(contactType) {
    const prefix = contactType === 'sender' ? 'sender' : 'receive';
    const fieldNames = [
        contactType === 'sender' ? 'sender_name' : 'receive_name',
        contactType === 'sender' ? 'sender_address' : 'receive_address',
    ];
    const selectIds = [
        prefix + '_province',
        prefix + '_amphure',
        prefix + '_district',
    ];

    fieldNames.forEach(function(fieldName) {
        $('[name="' + fieldName + '"]').addClass('border-success');
    });
    $('#' + prefix + '_zip_code').addClass('border-success');
    selectIds.forEach(function(selectId) {
        $('#' + selectId).next('.select2').find('.select2-selection').addClass('border-success');
    });

    setTimeout(function() {
        fieldNames.forEach(function(fieldName) {
            $('[name="' + fieldName + '"]').removeClass('border-success');
        });
        $('#' + prefix + '_zip_code').removeClass('border-success');
        selectIds.forEach(function(selectId) {
            $('#' + selectId).next('.select2').find('.select2-selection').removeClass('border-success');
        });
    }, 1600);
}

function loadOrderContactSubData(prefix, parentId, type, selectedValue, callback) {
    let url = '';

    if (type === 'amphure') {
        url = "{{route('api.amphure.index')}}" + "?province_id=" + parentId;
    }

    if (type === 'district') {
        url = "{{route('api.district.index')}}" + "?amphure_id=" + parentId;
    }

    if (!url) {
        return;
    }

    $.ajax({
        url: url,
        method: 'GET',
        dataType: 'JSON',
        success: function(response) {
            const element = $('#' + prefix + '_' + type);
            element.html('<option value="">à¹€à¸¥à¸·à¸­à¸</option>');

            response.data.forEach(function(item) {
                const selected = String(selectedValue) === String(item.id) ? ' selected' : '';
                const zipCode = type === 'district' ? ' data-zipcode="' + item.zip_code + '"' : '';
                element.append('<option value="' + item.id + '"' + zipCode + selected + '>' + item.name_th + '</option>');
            });

            element.select2();

            if (callback) {
                callback();
            }
        }
    });
}

function lookupContactByMobile(contactType, mobile) {
    const normalizedMobile = normalizeMobile(mobile);

    if (!/^\d{9,10}$/.test(normalizedMobile)) {
        clearContactLookupState(contactType);
        return;
    }

    setContactLookupState(contactType, 'searching', 'กำลังค้นหาข้อมูลจากเบอร์นี้...');

    $.ajax({
        url: "{{route('api.contacts.search')}}",
        method: 'GET',
        dataType: 'JSON',
        data: {
            type: contactType,
            mobile: normalizedMobile,
        },
        success: function(response) {
            if (normalizeMobile(contactMobileField(contactType).val()) !== normalizedMobile) {
                return;
            }

            if (response.data) {
                fillOrderContact(contactType, response.data);
                setContactLookupState(contactType, 'found', 'พบข้อมูลจากรายการ' + contactTypeLabel(response.data.type) + ' และเติมข้อมูลให้แล้ว');
            }
        },
        error: function(jqXHR) {
            if (normalizeMobile(contactMobileField(contactType).val()) !== normalizedMobile) {
                return;
            }

            if (jqXHR.status === 404) {
                setContactLookupState(contactType, 'missing', 'ไม่พบข้อมูลเดิม สามารถกรอกใหม่ได้');
                return;
            }

            setContactLookupState(contactType, 'error', 'ค้นหาข้อมูลไม่สำเร็จ');
        }
    });
}

function lookupContactSuggestions(contactType, mobile) {
    const normalizedMobile = normalizeMobile(mobile);

    if (normalizedMobile.length < 3) {
        hideContactSuggestions(contactType);
        clearContactLookupState(contactType);
        return;
    }

    setContactLookupState(contactType, 'searching', 'กำลังค้นหารายการที่ตรงกับ ' + normalizedMobile + '...');

    $.ajax({
        url: "{{route('api.contacts.suggest')}}",
        method: 'GET',
        dataType: 'JSON',
        data: {
            type: contactType,
            mobile: normalizedMobile,
        },
        success: function(response) {
            if (normalizeMobile(contactMobileField(contactType).val()) !== normalizedMobile) {
                return;
            }

            renderContactSuggestions(contactType, response.data || [], normalizedMobile);
        },
        error: function() {
            if (normalizeMobile(contactMobileField(contactType).val()) !== normalizedMobile) {
                return;
            }

            hideContactSuggestions(contactType);
            setContactLookupState(contactType, 'error', 'ค้นหารายการไม่สำเร็จ');
        }
    });
}

function fillOrderContact(contactType, contact) {
    const prefix = contactType === 'sender' ? 'sender' : 'receive';
    const nameField = contactType === 'sender' ? 'sender_name' : 'receive_name';
    const mobileField = contactType === 'sender' ? 'sender_mobile' : 'receive_mobile';
    const addressField = contactType === 'sender' ? 'sender_address' : 'receive_address';
    const provinceField = $('#' + prefix + '_province');
    const amphureField = $('#' + prefix + '_amphure');
    const districtField = $('#' + prefix + '_district');
    const zipCodeField = $('#' + prefix + '_zip_code');

    $('[name="' + nameField + '"]').val(contact.name || '');
    $('[name="' + mobileField + '"]').val(contact.mobile || '');
    $('[name="' + addressField + '"]').val(contact.address || '');
    zipCodeField.val(contact.zip_code || '');

    if (!contact.province_id) {
        amphureField.html('');
        districtField.html('');
        highlightContactFields(contactType);
        return;
    }

    if (provinceField.find('option[value="' + contact.province_id + '"]').length === 0) {
        provinceField.append('<option value="' + contact.province_id + '">' + (contact.province_name || '') + '</option>');
    }

    provinceField.val(contact.province_id).trigger('change.select2');
    amphureField.html('');
    districtField.html('');

    loadOrderContactSubData(prefix, contact.province_id, 'amphure', contact.amphure_id || '', function() {
        if (!contact.amphure_id) {
            return;
        }

        loadOrderContactSubData(prefix, contact.amphure_id, 'district', contact.district_id || '', function() {
            districtField.trigger('change.select2');
            zipCodeField.val(contact.zip_code || districtField.find(':selected').data('zipcode') || '');
            highlightContactFields(contactType);
        });
    });

    highlightContactFields(contactType);
}

function bindContactLookup() {
    let senderLookupTimer = null;
    let receiverLookupTimer = null;

    $('[name="sender_mobile"]').on('input blur', function(event) {
        const mobile = $(this).val();
        clearTimeout(senderLookupTimer);
        const eventType = event.type;
        senderLookupTimer = setTimeout(function() {
            if (eventType === 'input') {
                lookupContactSuggestions('sender', mobile);
                return;
            }

            lookupContactByMobile('sender', mobile);
        }, 350);
    });

    $('[name="receive_mobile"]').on('input blur', function(event) {
        const mobile = $(this).val();
        clearTimeout(receiverLookupTimer);
        const eventType = event.type;
        receiverLookupTimer = setTimeout(function() {
            if (eventType === 'input') {
                lookupContactSuggestions('receiver', mobile);
                return;
            }

            lookupContactByMobile('receiver', mobile);
        }, 350);
    });

    $('[name="sender_mobile"]').on('blur', function() {
        setTimeout(function() {
            hideContactSuggestions('sender');
        }, 180);
    });

    $('[name="receive_mobile"]').on('blur', function() {
        setTimeout(function() {
            hideContactSuggestions('receiver');
        }, 180);
    });
}

function createReceiversDataTable(params) {
    let isPickupTypeChecked = $('#pickup_type').is(':checked');
    let pickupType;
    let paymentType;
    let dataArray={};
        if($("input:radio[name='payment_type']").is(":checked")){
	            paymentType = $("input:radio[name='payment_type']:checked").val();
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
	    localStorage.setItem(RECEIVER_STORAGE_PREFIX + receiver, JSON.stringify(dataArray));
    clearFormReceiver();
    receiver++;
}
async function showReceiversDataTable() {
	    const localStorageKeys = receiverStorageKeys();
	    $('#table_tbody_receivers').html("");
        if (localStorageKeys.length === 0) {
            $('#table_receivers').hide('fast');
            return;
        }
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
	    const localStorageKeys = receiverStorageKeys();
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
        $('.varidate-receive-address').show();
        clearValidationState();
	}

    function orderValidator(params) {
        const values = formValues(params);
        const includeReceiver = !hasQueuedReceivers() || hasCurrentReceiverPayload(values);

        return validateOrderFields(params, {
            includeSender: true,
            includeReceiver: includeReceiver,
            requirePayment: includeReceiver,
        });
    }

    function receiverValidator(params) {
        return validateOrderFields(params, {
            includeSender: false,
            includeReceiver: true,
            requirePayment: true,
        });
    }
	</script>
