<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FcmTokenController extends Controller
{
    public function save(Request $request)
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

        return response()->json(['success' => true]);
    }
}
