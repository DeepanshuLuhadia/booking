<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

/**
 * "Forgot password" — step one: email a signed reset link.
 */
class PasswordResetLinkController extends Controller
{
    public function create()
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink($request->only('email'));

        // Never confirm or deny that an address is registered: the same
        // response either way, so this endpoint cannot be used to enumerate
        // which emails hold accounts. Only a throttle response is surfaced.
        if ($status === Password::RESET_THROTTLED) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }

        return back()->with('status', 'If that email is registered with us, a password reset link is on its way. Please check your inbox and spam folder.');
    }
}
