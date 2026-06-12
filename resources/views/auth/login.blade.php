<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ config('app.name') }} - เข้าสู่ระบบ</title>

    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <meta name="theme-color" content="#060B18">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Fonts: Noto Sans Thai (Thai body) + Space Grotesk (Latin display) — matches the public tech-blue theme --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">

    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <link href="/plugins/sweetalert2/sweetalert2.min.css" rel="stylesheet">
</head>
<body class="hold-transition">

<div class="auth-shell">
    <!-- Background layers (all CSS/SVG, zero images) — mirrors the tech-blue hero -->
    <div class="auth-bg" aria-hidden="true">
        <div class="auth-glow"></div>
        <svg class="auth-grid" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="login-grid" width="44" height="44" patternUnits="userSpaceOnUse">
                    <path d="M44 0H0V44" fill="none" stroke="rgba(255,255,255,.05)" stroke-width="1"></path>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#login-grid)"></rect>
        </svg>
    </div>

    <main class="auth-main">
        <a class="auth-back" href="{{ url('/') }}">
            <span aria-hidden="true">←</span> กลับหน้าแรก
        </a>

        <div class="auth-card">
            <!-- Brand wordmark — text from config('app.name'), styled like BrandLogo -->
            <div class="auth-brand">
                <span class="auth-mark" aria-hidden="true">
                    <svg viewBox="0 0 28 28" width="28" height="28" fill="none">
                        <rect x="1.5" y="1.5" width="25" height="25" rx="7" stroke="url(#login-bl-grad)" stroke-width="2"></rect>
                        <path d="M8 14.5l3.5 3.5L20 9.5" stroke="url(#login-bl-grad)" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"></path>
                        <defs>
                            <linearGradient id="login-bl-grad" x1="0" y1="0" x2="28" y2="28" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#2563EB"></stop>
                                <stop offset="1" stop-color="#22D3EE"></stop>
                            </linearGradient>
                        </defs>
                    </svg>
                </span>
                <span class="auth-wordmark">{{ config('app.name') }}</span>
            </div>

            <h1 class="auth-title">เข้าสู่ระบบ</h1>
            <p class="auth-sub">สำหรับพนักงานและพนักงานขับรถ — กรุณาเข้าสู่ระบบเพื่อดำเนินการต่อ</p>

            @if (count($errors) >= 1)
                <div class="auth-alert" role="alert">
                    <i class="auth-alert-ico" aria-hidden="true">!</i>
                    <span>Username หรือ Password ไม่ถูกต้อง</span>
                </div>
            @endif

            <form class="auth-form" method="post" action="{{ route('login.login') }}">
                @csrf

                <div class="field">
                    <label class="field-label" for="username">ชื่อผู้ใช้ (Username)</label>
                    <input id="username"
                           class="input @error('username') is-invalid @enderror"
                           type="text"
                           name="username"
                           value="{{ old('username') }}"
                           placeholder="กรอกชื่อผู้ใช้ของคุณ"
                           autocomplete="username"
                           required
                           autofocus>
                </div>

                <div class="field">
                    <label class="field-label" for="password">รหัสผ่าน (Password)</label>
                    <input id="password"
                           class="input @error('password') is-invalid @enderror"
                           type="password"
                           name="password"
                           placeholder="กรอกรหัสผ่านของคุณ"
                           autocomplete="current-password"
                           required>
                </div>

                <div class="check">
                    <input type="checkbox" name="remember" id="remember" checked>
                    <label for="remember">จำการเข้าสู่ระบบของฉัน</label>
                </div>

                <button class="auth-submit" type="submit">
                    เข้าสู่ระบบ
                    <span aria-hidden="true">→</span>
                </button>
            </form>

            <!-- Driver Support Helper Card -->
            <div class="driver-helper-card">
                <i class="auth-helper-ico" aria-hidden="true">i</i>
                <div>
                    <strong>สำหรับพนักงานขับรถ (Drivers)</strong>
                    หากคุณพบปัญหาในการเข้าสู่ระบบหรือลืมรหัสผ่าน กรุณาติดต่อฝ่ายควบคุมการจัดส่งเพื่อขอรับความช่วยเหลือ
                </div>
            </div>
        </div>

        <div class="auth-footer">&copy; {{ date('Y') }} {{ config('app.name') }}</div>
    </main>
</div>

<script src="/plugins/sweetalert2/sweetalert2.min.js"></script>

@if (count($errors)>=1)
<script>
Swal.fire({
  icon: 'error',
  title: 'ไม่สามารถเข้าสู่ระบบได้!',
  text: 'Username หรือ Password ไม่ถูกต้อง',
  confirmButtonColor: '#2563eb'
})
</script>
@endif
</body>
</html>
