<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class InitialPasswordController extends Controller
{
    public function show()
    {
        $user = Auth::guard('web')->user();
        if ($user === null || !$user->password_must_change) {
            return redirect()->route('dashboard');
        }

        return view('auth.password-initial');
    }

    public function update(Request $request)
    {
        $user = Auth::guard('web')->user();
        if ($user === null || !$user->password_must_change) {
            return redirect()->route('dashboard');
        }

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->password = Hash::make($validated['password']);
        $user->password_must_change = false;
        $user->save();

        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Contraseña actualizada correctamente.');
    }
}
