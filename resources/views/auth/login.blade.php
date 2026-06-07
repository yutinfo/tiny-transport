<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ config('app.name') }} - เข้าสู่ระบบ</title>

    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/plugins/fontawesome-free/css/all.min.css">

    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <link href="/plugins/sweetalert2/sweetalert2.min.css" rel="stylesheet">
</head>
<body class="hold-transition">

<div class="auth-shell">
    <!-- Left Pane: Aside Branding Info (Desktop Only) -->
    <aside class="auth-aside">
        <div class="auth-brand">
            <div class="logo-container">
                <i class="fas fa-truck-moving text-white"></i>
            </div>
            <div class="brand-name">{{ config('app.name') }}</div>
        </div>
        <div class="auth-aside-body">
            <span class="auth-aside-eyebrow">Smart Delivery & Fleet</span>
            <h1>ระบบจัดการขนส่งสินค้าและรอบงานคนขับ</h1>
            <p>ติดตามพัสดุ วางแผนเส้นทางที่มีประสิทธิภาพ และอัปเดตสถานะการจัดส่งได้รวดเร็วทันใจ</p>
            <div class="auth-quote">
                "เครื่องมือจัดการจัดส่งสินค้าที่ดีที่สุด ช่วยเพิ่มความสะดวกและรวดเร็วให้กับคนขับรถในการส่งมอบสินค้าถึงมือผู้รับอย่างแม่นยำ"
                <div class="auth-quote-author">
                    <div class="av">TT</div>
                    <div>Tiny Transport Operations Cockpit</div>
                </div>
            </div>
        </div>
        <div class="auth-aside-footer">
            <span>&copy; {{ date('Y') }} {{ config('app.name') }}</span>
            <span>PROUDLY POWERED BY LARAVEL</span>
        </div>
    </aside>

    <!-- Right Pane: Login Form -->
    <main class="auth-main">
        <div class="auth-card">
            <!-- Mobile Brand Header (Visible on Mobile Only) -->
            <div class="mobile-brand-header">
                <div class="mobile-logo">
                    <i class="fas fa-truck-moving"></i>
                </div>
                <h1>{{ config('app.name') }}</h1>
                <span>ระบบจัดการการจัดส่งสินค้า</span>
            </div>

            <h2>ยินดีต้อนรับกลับมา</h2>
            <p class="sub">กรุณาเข้าสู่ระบบบัญชีผู้ใช้งานของคุณเพื่อดำเนินการต่อ</p>

            <form class="auth-form" method="post" action="{{ route('login.login') }}">
                @csrf

                <div class="field">
                    <label class="field-label" for="username">ชื่อผู้ใช้ (Username)</label>
                    <div class="input-icon">
                        <span class="ico"><i class="fas fa-user"></i></span>
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
                </div>

                <div class="field">
                    <div class="field-row">
                        <label class="field-label" for="password">รหัสผ่าน (Password)</label>
                    </div>
                    <div class="input-icon">
                        <span class="ico"><i class="fas fa-lock"></i></span>
                        <input id="password"
                               class="input @error('password') is-invalid @enderror"
                               type="password"
                               name="password"
                               placeholder="กรอกรหัสผ่านของคุณ"
                               autocomplete="current-password"
                               required>
                    </div>
                </div>

                <div class="check">
                    <input type="checkbox" name="remember" id="remember" checked>
                    <label for="remember">จำการเข้าสู่ระบบของฉัน</label>
                </div>

                <button class="auth-submit" type="submit">
                    เข้าสู่ระบบ
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <!-- Driver Support Helper Card -->
            <div class="driver-helper-card">
                <i class="fas fa-info-circle"></i>
                <div>
                    <strong>สำหรับพนักงานขับรถ (Drivers)</strong>
                    หากคุณพบปัญหาในการเข้าสู่ระบบหรือลืมรหัสผ่าน กรุณาติดต่อฝ่ายควบคุมการจัดส่งเพื่อขอรับความช่วยเหลือ
                </div>
            </div>
        </div>
    </main>
</div>

<script src="{{ mix('js/app.js') }}" defer></script>
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
