<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;

class ApprovalController extends Controller
{
    /**
     * Holding screen for vendors & employees when vendor account is pending, rejected, or suspended.
     */
    public function pending()
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $vendor = null;
        $isEmployee = false;

        if ($user->isVendor()) {
            $vendor = $user->vendor;
        } elseif ($user->isEmployee()) {
            $isEmployee = true;
            $vendor = $user->employee?->vendor;
        } else {
            return redirect('/');
        }

        // If the vendor account is active, send vendor/employee to their respective dashboard
        if ($vendor && $vendor->status === 'active') {
            if ($user->isVendor()) {
                return redirect()->route('vendor.dashboard');
            } elseif ($user->isEmployee()) {
                return redirect()->route('employee.dashboard');
            }
        }

        $status = $vendor ? $vendor->status : 'pending';

        return view('vendor.approval-pending', [
            'vendor'      => $vendor,
            'status'      => $status,
            'isEmployee'  => $isEmployee,
            'user'        => $user,
            'adminEmail'  => config('support.admin_email'),
            'adminPhone'  => config('support.admin_phone'),
        ]);
    }
}
