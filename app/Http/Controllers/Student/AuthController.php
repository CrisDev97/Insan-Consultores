<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        // Si ya está logueado y es estudiante, directo a su dashboard
        if (auth()->check() && auth()->user()->role === 'estudiante') {
            return redirect()->route('student.dashboard');
        }

        return view('student.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required','email'],
            'password' => ['required'],
        ]);

        $remember = (bool) $request->boolean('remember');

        if (!Auth::attempt($request->only('email','password'), $remember)) {
            return back()->withInput()->with('error', 'Credenciales inválidas.');
        }

        // Solo estudiantes
        if (auth()->user()->role !== 'estudiante') {
            Auth::logout();
            return back()->withInput()->with('error', 'Acceso solo para estudiantes.');
        }

        $request->session()->regenerate();

        return redirect()->route('student.dashboard')->with('success', 'Bienvenido a tu plataforma.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('student.login')->with('success', 'Sesión cerrada.');
    }
}
