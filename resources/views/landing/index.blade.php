<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ config('app.name') }} — ขนส่งพัสดุด่วนทั่วไทย</title>
    <meta name="description" content="{{ config('app.name') }} บริการขนส่งพัสดุด่วน เก็บเงินปลายทาง (COD) ติดตามสถานะแบบเรียลไทม์ ครอบคลุมทุกพื้นที่ทั่วประเทศไทย">
    <meta name="theme-color" content="#060B18">
    <link rel="canonical" href="{{ url('/') }}">

    {{-- Open Graph / social --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:title" content="{{ config('app.name') }} — ขนส่งพัสดุด่วนทั่วไทย">
    <meta property="og:description" content="บริการขนส่งพัสดุด่วน เก็บเงินปลายทาง ติดตามสถานะแบบเรียลไทม์ ครอบคลุมทุกพื้นที่">
    <meta property="og:url" content="{{ url('/') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ config('app.name') }} — ขนส่งพัสดุด่วนทั่วไทย">
    <meta name="twitter:description" content="บริการขนส่งพัสดุด่วน เก็บเงินปลายทาง ติดตามสถานะแบบเรียลไทม์ ครอบคลุมทุกพื้นที่">

    {{-- Fonts: Noto Sans Thai (Thai body) + Space Grotesk (Latin display) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Noto Sans Thai', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #060B18;
            color: #0F172A;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
        }
        /* First-paint splash so the dark hero never flashes white before Vue mounts */
        #landing-app:empty {
            display: block;
            min-height: 100vh;
            background:
                radial-gradient(60% 50% at 80% 0%, rgba(34, 211, 238, .12), transparent 60%),
                linear-gradient(180deg, #060B18 0%, #0A1628 100%);
        }
        .ls-noscript {
            max-width: 640px;
            margin: 0 auto;
            padding: 64px 24px;
            color: #E2E8F0;
            line-height: 1.7;
            text-align: center;
        }
        .ls-noscript a { color: #22D3EE; }
    </style>

    {{-- Brand rename contract: the company name is injected here from config only. --}}
    <script>window.__BRAND = @json(['name' => config('app.name')]);</script>
</head>
<body>
    <div id="landing-app"></div>

    <noscript>
        <div class="ls-noscript">
            <h1>{{ config('app.name') }}</h1>
            <p>บริการขนส่งพัสดุด่วน เก็บเงินปลายทาง (COD) และติดตามสถานะพัสดุแบบเรียลไทม์ ครอบคลุมทุกพื้นที่ทั่วประเทศไทย</p>
            <p>ติดตามพัสดุของคุณได้ที่ <a href="{{ url('/tracking') }}">{{ url('/tracking') }}</a></p>
            <p>สำหรับพนักงาน <a href="{{ url('/login') }}">เข้าสู่ระบบ</a></p>
        </div>
    </noscript>

    <script src="{{ asset('js/landing.js') }}"></script>
</body>
</html>
