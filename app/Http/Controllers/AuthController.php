<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Authenticate the user.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:3|max:6',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Login success',
                    'user' => $user,
                ]);
            }

            if ($user->role && $user->role->name === 'cabang') {
                return redirect()->intended('/cabang/dashboard');
            }

            if ($user->role && $user->role->name === 'outlet') {
                return redirect()->intended('/outlet/dashboard');
            }

            return redirect()->intended('/admin/dashboard');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        return redirect()->back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => 'Email atau password salah.',
            ]);
    }

    /**
     * Get the authenticated user.
     */
    public function me()
    {
        return response()->json([
            'user' => Auth::user(),
        ]);
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Logout success',
            ]);
        }

        return redirect('/');
    }
}
