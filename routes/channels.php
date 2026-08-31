<?php

use App\Models\Employee;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*
|--------------------------------------------------------------------------
| Shop Feed — private
|--------------------------------------------------------------------------
| Everything happening at one business: bookings arriving, being completed,
| cancelled, skipped. Carries customer names and phone numbers, so it is
| restricted to the people who run the shop — the owner, and the specialists
| who work there (an employee's dashboard needs the same live feed).
*/
Broadcast::channel('vendor.{vendorId}', function ($user, $vendorId) {
    $vendorId = (int) $vendorId;

    if ($user->vendor && (int) $user->vendor->id === $vendorId) {
        return true;
    }

    return (bool) ($user->employee && (int) $user->employee->vendor_id === $vendorId);
});

/*
|--------------------------------------------------------------------------
| One Specialist's Feed — private
|--------------------------------------------------------------------------
| The employee themselves, plus the owner of the shop they work at — the
| vendor dashboard shows per-specialist queues and must follow them too.
*/
Broadcast::channel('employee.{employeeId}', function ($user, $employeeId) {
    $employee = Employee::find($employeeId);

    if (!$employee) {
        return false;
    }

    if ($user->employee && (int) $user->employee->id === (int) $employee->id) {
        return true;
    }

    return (bool) ($user->vendor && (int) $user->vendor->id === (int) $employee->vendor_id);
});

/*
|--------------------------------------------------------------------------
| Public queue channels
|--------------------------------------------------------------------------
| queue.{employeeId} and shop.{vendorId} are deliberately PUBLIC and are not
| declared here — customers are guests with no account to authorise against,
| and what they watch (now serving, tokens issued, is the shop open) is
| already public through /vendors/{slug}/queue-status.
|
| Nothing personal may ever be published on them. See QueueUpdated and
| ShopStatusChanged: they carry counters and a token number only — never a
| customer name, a phone number, or any other identifying detail.
*/
