<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerRegistrationController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            // Both columns, for the same reason as the vendor form: a number
            // already answering for a business cannot also be a customer's.
            'mobile' => 'required|string|max:15|unique:users,mobile|unique:vendors,contact_number',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.unique'  => 'That email address is already registered. Please sign in instead.',
            'mobile.unique' => 'That mobile number is already registered with us.',
        ]);

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'role' => 'customer',
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'status' => 'active',
        ]);

        // Registered with a password of their own choosing.
        $user->forceFill(['password_set_at' => now()])->save();

        \Illuminate\Support\Facades\Auth::login($user, true);

        return redirect('/')->with('success', 'Registration successful!');
    }
}
