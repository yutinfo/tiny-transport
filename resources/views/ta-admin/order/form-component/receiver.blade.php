<div class="card card-danger">
    <div class="card-header">
        <h3 class="card-title">ข้อมูลพัสดุและผู้รับ</h3>

    </div>
    <!-- /.card-header -->

    <div class="card-body">

            <div class="row">
                <div class="col-sm-6">
                    <!-- text input -->
                    <div class="form-group">
                        <label>ชื่อ-นามสกุล ผู้รับ <span class="text-danger">*</span></label>
                        <input data-name="ชื่อ-นามสกุลผู้รับ" type="text" id="receive_name" name="receive_name" class="form-control" placeholder="ขื่อ ...">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                        <input data-name="เบอร์โทรศัพท์ผู้รับ" type="text" id="receive_mobile" name="receive_mobile" class="form-control" placeholder="080 ...">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">

                    <div class="form-group">
                        <label>วิธีจัดส่ง</label>
                        <div class="custom-control custom-radio">
                            <div class="icheck-danger d-inline">
                                <input type="checkbox" value="1" id="pickup_type" name="pickup_type">
                                <label for="pickup_type">
                                    รับด้วยตัวเอง
                                </label>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <!-- textarea -->
                    <div class="form-group">
                        <label>ที่อยู่ ผู้รับ <span class="text-danger varidate-receive-address">*</span></label>
                        <textarea data-name="ที่อยู่ผู้รับ" id="receive_address" name="receive_address" class="form-control" rows="3" placeholder="บ้านเลขที่ ..."></textarea>
                    </div>
                </div>

            </div>

            <div class="row">
                <div class="col-sm-6">
                    <!-- select -->
                    <div class="form-group">
                        <label>จังหวัด <span class="text-danger varidate-receive-address">*</span></label>
                        <select data-name="จังหวัดผู้รับ"  name="receive_province" id="receive_province" class="form-control">

                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>อำเภอ <span class="text-danger varidate-receive-address">*</span></label>
                        <select data-name="อำเภอผู้รับ" name="receive_amphure" id="receive_amphure" class="form-control">

                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <!-- select -->
                    <div class="form-group">
                        <label>ตำบล <span class="text-danger varidate-receive-address">*</span></label>
                        <select data-name="ตำบลผู้รับ" name="receive_district" id="receive_district" class="form-control">

                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>รหัสไปรษณีย์ <span class="text-danger varidate-receive-address">*</span></label>
                        <input data-name="รหัสไปรษณีย์ผู้รับ" id="receive_zip_code" type="text" name="receive_zip_code" class="form-control" placeholder="">
                    </div>
                </div>
            </div>


    </div>
    <!-- /.card-body -->
    <div class="card-body">
        <div class="row">


        </div>
        <div class="row">
            <div class="col-sm-12">
                <!-- textarea -->
                <div class="form-group">
                    <label>ข้อมูลพัสดุ <span class="text-danger">*</span></label>
                    <textarea data-name="ข้อมูลพัสดุ" id="parcel_description" name="parcel_description" class="form-control" rows="3" placeholder=""></textarea>
                </div>
            </div>

        </div>

       <div class="row">
            <div class="col-sm-6">
                <!-- textarea -->
                <div class="form-group">
                    <label>ช่องทางการชำระเงิน <span class="text-danger">*</span></label>
                    <div class="custom-control custom-radio">
                        <div class="icheck-primary d-inline">
                            <input type="radio" id="payment_type1" value="1" data-name="ช่องทางการชำระเงิน" name="payment_type">
                            <label for="payment_type1">
                                จ่ายทันที
                            </label>
                            </div>
                    </div>
                    <div class="custom-control custom-radio">
                        <div class="icheck-primary d-inline">
                            <input type="radio" id="payment_type2" value="2" data-name="ช่องทางการชำระเงิน" name="payment_type">
                            <label for="payment_type2">
                                เก็บเงินปลายทาง
                            </label>
                            </div>
                    </div>
                </div>

            </div>
            <div class="col-sm-6">
                <div class="form-group ">
                    <label>จำนวนเงิน/ราคา <span class="text-danger">*</span></label>
                    <input data-name="จำนวนเงิน/ราคา" id="parcel_pice" name="parcel_pice" type="number" class="text-right form-control form-control-lg" placeholder="00.00">
                </div>

            </div>
        </div>

    </div>
</div>
