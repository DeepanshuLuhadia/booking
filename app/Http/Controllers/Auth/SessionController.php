<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $attributes = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Always remembered: a signed-in user stays signed in until they press
        // Logout. The long session lifetime does most of the work; the remember
        // cookie is the backstop for when the session cookie or row is gone.
        if (!Auth::attempt($attributes, true)) {
            throw ValidationException::withMessages([
                'email' => 'Your provided credentials could not be verified.'
            ]);
        }

        session()->regenerate();

        $user = auth()->user();
        if ($user->isAdmin()) {
            return redirect('/admin/dashboard');
        } elseif ($user->isVendor()) {
            return redirect('/vendor/dashboard');
        } elseif ($user->isEmployee()) {
            return redirect('/employee/dashboard');
        }

        return redirect('/');
    }

    public function destroy()
    {
        Auth::logout();
        return redirect('/');
    }
}
