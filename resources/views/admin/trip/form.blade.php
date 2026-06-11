@php
    $selectedDriverId = old('driver_id', $data->driver_id);
    $excludeTripId = $data->exists ? $data->id : null;
@endphp
<div class="ta-form-grid">
    <div class="col-md-3">
        <div class="form-group">
            <label for="trip_date">วันที่รอบขนส่ง <span class="text-danger">*</span></label>
            <input type="date" name="trip_date" id="trip_date" value="{{ old('trip_date', $data->trip_date ? \Carbon\Carbon::parse($data->trip_date)->format('Y-m-d') : '') }}" class="form-control" required>
        </div>
    </div>
    <div class="col-md-9">
        <div class="form-group">
            <label for="driver_id">คนขับรถ</label>
            <select name="driver_id" id="driver_id" class="form-control ta-driver-select" style="width:100%;"
                    data-availability-url="{{ route('admin.api.drivers.availability') }}"
                    @if($excludeTripId) data-exclude-trip="{{ $excludeTripId }}" @endif>
                <option value="">-- ไม่ระบุคนขับ (กรอกมือ) --</option>
                @foreach($drivers ?? [] as $driver)
                    <option value="{{ $driver->id }}"
                            data-name="{{ $driver->full_name }}"
                            data-mobile="{{ $driver->mobile }}"
                            data-license="{{ $driver->license_plate }}"
                            data-area="{{ $driver->area_name }}"
                            {{ (string) $selectedDriverId === (string) $driver->id ? 'selected' : '' }}
                            {{ $driver->status !== \App\Models\Driver::STATUS_ACTIVE && (string) $selectedDriverId !== (string) $driver->id ? 'disabled' : '' }}>
                        {{ $driver->full_name }} ({{ $driver->license_plate ?: 'ไม่มีทะเบียน' }} · {{ $driver->mobile }})@if($driver->status !== \App\Models\Driver::STATUS_ACTIVE) [ปิดใช้งาน]@endif
                    </option>
                @endforeach
            </select>
            <small class="form-text text-muted">ค้นหาด้วยชื่อ / เบอร์ / ทะเบียนรถ — เลือกแล้วระบบจะเติมข้อมูลด้านล่างให้ (แก้ทับได้)</small>
            <input type="hidden" name="confirm_busy" id="confirm_busy" value="{{ old('confirm_busy') }}">
        </div>
    </div>
    {{-- Legacy account dropdown kept for trips that used it directly without a master driver. --}}
    <div class="col-md-12" id="legacy_driver_user_wrapper" style="{{ $selectedDriverId ? 'display:none;' : '' }}">
        <div class="form-group">
            <label for="driver_user_id">บัญชีคนขับ (กรณีไม่เลือกจากรายชื่อ)</label>
            <select name="driver_user_id" id="driver_user_id" class="form-control">
                <option value="">-- ไม่ระบุ --</option>
                @foreach(($legacyDriverUsers ?? collect()) as $u)
                    <option value="{{ $u->id }}" {{ (string) old('driver_user_id', $data->driver_user_id) === (string) $u->id ? 'selected' : '' }}>
                        {{ trim($u->name . ' ' . $u->last_name) }}
                    </option>
                @endforeach
            </select>
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
    <div class="col-md-3">
        <div class="form-group mb-0">
            <label for="area_name">พื้นที่จัดส่ง</label>
            <input type="text" name="area_name" id="area_name" value="{{ old('area_name', $data->area_name) }}" class="form-control">
        </div>
    </div>
</div>

@push('page_css')
<link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
<style>
    .ta-avail-badge { font-size: 11px; padding: 2px 6px; border-radius: 10px; margin-left: 6px; }
    .ta-avail-badge--free { background: #d4edda; color: #155724; }
    .ta-avail-badge--busy { background: #f8d7da; color: #721c24; }
</style>
@endpush

@push('page_scripts')
<script src="/plugins/select2/js/select2.full.min.js"></script>
<script>
(function () {
    var $select = $('#driver_id');
    if (!$select.length) { return; }

    var availability = {}; // driver_id -> {busy, trips:[...]}

    function fillFromOption($opt) {
        if (!$opt.val()) { return; }
        // Auto-fill snapshot fields (only overwrite — user can edit afterwards).
        $('#driver_name').val($opt.data('name') || '');
        $('#driver_mobile').val($opt.data('mobile') || '');
        $('#car_id').val($opt.data('license') || '');
        $('#area_name').val($opt.data('area') || '');
    }

    function badgeFor(driverId) {
        var info = availability[driverId];
        if (!info) { return ''; }
        if (info.busy) {
            var codes = (info.trips || []).map(function (t) { return t.code; }).join(', ');
            return '🔴 มีรอบแล้ว' + (codes ? ' (' + codes + ')' : '');
        }
        return '🟢 ว่าง';
    }

    function formatOption(state) {
        if (!state.id) { return state.text; }
        var $el = $(state.element);
        var label = state.text;
        var badge = badgeFor(state.id);
        var $wrap = $('<span></span>').text(label);
        if (badge) {
            var cls = availability[state.id] && availability[state.id].busy ? 'ta-avail-badge--busy' : 'ta-avail-badge--free';
            $wrap.append($('<span class="ta-avail-badge"></span>').addClass(cls).text(badge));
        }
        return $wrap;
    }

    $select.select2({
        theme: 'default',
        width: '100%',
        templateResult: formatOption,
        templateSelection: function (state) { return state.text; }
    });

    function loadAvailability() {
        var date = $('#trip_date').val();
        if (!date) { return; }
        var url = $select.data('availability-url');
        var params = { date: date };
        var exclude = $select.data('exclude-trip');
        if (exclude) { params.exclude_trip = exclude; }
        $.getJSON(url, params).done(function (rows) {
            availability = {};
            (rows || []).forEach(function (r) { availability[r.driver_id] = r; });
            // Force Select2 to re-render the options with fresh badges.
            $select.trigger('change.select2');
        });
    }

    function maybeConfirmBusy() {
        var id = $select.val();
        $('#confirm_busy').val('');
        if (!id || !availability[id] || !availability[id].busy) { return true; }
        var codes = (availability[id].trips || []).map(function (t) { return t.code; }).join(', ');
        var ok = window.confirm('คนขับมีรอบ ' + codes + ' ในวันที่นี้แล้ว ยืนยันจัดรอบซ้อน?');
        if (ok) { $('#confirm_busy').val('1'); }
        return ok;
    }

    $select.on('select2:select', function (e) {
        var $opt = $(e.params.data.element);
        fillFromOption($opt);
        $('#legacy_driver_user_wrapper').hide();
        maybeConfirmBusy();
    });

    $select.on('select2:clear', function () {
        $('#legacy_driver_user_wrapper').show();
        $('#confirm_busy').val('');
    });

    $('#trip_date').on('change', loadAvailability);

    // Validate the busy confirmation on submit (guards manual changes).
    $select.closest('form').on('submit', function (e) {
        if (!maybeConfirmBusy()) { e.preventDefault(); }
    });

    loadAvailability();
})();
</script>
@endpush
