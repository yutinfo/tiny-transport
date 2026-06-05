<script>
$(function() {
    const amphureSelected = $('#contact_amphure_id').data('selected') || '';
    const districtSelected = $('#contact_district_id').data('selected') || '';

    $('#contact_province_id, #contact_amphure_id, #contact_district_id').select2();

    function loadContactSubData(target, params, selectedValue, callback) {
        let url = '';

        if (target === 'amphure') {
            url = "{{route('api.amphure.index')}}" + "?province_id=" + params;
        }

        if (target === 'district') {
            url = "{{route('api.district.index')}}" + "?amphure_id=" + params;
        }

        if (!url) {
            return;
        }

        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'JSON',
            success: function(response) {
                const element = $('#contact_' + target + '_id');
                element.html('<option value="">เลือก</option>');

                response.data.forEach(function(item) {
                    const zipCode = target === 'district' ? ' data-zipcode="' + item.zip_code + '"' : '';
                    const selected = String(selectedValue) === String(item.id) ? ' selected' : '';
                    element.append('<option value="' + item.id + '"' + zipCode + selected + '>' + item.name_th + '</option>');
                });

                element.trigger('change.select2');

                if (callback) {
                    callback();
                }
            }
        });
    }

    $('#contact_province_id').on('change', function() {
        const provinceId = $(this).val();
        $('#contact_amphure_id').html('<option value="">เลือก</option>').trigger('change.select2');
        $('#contact_district_id').html('<option value="">เลือก</option>').trigger('change.select2');
        $('#contact_zip_code').val('');

        if (provinceId) {
            loadContactSubData('amphure', provinceId, '');
        }
    });

    $('#contact_amphure_id').on('change', function() {
        const amphureId = $(this).val();
        $('#contact_district_id').html('<option value="">เลือก</option>').trigger('change.select2');
        $('#contact_zip_code').val('');

        if (amphureId) {
            loadContactSubData('district', amphureId, '');
        }
    });

    $('#contact_district_id').on('change', function() {
        const zipCode = $(this).find(':selected').data('zipcode') || '';
        if (zipCode) {
            $('#contact_zip_code').val(zipCode);
        }
    });

    if ($('#contact_province_id').val()) {
        loadContactSubData('amphure', $('#contact_province_id').val(), amphureSelected, function() {
            if (amphureSelected) {
                loadContactSubData('district', amphureSelected, districtSelected);
            }
        });
    }

    @if($errors->any())
        const contactErrors = @json($errors->all());
        $(".msg-alert-danger-show-text").html("");
        contactErrors.forEach(function(error) {
            $(".msg-alert-danger-show-text").append(error + "<br />");
        });
        $(".msg-alert-danger").show("slow");
    @endif

    @if(session()->has('success'))
        $(".msg-alert-success-show-text").html("").append("บันทึกข้อมูลสำเร็จ");
        $(".msg-alert-success").show("slow");
        setTimeout(function() {
            window.location.href = "{{route('ta-admin.contacts.index')}}";
        }, 1200);
    @endif
});
</script>
