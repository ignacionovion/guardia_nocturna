<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CentralAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('central')->check()) {
            return redirect('/admin');
        }

        return view('central.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('central')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect('/admin');
        }

        return back()->withErrors([
            'email' => 'Credenciales incorrectas.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('central')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
