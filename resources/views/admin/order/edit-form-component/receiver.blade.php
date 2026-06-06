<div class="card card-danger">
    <div class="card-header">
        <h3 class="card-title">ข้อมูลพัสดุและผู้รับ</h3>
        <div class="card-tools">
            <a href="{{ route('admin.parcels.tracking', $data_item) }}" class="btn bg-secondary btn-xs"><i class="fas fa-history"></i> ดูประวัติ</a>
        </div>
    </div>
    <!-- /.card-header -->

    <div class="card-body">
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <label for="edit_receiver_contact_search{{$data_item->id}}">ค้นหาผู้รับเดิม</label>
                        <input id="edit_receiver_contact_search{{$data_item->id}}" type="text" class="form-control js-edit-contact-search" data-contact-type="receiver" data-receiver-id="{{$data_item->id}}" placeholder="ค้นหาชื่อหรือเบอร์โทร">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <!-- text input -->
                    <div class="form-group">
                        <label for="receive_name{{$data_item->id}}">ชื่อ-นามสกุล ผู้รับ </label>
                        <input data-name="ชื่อ-นามสกุลผู้รับ" type="text" id="receive_name{{$data_item->id}}" value="{{ old('receive_name', $data_item->receive_name??"") }}" name="receive_name[{{$data_item->id}}][]" class="form-control" placeholder="ขื่อ ...">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="receive_mobile{{$data_item->id}}">เบอร์โทรศัพท์ </label>
                        <input data-name="เบอร์โทรศัพท์ผู้รับ" type="text" id="receive_mobile{{$data_item->id}}" value="{{ old('receive_mobile', $data_item->receive_mobile??"") }}" name="receive_mobile[{{$data_item->id}}][]" class="form-control" placeholder="080 ...">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">

                    <div class="form-group">
                        <label for="pickup_type{{$data_item->id}}">วิธีจัดส่ง</label>
                        <div class="custom-control custom-radio">
                            <div class="icheck-danger d-inline">
                                <input type="checkbox" value="1" {{$data_item->parcel_pickup_type=='pickup'?"checked":""}} id="pickup_type{{$data_item->id}}"  name="pickup_type[{{$data_item->id}}][]">
                                <label for="pickup_type{{$data_item->id}}">
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
                        <label for="receive_address{{$data_item->id}}">ที่อยู่ ผู้รับ </label>
                        <textarea data-name="ที่อยู่ผู้รับ" id="receive_address{{$data_item->id}}" name="receive_address[{{$data_item->id}}][]" class="form-control" rows="3" placeholder="บ้านเลขที่ ...">{{ old('receive_address', $data_item->receive_address??"") }}</textarea>
                    </div>
                </div>

            </div>

            <div class="row">
                <div class="col-sm-6">
                    <!-- select -->
                    <div class="form-group">
                        <label for="receive_province{{$data_item->id}}">จังหวัด : {{ $data_item->province_name??"" }}</label>
                        <select data-name="จังหวัดผู้รับ"  name="receive_province[{{$data_item->id}}][]" id="receive_province{{$data_item->id}}" class="form-control receive_province" data-id="{{$data_item->id}}">

                        </select>
                        <input type="hidden" name="receive_province_id[{{$data_item->id}}][]" id="receive_province_id{{$data_item->id}}">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="receive_amphure{{$data_item->id}}">อำเภอ : {{ $data_item->amphures_name??"" }}</label>
                        <select data-name="อำเภอผู้รับ" name="receive_amphure[{{$data_item->id}}][]" id="receive_amphure{{$data_item->id}}" class="form-control receive_amphure">

                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <!-- select -->
                    <div class="form-group">
                        <label for="receive_district{{$data_item->id}}">ตำบล : {{ $data_item->district_name??"" }}</label>
                        <select data-name="ตำบลผู้รับ" name="receive_district[{{$data_item->id}}][]" data-item-id="{{$data_item->id}}" id="receive_district{{$data_item->id}}" class="form-control receive_district">

                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="receive_zip_code{{$data_item->id}}">รหัสไปรษณีย์ </label>
                        <input data-name="รหัสไปรษณีย์ผู้รับ" id="receive_zip_code{{$data_item->id}}" value="{{ old('zip_code', $data_item->zip_code??"") }}"  type="text" name="receive_zip_code[{{$data_item->id}}][]" class="form-control receive_zip_code" placeholder="">
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
                    <label for="parcel_description{{$data_item->id}}">ข้อมูลพัสดุ </label>
                    <textarea data-name="ข้อมูลพัสดุ"  id="parcel_description{{$data_item->id}}" name="parcel_description[{{$data_item->id}}][]" class="form-control" rows="3" placeholder="">{{ old('parcel_description', $data_item->parcel_description??"") }}</textarea>
                </div>
            </div>

        </div>

       <div class="row">
            <div class="col-sm-6">
                <!-- textarea -->
                <div class="form-group">
                    <label for="payment_type{{$data_item->id}}-1">ช่องทางการชำระเงิน </label>
                    <div class="custom-control custom-radio">
                        <div class="icheck-primary d-inline">
                            <input type="radio" {{$data_item->payment_type=='immediately'?"checked":""}} id="payment_type{{$data_item->id}}-1" value="1" data-name="ช่องทางการชำระเงิน" name="payment_type[{{$data_item->id}}][]">
                            <label for="payment_type{{$data_item->id}}-1">
                                จ่ายทันที
                            </label>
                            </div>
                    </div>
                    <div class="custom-control custom-radio">
                        <div class="icheck-primary d-inline">
                            <input type="radio" {{$data_item->payment_type=='on_delivery'?"checked":""}} id="payment_type{{$data_item->id}}-2" value="2" data-name="ช่องทางการชำระเงิน" name="payment_type[{{$data_item->id}}][]">
                            <label for="payment_type{{$data_item->id}}-2">
                                เก็บเงินปลายทาง
                            </label>
                            </div>
                    </div>
                </div>

            </div>
            <div class="col-sm-6">
                <div class="form-group ">
                    <label for="parcel_pice{{$data_item->id}}">จำนวนเงิน/ราคา </label>
                    <input data-name="จำนวนเงิน/ราคา" value="{{ old('parcel_pice', $data_item->parcel_pice??"") }}" id="parcel_pice{{$data_item->id}}" name="parcel_pice[{{$data_item->id}}][]" type="number" class="text-right form-control form-control-lg" placeholder="00.00">
                </div>

            </div>
        </div>

    </div>
</div>
