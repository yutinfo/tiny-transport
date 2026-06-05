<div class="card card-info">
    <div class="card-header">
        <h3 class="card-title">รายละเอียดผู้ติดต่อ</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>ประเภท <span class="text-danger">*</span></label>
                    <select name="type" class="form-control @error('type') is-invalid @enderror">
                        @foreach ($typeLabels as $type => $label)
                            <option value="{{$type}}" {{old('type', $data->type ?? 'receiver') == $type ? 'selected' : ''}}>{{$label}}</option>
                        @endforeach
                    </select>
                    @error('type') <div class="invalid-feedback d-block">{{$message}}</div> @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{old('name', $data->name)}}" class="form-control @error('name') is-invalid @enderror" placeholder="ชื่อ ...">
                    @error('name') <div class="invalid-feedback d-block">{{$message}}</div> @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                    <input type="text" name="mobile" value="{{old('mobile', $data->mobile)}}" class="form-control @error('mobile') is-invalid @enderror" placeholder="080 ...">
                    @error('mobile') <div class="invalid-feedback d-block">{{$message}}</div> @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>ที่อยู่</label>
                    <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="3" placeholder="บ้านเลขที่ ...">{{old('address', $data->address)}}</textarea>
                    @error('address') <div class="invalid-feedback d-block">{{$message}}</div> @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>จังหวัด</label>
                    <select name="province_id" id="contact_province_id" class="form-control @error('province_id') is-invalid @enderror" data-selected="{{old('province_id', $data->province_id)}}">
                        <option value="">เลือก</option>
                        @foreach ($provinces as $province)
                            <option value="{{$province->id}}" {{old('province_id', $data->province_id) == $province->id ? 'selected' : ''}}>{{$province->name_th}}</option>
                        @endforeach
                    </select>
                    @error('province_id') <div class="invalid-feedback d-block">{{$message}}</div> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>อำเภอ</label>
                    <select name="amphure_id" id="contact_amphure_id" class="form-control @error('amphure_id') is-invalid @enderror" data-selected="{{old('amphure_id', $data->amphure_id)}}">
                        <option value="">เลือก</option>
                    </select>
                    @error('amphure_id') <div class="invalid-feedback d-block">{{$message}}</div> @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>ตำบล</label>
                    <select name="district_id" id="contact_district_id" class="form-control @error('district_id') is-invalid @enderror" data-selected="{{old('district_id', $data->district_id)}}">
                        <option value="">เลือก</option>
                    </select>
                    @error('district_id') <div class="invalid-feedback d-block">{{$message}}</div> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>รหัสไปรษณีย์</label>
                    <input type="text" name="zip_code" id="contact_zip_code" value="{{old('zip_code', $data->zip_code)}}" class="form-control @error('zip_code') is-invalid @enderror">
                    @error('zip_code') <div class="invalid-feedback d-block">{{$message}}</div> @enderror
                </div>
            </div>
        </div>
    </div>
</div>
