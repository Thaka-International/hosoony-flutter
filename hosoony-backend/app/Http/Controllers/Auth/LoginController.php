<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            if ($user->isStudent()) {
                return redirect()->route('student.dashboard');
            } elseif ($user->isTeacher()) {
                return redirect()->route('teacher.dashboard');
            }
        }
        
        return view('pwa.auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            if ($user->isStudent()) {
                return redirect()->intended(route('student.dashboard'));
            } elseif ($user->isTeacher()) {
                return redirect()->intended(route('teacher.dashboard'));
            }
            
            return redirect()->intended(route('home'));
        }

        throw ValidationException::withMessages([
            'email' => 'بيانات الدخول غير صحيحة.',
        ]);
    }
}