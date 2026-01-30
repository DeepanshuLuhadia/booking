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
            'email' => 'required|string|email|max:255|unique:users',
            'mobile' => 'required|string|max:15|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'role' => 'customer',
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'status' => 'active',
        ]);

        \Illuminate\Support\Facades\Auth::login($user);

        return redirect('/')->with('success', 'Registration successful!');
    }
}
