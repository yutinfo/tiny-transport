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

    bindEditContactSearch();


});

function editContactEscapeHtml(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function editContactTypeLabel(type) {
    const labels = {
        sender: 'ผู้ส่ง',
        receiver: 'ผู้รับ',
        both: 'ผู้ส่ง/ผู้รับ',
    };

    return labels[type] || type || '';
}

function editContactSuggestionList(field) {
    const formGroup = field.closest('.form-group');
    let list = formGroup.find('.js-edit-contact-suggestion-list');

    if (!list.length) {
        formGroup.css('position', 'relative');
        list = $('<div class="list-group shadow-sm js-edit-contact-suggestion-list" style="display:none; position:absolute; left:0; right:0; z-index:1050; max-height:260px; overflow:auto;"></div>');
        formGroup.append(list);
    }

    return list;
}

function appendEditOption(select, value, label) {
    if (!value) {
        return;
    }

    if (select.find('option[value="' + value + '"]').length === 0) {
        select.append('<option value="' + editContactEscapeHtml(value) + '">' + editContactEscapeHtml(label || value) + '</option>');
    }

    select.val(value).trigger('change.select2');
}

function fillEditContact(field, contact) {
    const contactType = field.data('contact-type');
    const receiverId = field.data('receiver-id');

    if (contactType === 'sender') {
        $('#sender_name').val(contact.name || '');
        $('#sender_mobile').val(contact.mobile || '');
        $('#sender_address').val(contact.address || '');
        $('#sender_zip_code').val(contact.zip_code || '');
        appendEditOption($('#sender_province'), contact.province_name, contact.province_name);
        appendEditOption($('#sender_amphure'), contact.amphure_name, contact.amphure_name);
        appendEditOption($('#sender_district'), contact.district_name, contact.district_name);
        field.val([contact.name, contact.mobile].filter(Boolean).join(' - '));
        return;
    }

    $('#receive_name' + receiverId).val(contact.name || '');
    $('#receive_mobile' + receiverId).val(contact.mobile || '');
    $('#receive_address' + receiverId).val(contact.address || '');
    $('#receive_zip_code' + receiverId).val(contact.zip_code || '');
    appendEditOption($('#receive_province' + receiverId), contact.province_name, contact.province_name);
    appendEditOption($('#receive_amphure' + receiverId), contact.amphure_name, contact.amphure_name);
    appendEditOption($('#receive_district' + receiverId), contact.district_name, contact.district_name);
    field.val([contact.name, contact.mobile].filter(Boolean).join(' - '));
}

function renderEditContactSuggestions(field, contacts, keyword) {
    const list = editContactSuggestionList(field);
    list.html('');

    if (!contacts.length) {
        list.hide();
        return;
    }

    contacts.forEach(function(contact) {
        const item = $(
            '<button type="button" class="list-group-item list-group-item-action text-left">' +
                '<div class="d-flex justify-content-between align-items-center">' +
                    '<strong>' + editContactEscapeHtml(contact.name) + '</strong>' +
                    '<span class="badge badge-light">' + editContactEscapeHtml(editContactTypeLabel(contact.type)) + '</span>' +
                '</div>' +
                '<div class="small text-muted">' + editContactEscapeHtml(contact.mobile) + '</div>' +
            '</button>'
        );

        item.on('mousedown', function(event) {
            event.preventDefault();
            fillEditContact(field, contact);
            list.hide().html('');
        });

        list.append(item);
    });

    list.show();
}

function bindEditContactSearch() {
    let timer = null;

    $('.js-edit-contact-search').on('input', function() {
        const field = $(this);
        const keyword = $.trim(field.val() || '');

        clearTimeout(timer);

        if (keyword.length < 2) {
            editContactSuggestionList(field).hide().html('');
            return;
        }

        timer = setTimeout(function() {
            $.ajax({
                url: "{{ route('admin.api.contacts.search') }}",
                method: 'GET',
                dataType: 'JSON',
                data: {
                    type: field.data('contact-type'),
                    q: keyword,
                },
                success: function(response) {
                    if ($.trim(field.val() || '') !== keyword) {
                        return;
                    }

                    renderEditContactSuggestions(field, response.data || [], keyword);
                },
                error: function() {
                    editContactSuggestionList(field).hide().html('');
                }
            });
        }, 300);
    });

    $('.js-edit-contact-search').on('blur', function() {
        const field = $(this);
        setTimeout(function() {
            editContactSuggestionList(field).hide().html('');
        }, 180);
    });
}





</script>
