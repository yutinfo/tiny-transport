<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ติดตามพัสดุ — {{ config('app.name') }}</title>
    <meta name="description" content="ติดตามสถานะพัสดุของ {{ config('app.name') }} แบบเรียลไทม์ — กรอกรหัสพัสดุเพื่อเช็กสถานะการจัดส่งได้ตลอด 24 ชั่วโมง">
    <meta name="theme-color" content="#060B18">
    <meta name="robots" content="noindex">

    {{-- Fonts: Noto Sans Thai (Thai body) + Space Grotesk (Latin display) — same as the landing shell. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Noto Sans Thai', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #F8FAFC;
            color: #0F172A;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
        }
        /* First-paint splash so the dark hero never flashes white before Vue mounts. */
        #tracking-app:empty {
            display: block;
            min-height: 100vh;
            background:
                radial-gradient(70% 50% at 80% 0%, rgba(34, 211, 238, .12), transparent 60%),
                linear-gradient(180deg, #060B18 0%, #0A1628 100%);
        }
        .ts-noscript {
            max-width: 640px;
            margin: 0 auto;
            padding: 64px 24px;
            line-height: 1.7;
            text-align: center;
        }
        .ts-noscript a { color: #2563EB; }
    </style>

    {{-- Brand rename contract: the company name is injected here from config only. --}}
    <script>window.__BRAND = @json(['name' => config('app.name')]);</script>
</head>
<body>
    <div id="tracking-app"></div>

    <noscript>
        <div class="ts-noscript">
            <h1>ติดตามพัสดุ — {{ config('app.name') }}</h1>
            <p>กรุณาเปิดใช้งาน JavaScript เพื่อค้นหาและติดตามสถานะพัสดุของคุณ</p>
            <p>กลับสู่ <a href="{{ url('/') }}">หน้าแรก</a> · สำหรับพนักงาน <a href="{{ url('/login') }}">เข้าสู่ระบบ</a></p>
        </div>
    </noscript>

    <script src="{{ asset('js/tracking.js') }}"></script>
</body>
</html>
