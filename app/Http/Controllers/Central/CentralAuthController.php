<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\CentralAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class CentralAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('central')->check()) {
            return redirect('/admin');
        }

        return view('central.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'max:190'],
            'password' => ['required', 'string'],
        ]);

        $this->ensureIsNotRateLimited($request);

        /** @var CentralAdmin|null $admin */
        $admin = CentralAdmin::query()
            ->where('username', $request->string('username')->trim()->toString())
            ->first();

        if ($admin === null || ! Hash::check($request->input('password'), $admin->password)) {
            RateLimiter::hit($this->throttleKey($request), 60);

            throw ValidationException::withMessages([
                'username' => 'Las credenciales no coinciden con nuestros registros.',
            ]);
        }

        if (! $admin->activo) {
            throw ValidationException::withMessages([
                'username' => 'Esta cuenta está desactivada. Contactá a un super administrador.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        Auth::guard('central')->login($admin, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended('/admin');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('central')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * @throws ValidationException
     */
    private function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'username' => "Demasiados intentos. Probá de nuevo en {$seconds} segundos.",
        ]);
    }

    private function throttleKey(Request $request): string
    {
        return Str::lower($request->input('username', '')).'|'.$request->ip();
    }
}
