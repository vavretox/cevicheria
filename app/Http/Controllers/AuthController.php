<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            if (!$user->active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Tu cuenta está desactivada.',
                ]);
            }

            // Redirigir según el rol
            switch ($user->role) {
                case 'admin':
                    return redirect()->route('admin.dashboard');
                case 'cajero':
                    return redirect()->route('cashier.dashboard');
                case 'mesero':
                    return redirect()->route('waiter.dashboard');
                default:
                    return redirect()->route('home');
            }
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
