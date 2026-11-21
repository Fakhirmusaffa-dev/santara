<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ], [], [
            'email' => 'Email',
            'password' => 'Kata sandi',
        ]);

        $remember = $request->boolean('remember');

        $user = User::where('email', $credentials['email'])->first();

        // Akun nonaktif ditolak sebelum kredensial diperiksa, agar tidak bisa login sama sekali.
        if ($user && ! $user->is_active) {
            ActivityLogger::log('login_failed', "Login ditolak (akun nonaktif): {$credentials['email']}", null, null, [], $request);

            throw ValidationException::withMessages([
                'email' => 'Akun Anda dinonaktifkan. Hubungi admin.',
            ]);
        }

        if (! Auth::attempt($credentials, $remember)) {
            ActivityLogger::log('login_failed', "Percobaan login gagal: {$credentials['email']}", null, null, [], $request);

            return back()->withErrors([
                'email' => 'Email atau kata sandi salah.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();
        $request->user()->forceFill(['last_login_at' => now()])->save();

        ActivityLogger::log('login', 'Login berhasil', null, null, [], $request);

        return redirect()->intended(route($request->user()->dashboardRoute(), absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        ActivityLogger::log('logout', 'Logout', null, null, [], $request);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
