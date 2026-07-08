<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OtpController extends Controller
{
    public function show()
    {
        if (!config('otp.enabled')) {
            return $this->skipVerification();
        }

        return view('auth.verify-otp');
    }

    public function verify(Request $request)
    {
        if (!config('otp.enabled')) {
            return $this->skipVerification();
        }

        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $user = auth()->user();

        // Look at the latest unverified OTP for this user, regardless of the
        // submitted value, so we can count failed attempts against it.
        $otpRecord = Otp::where('identifier', $user->mobile)
            ->where('verified', false)
            ->latest()
            ->first();

        if (!$otpRecord) {
            return back()->withErrors(['otp' => 'No OTP found. Please request a new one.']);
        }

        // Block brute-forcing of the 6-digit code.
        if ($otpRecord->attempts >= 5) {
            return back()->withErrors(['otp' => 'Too many incorrect attempts. Please request a new OTP.']);
        }

        if ($otpRecord->otp !== $request->otp || $otpRecord->expires_at->isPast()) {
            $otpRecord->increment('attempts');
            return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
        }

        $otpRecord->update(['verified' => true]);
        $user->update(['mobile_verified_at' => Carbon::now()]);

        if ($user->role === 'admin') {
            return redirect('/admin/dashboard');
        } elseif ($user->role === 'vendor') {
            return redirect('/vendor/dashboard');
        } elseif ($user->role === 'employee') {
            return redirect('/employee/dashboard');
        }

        return redirect('/');
    }

    public function resend()
    {
        if (!config('otp.enabled')) {
            return $this->skipVerification();
        }

        $user = auth()->user();

        // In real app, send actual SMS/Email here
        $otp = rand(100000, 999999);
        
        Otp::create([
            'identifier' => $user->mobile,
            'otp' => $otp,
            'type' => 'verification',
            'expires_at' => Carbon::now()->addMinutes(5),
        ]);

        return back()->with('success', "A new OTP has been sent to {$user->mobile} (Simulated: {$otp})");
    }

    /**
     * OTP verification is disabled. Treat the mobile as verified and send the
     * user on to their destination without any OTP challenge.
     */
    private function skipVerification()
    {
        $user = auth()->user();

        if ($user && is_null($user->mobile_verified_at)) {
            $user->update(['mobile_verified_at' => Carbon::now()]);
        }

        if ($user && $user->role === 'admin') {
            return redirect('/admin/dashboard');
        } elseif ($user && $user->role === 'vendor') {
            return redirect('/vendor/dashboard');
        } elseif ($user && $user->role === 'employee') {
            return redirect('/employee/dashboard');
        }

        return redirect('/');
    }
}
