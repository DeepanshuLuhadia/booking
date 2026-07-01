<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;

class ApprovalController extends Controller
{
    /**
     * "Approval pending" holding screen for vendors awaiting admin confirmation.
     *
     * This gate only applies while OTP verification is disabled — in that mode
     * admin approval replaces OTP as the entry check for the vendor panel. Once
     * the admin approves the vendor (status 'active') they are sent on to the
     * dashboard.
     */
    public function pending()
    {
        $user = auth()->user();

        // Only vendors have an approval flow; send everyone else to their place.
        if (!$user->isVendor()) {
            return redirect('/');
        }

        $vendor = $user->vendor;

        // If OTP is the active gate, or the vendor is already approved, there is
        // nothing to wait for — hand them back to the panel.
        if (config('otp.enabled') || ($vendor && $vendor->status === 'active')) {
            return redirect()->route('vendor.dashboard');
        }

        return view('vendor.approval-pending', [
            'vendor'      => $vendor,
            'adminEmail'  => config('support.admin_email'),
            'adminPhone'  => config('support.admin_phone'),
        ]);
    }
}
