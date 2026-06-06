<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $this->ensureIsNotRateLimited($request);

        $credentials = $request->getCredentials();
        if(!Auth::validate($credentials)):
            RateLimiter::hit($this->throttleKey($request));

            return redirect()->to('/login')
                ->withErrors(trans('auth.failed'));
        endif;

        $user = Auth::getProvider()->retrieveByCredentials($credentials);
        if($user->status !== 'active'){
            RateLimiter::hit($this->throttleKey($request));

            return redirect()->to('/login')
            ->withErrors(trans('auth.failed'));
        }

        Auth::login($user);
        $request->session()->regenerate();
        RateLimiter::clear($this->throttleKey($request));


        return $this->authenticated($request, $user);
    }

    protected function authenticated(Request $request, $user)
    {
        if($user->role_name=='admin'){
            return redirect()->intended();
        }else{
            return redirect()->route('admin.orders.create');
        }

    }

    protected function ensureIsNotRateLimited(LoginRequest $request)
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'username' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey(LoginRequest $request)
    {
        return Str::lower($request->input('username')).'|'.$request->ip();
    }

}
