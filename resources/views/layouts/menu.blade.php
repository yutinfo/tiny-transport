@php

$get_route_name = Route::current()->getName();
$route_name_arr = explode('.',$get_route_name);
$route_name = $route_name_arr[1];
$role_name = Auth::user()->role_name;

@endphp
@if ($role_name == 'admin')
<li class="nav-item">
    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ $route_name=='dashboard' ? 'active' : '' }}">
        <i class="nav-icon fas fa-tachometer-alt"></i>
        <p>หน้าหลัก</p>
    </a>
</li>
@endif
@if ($role_name=='admin'||$role_name=='staff')
<li class="nav-item">
    <a href="{{ route('admin.orders.index') }}" class="nav-link {{ $route_name=='orders' ? 'active' : '' }}">
        <i class="nav-icon fas fa-th-list"></i>
        <p>รายการออเดอร์</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('admin.contacts.index') }}" class="nav-link {{ $route_name=='contacts' ? 'active' : '' }}">
        <i class="nav-icon fas fa-address-book"></i>
        <p>ข้อมูลผู้ส่ง/ผู้รับ</p>
    </a>
</li>
@endif
@if ($role_name=='admin')
<li class="nav-item">
    <a href="{{ route('admin.users.index') }}" class="nav-link {{ $route_name=='users' ? 'active' : '' }}">
        <i class="nav-icon  fas fa-user-alt"></i>
        <p>ผู้ใช้งาน</p>
    </a>
</li>
@endif
