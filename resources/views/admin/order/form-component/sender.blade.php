<div class="card card-info">
    <div class="card-header">
        <h3 class="card-title">ข้อมูลผู้ฝาก</h3>
    </div>
    {{-- {{dump($data)}} --}}
    <!-- /.card-header -->
    <div class="card-body">

            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <label for="sender_contact_search">ค้นหาผู้ฝากเดิม</label>
                        <input id="sender_contact_search" type="text" class="form-control js-contact-search" data-contact-type="sender" placeholder="ค้นหาชื่อหรือเบอร์โทร">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <!-- text input -->
                    <div class="form-group">
                        <label for="sender_name">ชื่อ-นามสกุล ผู้ฝาก <span class="text-danger">*</span></label>
                        <input data-name="ชื่อ-นามสกุลผู้ฝาก" id="sender_name" name="sender_name" type="text" value="{{ old('customer_name', $data->customer_name??"") }}" class="form-control" placeholder="ขื่อ ...">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="sender_mobile">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                        <input data-name="เบอร์โทรศัพท์ผู้ฝาก" id="sender_mobile" name="sender_mobile" value="{{ old('customer_mobile', $data->customer_mobile??"") }}" type="text" class="form-control" placeholder="080 ...">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <!-- textarea -->
                    <div class="form-group">
                        <label for="sender_address">ที่อยู่ ผู้ฝาก</label>
                        <textarea name="sender_address" id="sender_address" class="form-control"  rows="3" placeholder="บ้านเลขที่ ...">{{ old('customer_address', $data->customer_address??"") }}</textarea>
                    </div>
                </div>

            </div>

            <div class="row">
                <div class="col-sm-6">
                    <!-- select -->
                    <div class="form-group">
                        <label for="sender_province">จังหวัด</label>
                        <select name="sender_province" id="sender_province" class="form-control" data-selected="{{ old('province_name', $data->province_name??"") }}">

                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="sender_amphure">อำเภอ</label>
                        <select name="sender_amphure" id="sender_amphure" class="form-control disabled" data-selected="{{ old('amphures_name', $data->amphures_name??"") }}">

                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <!-- select -->
                    <div class="form-group">
                        <label for="sender_district">ตำบล</label>
                        <select name="sender_district" id="sender_district" class="form-control disabled" data-selected="{{ old('district_name', $data->district_name??"") }}">

                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="sender_zip_code">รหัสไปรษณีย์</label>
                        <input name="sender_zip_code" id="sender_zip_code" type="text" value="{{ old('zip_code', $data->zip_code??"") }}"  class="form-control" placeholder="">
                    </div>
                </div>
            </div>




    </div>
    <!-- /.card-body -->
</div>
