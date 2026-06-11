<nav class="driver-tabbar" role="navigation" aria-label="เมนูหลัก">
    <a href="{{ route('driver.trips.history') }}"
       class="driver-tab {{ request()->routeIs('driver.trips.history') ? 'is-active' : '' }}">
        <i class="fas fa-history"></i>
        <span>ประวัติ</span>
    </a>
    <a href="{{ route('driver.dashboard') }}"
       class="driver-tab {{ request()->routeIs('driver.dashboard') || request()->routeIs('driver.trips.show') ? 'is-active' : '' }}">
        <i class="fas fa-truck-moving"></i>
        <span>งาน</span>
    </a>
    <a href="{{ route('driver.profile') }}"
       class="driver-tab {{ request()->routeIs('driver.profile') ? 'is-active' : '' }}">
        <i class="fas fa-user"></i>
        <span>โปรไฟล์</span>
    </a>
</nav>
