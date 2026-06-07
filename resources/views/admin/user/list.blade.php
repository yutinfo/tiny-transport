@extends('layouts.app')

@section('content')
<div class="container-fluid ta-page-shell">
    <section class="ta-page-header-card">
        <div class="card-body">
            <div class="ta-page-header-row">
                <div>
                    <span class="ta-page-kicker"><i class="fas fa-users-cog" aria-hidden="true"></i> Users</span>
                    <h1 class="ta-page-title">จัดการผู้ใช้</h1>
                    <p class="ta-page-subtitle">ดูสถานะสิทธิ์การใช้งานและจัดการบัญชีผู้ใช้ของระบบทั้งหมด</p>
                </div>
                <div class="ta-page-actions">
                    <a class="btn bg-success" href="{{ route('admin.users.create') }}">
                        <i class="fas fa-plus"></i> เพิ่มรายการ
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="card ta-table-card">
        <div class="card-header">
            <div>
                <h3 class="ta-section-title">รายการผู้ใช้งานระบบ</h3>
                <p class="ta-section-subtitle">สรุปข้อมูลลงทะเบียน บทบาท และสถานะการเข้าใช้ระบบ</p>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>วันที่ลงทะเบียน</th>
                        <th>Username</th>
                        <th>ชื่อ - นามสกุล</th>
                        <th>อีเมล</th>
                        <th>บทบาท</th>
                        <th>สถานะ</th>
                        <th style="width: 10%"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $key => $value)
                        <tr>
                            <td>{{ $key + 1 }}.</td>
                            <td>{{ thaiDateFullmonth($value['created_at']) }}</td>
                            <td>{{ $value['username'] }}</td>
                            <td>{{ $value['name'] . ' ' . $value['last_name'] }}</td>
                            <td>{{ $value['email'] }}</td>
                            <td><span class="badge badge-primary">{{ $value['role_name'] }}</span></td>
                            <td>
                                <span class="badge {{ $value['status'] === 'active' ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $value['status'] }}
                                </span>
                            </td>
                            <td>
                                <div class="ta-table-actions">
                                    <a href="{{ route('admin.users.edit', $value['id']) }}" class="btn bg-info btn-xs">
                                        <i class="fas fa-edit"></i> แก้ไข
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="ta-empty-state">
                                    <div class="ta-empty-state__icon"><i class="fas fa-user-slash"></i></div>
                                    <div>ยังไม่มีผู้ใช้งานในระบบ</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
