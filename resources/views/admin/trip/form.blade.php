<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label for="trip_date">วันที่รอบขนส่ง <span class="text-danger">*</span></label>
            <input type="date" name="trip_date" id="trip_date" value="{{ old('trip_date', $data->trip_date ? \Carbon\Carbon::parse($data->trip_date)->format('Y-m-d') : '') }}" class="form-control" required>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label for="driver_name">พนักงานขับรถ</label>
            <input type="text" name="driver_name" id="driver_name" value="{{ old('driver_name', $data->driver_name) }}" class="form-control">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label for="driver_mobile">เบอร์โทรศัพท์</label>
            <input type="text" name="driver_mobile" id="driver_mobile" value="{{ old('driver_mobile', $data->driver_mobile) }}" class="form-control" maxlength="10">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label for="car_id">ทะเบียนรถ</label>
            <input type="text" name="car_id" id="car_id" value="{{ old('car_id', $data->car_id) }}" class="form-control">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group mb-0">
            <label for="area_name">พื้นที่จัดส่ง</label>
            <input type="text" name="area_name" id="area_name" value="{{ old('area_name', $data->area_name) }}" class="form-control">
        </div>
    </div>
</div>
