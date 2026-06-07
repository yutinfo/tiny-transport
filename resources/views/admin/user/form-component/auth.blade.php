<div class="card ta-form-section">
    <div class="card-header">
        <div>
            <h3 class="ta-section-title">ข้อมูลเข้าสู่ระบบ</h3>
            <p class="ta-section-subtitle">กำหนดข้อมูลยืนยันตัวตนและสิทธิ์การใช้งาน</p>
        </div>
    </div>
    <div class="card-body">
            <div class="ta-form-grid">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="username">ชื่อเข้าใช้ระบบ Username</label>
                        <input type="text" @isset($data->username) disabled @endif name="username" id="username" value="{{old('username',$data->username??'')}}" class="form-control" placeholder="ขื่อ ...">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="password">รหัสผ่าน Password</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder=" ...">
                    </div>
                </div>
            </div>
            <div class="ta-form-grid">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="role_name">บทบาท</label>
                        <select name="role_name" id="role_name" class="form-control">
                            <option value="">-- เลือก --</option>
                            @php($roleLabels = \App\Models\User::roleLabels())
                            @foreach($roleLabels as $roleValue => $roleLabel)
                                <option value="{{ $roleValue }}" @isset($data->role_name) {{ $data->role_name === $roleValue ? 'selected' : '' }} @endif>
                                    {{ $roleLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">

                </div>
            </div>



    </div>
    <!-- /.card-body -->
</div>
