<div class="card card-danger">
    <div class="card-header">
        <h3 class="card-title">ข้อมูลเข้าสู่ระบบ</h3>
    </div>
    <!-- /.card-header -->
    <div class="card-body">

            <div class="row">
                <div class="col-sm-6">
                    <!-- text input -->
                    <div class="form-group">
                        <label>ชื่อเข้าใช้ระบบ Username</label>
                        <input type="text" @isset($data->username) disabled @endif name="username" value="{{old('username',$data->username??'')}}" class="form-control" placeholder="ขื่อ ...">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>รหัสผ่าน Password</label>
                        <input type="password" name="password"  class="form-control" placeholder=" ...">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <!-- text input -->
                    <div class="form-group">
                        <label>บทบาท</label>
                       <select name="role_name" id="" class="form-control">
                        <option value="">-- เลือก --</option>
                           <option  @isset($data->role_name) {{$data->role_name=='admin'?'selected':''}} @endif value="admin">ผู้ดูแลระบบ</option>
                           <option  @isset($data->role_name) {{$data->role_name=='staff'?'selected':''}} @endif value="staff">พนักงาน</option>

                       </select>
                    </div>
                </div>
                <div class="col-sm-6">

                </div>
            </div>



    </div>
    <!-- /.card-body -->
</div>
