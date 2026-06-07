@extends('layouts.app')

@section('content')
<div class="container-fluid ta-page-shell">
    <section class="ta-page-header-card">
        <div class="card-body">
            <div class="ta-page-header-row">
                <div>
                    <span class="ta-page-kicker"><i class="fas fa-chart-bar" aria-hidden="true"></i> Reports</span>
                    <h1 class="ta-page-title">รายงาน</h1>
                    <p class="ta-page-subtitle">หน้ารายงานถูกปรับโครงสร้างให้พร้อมกับระบบกรองและการส่งออกข้อมูลแบบใหม่</p>
                </div>
                <div class="ta-page-actions">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-default"><i class="fas fa-arrow-left"></i> กลับแดชบอร์ด</a>
                </div>
            </div>
        </div>
    </section>

    <section class="card ta-toolbar-card">
        <div class="card-body">
            <form>
                <div class="ta-toolbar-grid">
                    <div class="ta-col-span-3">
                        <label for="report-date-from">วันที่เริ่ม</label>
                        <input id="report-date-from" type="date" class="form-control">
                    </div>
                    <div class="ta-col-span-3">
                        <label for="report-date-to">วันที่สิ้นสุด</label>
                        <input id="report-date-to" type="date" class="form-control">
                    </div>
                    <div class="ta-col-span-3">
                        <label for="report-type">ประเภทรายงาน</label>
                        <select id="report-type" class="form-control">
                            <option>สรุปพัสดุ</option>
                            <option>สรุป COD</option>
                            <option>สรุปรอบขนส่ง</option>
                        </select>
                    </div>
                    <div class="ta-col-span-3 ta-toolbar-actions">
                        <button type="button" class="btn bg-info"><i class="fas fa-filter"></i> กรองข้อมูล</button>
                        <button type="button" class="btn btn-default"><i class="fas fa-file-export"></i> ส่งออก</button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="card ta-table-card">
        <div class="card-header">
            <div>
                <h3 class="ta-section-title">ผลลัพธ์รายงาน</h3>
                <p class="ta-section-subtitle">รองรับการนำตารางรายงานจริงมาเสียบต่อในภายหลังโดยไม่ต้องเปลี่ยนโครง UI อีก</p>
            </div>
        </div>
        <div class="card-body">
            <div class="ta-empty-state">
                <div class="ta-empty-state__icon"><i class="fas fa-table"></i></div>
                <div>ยังไม่มีชุดข้อมูลรายงานในหน้านี้</div>
            </div>
        </div>
    </section>
</div>
@endsection
