@php
    $isEdit = $data->exists ?? false;
@endphp
<div class="ta-form-grid">
    <div class="col-md-4">
        <div class="form-group">
            <label for="name">ชื่อ <span class="text-danger">*</span></label>
            <input type="text" name="name" id="name" value="{{ old('name', $data->name) }}" class="form-control" maxlength="100" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="last_name">นามสกุล</label>
            <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $data->last_name) }}" class="form-control" maxlength="100">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="mobile">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
            <input type="text" name="mobile" id="mobile" value="{{ old('mobile', $data->mobile) }}" class="form-control" maxlength="10" required>
            <small class="form-text text-muted">ตัวเลข 9–10 หลัก ห้ามซ้ำกับคนขับรายอื่น</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="license_plate">ทะเบียนรถประจำตัว</label>
            <input type="text" name="license_plate" id="license_plate" value="{{ old('license_plate', $data->license_plate) }}" class="form-control" maxlength="20">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="driver_license_no">เลขใบขับขี่</label>
            <input type="text" name="driver_license_no" id="driver_license_no" value="{{ old('driver_license_no', $data->driver_license_no) }}" class="form-control" maxlength="20">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="area_name">พื้นที่วิ่งประจำ</label>
            <input type="text" name="area_name" id="area_name" value="{{ old('area_name', $data->area_name) }}" class="form-control" maxlength="100">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="status">สถานะ <span class="text-danger">*</span></label>
            <select name="status" id="status" class="form-control" required>
                @foreach(\App\Models\Driver::statusLabels() as $value => $label)
                    <option value="{{ $value }}" {{ old('status', $data->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <small class="form-text text-muted">ปิดใช้งานแล้วจะไม่แสดงในตัวเลือกตอนสร้างรอบ และบัญชี login ที่ผูกจะเข้าระบบไม่ได้</small>
        </div>
    </div>
    <div class="col-md-8">
        <div class="form-group mb-0">
            <label for="note">หมายเหตุ</label>
            <textarea name="note" id="note" class="form-control" rows="4">{{ old('note', $data->note) }}</textarea>
        </div>
    </div>
</div>

<hr>

<h3 class="ta-section-title">บัญชีเข้าสู่ระบบ (Driver Portal)</h3>
<p class="ta-section-subtitle">เลือกวิธีจัดการบัญชี login ของคนขับรายนี้ — 1 บัญชีต่อ 1 คนขับ</p>

@php
    $defaultMode = $isEdit ? 'keep' : 'none';
    $accountMode = old('account_mode', $defaultMode);
@endphp

<div class="ta-form-grid">
    <div class="col-md-12">
        <div class="form-group">
            @if($isEdit && $data->user)
                <div class="alert alert-info py-2 mb-2">
                    บัญชีปัจจุบัน: <strong>{{ $data->user->username }}</strong>
                    ({{ trim($data->user->name . ' ' . $data->user->last_name) }})
                </div>
            @endif

            @if($isEdit)
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="account_mode" id="account_keep" value="keep" {{ $accountMode === 'keep' ? 'checked' : '' }}>
                    <label class="form-check-label" for="account_keep">คงบัญชีเดิมไว้ (ไม่เปลี่ยน)</label>
                </div>
            @else
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="account_mode" id="account_none" value="none" {{ $accountMode === 'none' ? 'checked' : '' }}>
                    <label class="form-check-label" for="account_none">ไม่มีบัญชี (คนขับชั่วคราว/รายวัน — assign รอบได้แต่ไม่เห็น Portal)</label>
                </div>
            @endif

            <div class="form-check">
                <input class="form-check-input" type="radio" name="account_mode" id="account_create" value="create" {{ $accountMode === 'create' ? 'checked' : '' }}>
                <label class="form-check-label" for="account_create">สร้างบัญชีใหม่</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="account_mode" id="account_link" value="link" {{ $accountMode === 'link' ? 'checked' : '' }}>
                <label class="form-check-label" for="account_link">ผูกบัญชี driver ที่มีอยู่</label>
            </div>
            @if($isEdit && $data->user)
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="account_mode" id="account_unlink" value="unlink" {{ $accountMode === 'unlink' ? 'checked' : '' }}>
                    <label class="form-check-label" for="account_unlink">ปลดการผูกบัญชี (รอบเก่าไม่เปลี่ยน)</label>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="ta-form-grid ta-account-create-fields" style="{{ $accountMode === 'create' ? '' : 'display:none;' }}">
    <div class="col-md-4">
        <div class="form-group">
            <label for="username">ชื่อผู้ใช้ (username)</label>
            <input type="text" name="username" id="username" value="{{ old('username') }}" class="form-control" autocomplete="off">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="account_email">อีเมล</label>
            <input type="email" name="email" id="account_email" value="{{ old('email') }}" class="form-control" autocomplete="off">
        </div>
    </div>
    <div class="col-md-4"></div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="password">รหัสผ่าน</label>
            <input type="password" name="password" id="password" class="form-control" autocomplete="new-password">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="password_confirmation">ยืนยันรหัสผ่าน</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" autocomplete="new-password">
        </div>
    </div>
</div>

<div class="ta-form-grid ta-account-link-fields" style="{{ $accountMode === 'link' ? '' : 'display:none;' }}">
    <div class="col-md-6">
        <div class="form-group mb-0">
            <label for="user_id">บัญชี driver ที่ยังไม่ถูกผูก</label>
            <select name="user_id" id="user_id" class="form-control">
                <option value="">-- เลือกบัญชี --</option>
                @foreach($unlinkedDriverUsers ?? [] as $u)
                    <option value="{{ $u->id }}" {{ (string) old('user_id', $isEdit ? $data->user_id : '') === (string) $u->id ? 'selected' : '' }}>
                        {{ $u->username }} ({{ trim($u->name . ' ' . $u->last_name) }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

@push('page_scripts')
<script>
    $(function () {
        function refreshAccountFields() {
            var mode = $('input[name="account_mode"]:checked').val();
            $('.ta-account-create-fields').toggle(mode === 'create');
            $('.ta-account-link-fields').toggle(mode === 'link');
        }
        $('input[name="account_mode"]').on('change', refreshAccountFields);
        refreshAccountFields();
    });
</script>
@endpush
