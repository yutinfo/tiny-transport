<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name') }}</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link rel="stylesheet" href="/plugins/fontawesome-free/css/all.min.css">

    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <link href="/plugins/sweetalert2/sweetalert2.min.css" rel="stylesheet">

    @yield('third_party_stylesheets')

    @stack('page_css')
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Main Header -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                    <i class="fa-2x fas fa-user-circle"></i>

                    <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <!-- User image -->
                    <li class="user-header bg-primary">
                        <i class="fa-5x fas fa-user-circle"></i>
                        <p>

                            {{ Auth::user()->name }}   {{ Auth::user()->last_name }}
                            <small>บทบาท {{ Auth::user()->role_name }}</small>
                        </p>
                    </li>
                    <!-- Menu Footer-->
                    <li class="user-footer">

                        <a href="#" class="btn btn-default btn-flat float-right"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Sign out
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
        <section class="content">
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
