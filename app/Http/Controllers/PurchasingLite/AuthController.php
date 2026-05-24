<?php

namespace App\Http\Controllers\PurchasingLite;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect('/purchasing-lite/dashboard');
        }

        return view('purchasing-lite.auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Please enter your username.',
            'password.required' => 'Please enter your password.',
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt([
            'username' => $validated['username'],
            'password' => $validated['password'],
            'is_active' => true,
        ], $remember)) {
            return back()
                ->withErrors([
                    'username' => 'Username or password is incorrect.',
                ])
                ->onlyInput('username');
        }

        $request->session()->regenerate();

        return redirect('/purchasing-lite/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/purchasing-lite/login');
    }
}
