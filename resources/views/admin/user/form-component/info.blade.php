<div class="card card-info">
    <div class="card-header">
        <h3 class="card-title">ข้อมูลผู้ใช้</h3>
    </div>
    <!-- /.card-header -->
    <div class="card-body">

            <div class="row">
                <div class="col-sm-6">
                    <!-- text input -->
                    <div class="form-group">
                        <label for="user_name">ชื่อ</label>
                        <input type="text" name="name" id="user_name" value="{{old('name',$data->name??'')}}" class="form-control" placeholder="ขื่อ ...">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="user_last_name">นามสกุล</label>
                        <input type="text" name="last_name" id="user_last_name" value="{{old('last_name',$data->last_name??'')}}" class="form-control" placeholder="นามสกุล ...">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <!-- text input -->
                    <div class="form-group">
                        <label for="user_email">อีเมล</label>
                        <input type="text" name="email" id="user_email" value="{{old('email',$data->email??'')}}" class="form-control" placeholder="อีเมล ...">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="user_status">สถานะการเข้าใช้ระบบ</label>
                       <select name="status" id="user_status" class="form-control">
                        <option value="">-- เลือก --</option>
                           <option @isset($data->status) {{$data->status=='active'?'selected':''}} @endif value="active">อนุญาต</option>
                           <option @isset($data->status) {{$data->status=='inactive'?'selected':''}} @endif value="inactive">ยกเลิก</option>

                       </select>
                    </div>
                </div>
            </div>







    </div>
    <!-- /.card-body -->
</div>
