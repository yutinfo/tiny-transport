<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('admin.dashboard') }}" class="brand-link">
        <span class="brand-mark" aria-hidden="true">
            <i class="fas fa-shipping-fast"></i>
        </span>
        <span class="brand-copy">
            <span class="brand-kicker">Admin Console</span>
            <span class="brand-text font-weight-light">{{ config('app.name') }}</span>
        </span>
    </a>

    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                @include('layouts.menu')
            </ul>
        </nav>
    </div>

</aside>
