<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ config('app.name') }}</title>

    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="/plugins/fontawesome-free/css/all.min.css">

    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <link href="/plugins/sweetalert2/sweetalert2.min.css" rel="stylesheet">

</head>
<body class="hold-transition login-page">

<div class="login-box">
    <div class="login-logo">
        <a href="{{ url('/home') }}"><b>{{ config('app.name') }}</b></a>
    </div>
    <!-- /.login-logo -->

    <!-- /.login-box-body -->
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">เข้าสู่ระบบ</p>

            <form method="post" action="{{ route('login.login') }}">
                @csrf

                <div class="input-group mb-3">
                    <input type="username"
                           name="username"
                           value="{{ old('username') }}"
                           placeholder="username"
                           class="form-control @error('username') is-invalid @enderror">
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-envelope"></span></div>
                    </div>

                </div>

                <div class="input-group mb-3">
                    <input type="password"
                           name="password"
                           placeholder="Password"
                           class="form-control @error('password') is-invalid @enderror">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>


                </div>

                <div class="row">
                    <div class="col-8">
                        {{-- <div class="icheck-primary">
                            <input type="checkbox" id="remember">
                            <label for="remember">Remember Me</label>
                        </div> --}}
                    </div>

                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">เข้าสู่ระบบ</button>
                    </div>

                </div>
            </form>


        </div>
        <!-- /.login-card-body -->
    </div>

</div>
<!-- /.login-box -->

<script src="{{ mix('js/app.js') }}" defer></script>
<script src="/plugins/sweetalert2/sweetalert2.min.js"></script>

@if (count($errors)>=1)
<script>

Swal.fire({
  icon: 'error',
  title: 'ไม่สามารถเข้าสู่ระบบได้!',
  text: 'Username หรือ Password ไม่ถูกต้อง',
})
</script>
@endif
</body>
</html>
