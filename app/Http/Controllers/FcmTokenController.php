<?php

namespace App\Http\Controllers;

use App\Services\CustomerBookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FcmTokenController extends Controller
{
    public function save(Request $request, CustomerBookingService $bookings)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        if (Auth::check()) {
            \App\Models\User::where('fcm_token', $request->token)
                ->where('id', '!=', Auth::id())
                ->update(['fcm_token' => null]);
            Auth::user()->update(['fcm_token' => $request->token]);
        } else {
            session(['fcm_token' => $request->token]);
        }

        /*
        | Backfill the bookings this device is already holding.
        |
        | Permission is asked for AFTER the first booking succeeds, so that
        | booking was written with no push address on it — and the shop later
        | completing or cancelling it would then reach nobody. Stamping the token
        | on here closes that window for everything still live.
        */
        $attached = $bookings->attachDeviceToken($request->token, $request);

        return response()->json([
            'success'           => true,
            'bookings_attached' => $attached,
        ]);
    }
}
