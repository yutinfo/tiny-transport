<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name') }}</title>
    <meta content="width=device-width, initial-scale=1, viewport-fit=cover" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="/plugins/fontawesome-free/css/all.min.css">
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @stack('page_css')
</head>
<body class="driver-shell">
    <main class="driver-app driver-app--tabbar">
        @yield('content')
    </main>
    @include('driver.partials._tabbar')
    <script src="{{ mix('js/app.js') }}"></script>
    @stack('page_scripts')
</body>
</html>
