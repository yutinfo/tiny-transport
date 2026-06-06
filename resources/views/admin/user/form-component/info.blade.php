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
                        <label>ชื่อ</label>
                        <input type="text" name="name" value="{{old('name',$data->name??'')}}" class="form-control" placeholder="ขื่อ ...">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>นามสกุล</label>
                        <input type="text" name="last_name" value="{{old('last_name',$data->last_name??'')}}" class="form-control" placeholder="นามสกุล ...">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <!-- text input -->
                    <div class="form-group">
                        <label>อีเมล</label>
                        <input type="text" name="email" value="{{old('email',$data->email??'')}}" class="form-control" placeholder="อีเมล ...">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>สถานะการเข้าใช้ระบบ</label>
                       <select name="status" id="" class="form-control">
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
