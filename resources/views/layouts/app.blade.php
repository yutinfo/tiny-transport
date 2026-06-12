<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name') }}</title>
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;500;600;700&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/plugins/fontawesome-free/css/all.min.css">

    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <link href="/plugins/sweetalert2/sweetalert2.min.css" rel="stylesheet">

    @yield('third_party_stylesheets')

    @stack('page_css')
</head>

<body class="hold-transition sidebar-mini layout-fixed ta-admin-shell">
<div class="wrapper">

    <!-- Main Header -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button" aria-label="Toggle navigation">
                    <i class="fas fa-bars" aria-hidden="true"></i>
                </a>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto align-items-center">
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="brand-mark mr-2" aria-hidden="true">
                        <i class="fas fa-user"></i>
                    </span>
                    <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <li class="user-header bg-primary">
                        <span class="brand-mark mb-3" aria-hidden="true">
                            <i class="fas fa-user"></i>
                        </span>
                        <p>
                            {{ Auth::user()->name }}   {{ Auth::user()->last_name }}
                            <small>บทบาท {{ Auth::user()->role_name }}</small>
                        </p>
                    </li>
                    <li class="user-footer">
                        <a href="#" class="btn btn-default btn-flat float-right"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                            ออกจากระบบ
                        </a>
                        <form id="logout-form" action="{{ route('login.logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>

    <!-- Left side column. contains the logo and sidebar -->
@include('layouts.sidebar')

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <section class="content pt-3" id="main-content">
            @yield('content')
        </section>
    </div>

    <!-- Main Footer -->
    <footer class="main-footer">
        <div class="float-right d-none d-sm-block">
            <b>Version</b> {{env('APP_VERSION')}}
        </div>
        <strong></strong>

    </footer>
</div>

<script src="{{ mix('js/app.js') }}"></script>
<script src="/plugins/sweetalert2/sweetalert2.min.js"></script>
@yield('third_party_scripts')

@stack('page_scripts')
</body>
</html>
