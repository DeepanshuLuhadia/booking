<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        $vendor = $user->vendor;
        $vendorCategories = \App\Models\VendorCategory::all();

        /*
        | Onboarding, staged — and this screen shows exactly one stage at a time.
        |
        | Stage one is `setupBlockers`: the business details and the map pin,
        | which is why an approved vendor was sent here in the first place
        | (EnsureSubscriptionActive holds them on this page until the details
        | are filled in). Only these, because only these are on the form below.
        | The specialist requirement used to be listed here too, which asked the
        | vendor for something this page has no section for.
        |
        | Stage two is `employeeBlocker`, and it begins on the reload after stage
        | one is saved: a shop with no bookable specialist has no slots and
        | nothing customers can book, so it is now the one thing outstanding, and
        | it is shown as the blocker it is — pointing at the staff section where
        | it can actually be dealt with. It reads the same check the going-live
        | celebration does, so the panel cannot claim the shop is ready while the
        | banner still says otherwise.
        |
        | The two stages are mutually exclusive by construction, so the page
        | never asks for the next step while the current one is still open.
        |
        | `needsFirstEmployee` is the narrower one: it drives the modal, whose
        | checklist deep-links into the *create* form, so it asks only when there
        | is genuinely no specialist yet. A shop whose only specialist is off
        | duty or unpriced is told by the banner instead — it has one to fix, not
        | one to add.
        */
        $setupBlockers      = $vendor->getProfileBlockers();
        $profileIncomplete  = ! empty($setupBlockers);
        $employeeBlocker    = ! $profileIncomplete && ! $vendor->hasBookableEmployee();
        $needsFirstEmployee = $employeeBlocker && $vendor->employees()->count() === 0;

        return view('vendor.profile', compact(
            'vendor',
            'vendorCategories',
            'user',
            'setupBlockers',
            'profileIncomplete',
            'employeeBlocker',
            'needsFirstEmployee'
        ));
    }

    public function update(Request $request)
    {
        $vendor = auth()->user()->vendor;

        /*
        | Coordinates are mandatory: the customer listing ranks nearest-first
        | from the visitor's GPS against these, so a shop without them shows no
        | distance and sinks below every located competitor.
        |
        | A literal 0 is rejected as well as a missing value. The listing treats
        | |coord| < 0.00001 as "unset" (see CustomerDiscoveryController::
        | coordinate), so accepting a zero here would let a vendor save a figure
        | the ranking then silently ignores — the worst of both worlds.
        */
        $rejectNullIsland = function ($attribute, $value, $fail) {
            if (is_numeric($value) && abs((float) $value) < 0.00001) {
                $fail('The ' . $attribute . ' looks unset. Use "Use My Location" or enter the real coordinate.');
            }
        };

        // Whether this save is switching direct advances ON. Read once: it
        // decides three sets of rules below and reading it per-field would let
        // them drift apart.
        $takingAdvances = $request->boolean('is_direct_payment_enabled');

        $request->validate([
            'business_name' => 'sometimes|required|string|min:5|max:255',
            'owner_name' => 'sometimes|required|string|min:5|max:255',
            /*
            | One phone number, one business — checked against both columns it
            | lives in, ignoring this shop's own row so re-saving an unchanged
            | form is not a conflict with itself. `vendors.contact_number`
            | carries a unique index of its own, so this is the message that
            | stands between the vendor and a constraint violation.
            */
            'contact_number' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                'unique:users,mobile,' . $vendor->user_id,
                'unique:vendors,contact_number,' . $vendor->id,
            ],
            'show_contact_number' => 'nullable|boolean',
            'require_customer_details' => 'nullable|boolean',
            /*
            | Optional, because the coordinates below are not.
            |
            | A shop that has pinned itself on the map has said where it is far
            | more precisely than a line of text can, and the customer page
            | turns the blank into a "Go to Map" link off those same figures.
            | `required_without_all` is the floor rather than the rule: it only
            | bites if coordinates ever stop being mandatory, so a vendor can
            | never end up with no location at all.
            */
            'address' => ['nullable', 'string', 'max:1000', 'required_without_all:latitude,longitude'],
            'latitude'  => ['required', 'numeric', 'between:-90,90', $rejectNullIsland],
            'longitude' => ['required', 'numeric', 'between:-180,180', $rejectNullIsland],
            'shop_photo' => 'nullable|image|max:2048',

            /*
            | Direct-to-vendor UPI payments.
            |
            | The UPI ID is the one field the toggle genuinely depends on:
            | turning direct payment on without a payable destination produces a
            | customer-facing payment screen with nobody to pay — a dead end
            | they cannot get out of, which reads as the shop having taken their
            | booking and vanished. So it becomes required, and is held to the
            | VPA shape, the moment the toggle is on.
            |
            | `upi_id` predates this feature as a free-text settlement note, so
            | outside that it stays optional and unvalidated — an existing shop
            | with "ask at counter" in the column must not be blocked from
            | saving unrelated profile changes.
            |
            | The advance is ALWAYS optional. Leaving it empty is a real
            | configuration, not an incomplete one: it means "charge the full
            | booking amount up front" rather than taking a deposit. Only the
            | ceiling and a non-negative floor are enforced.
            */
            'is_direct_payment_enabled' => 'nullable|boolean',
            'upi_id' => array_merge(
                $takingAdvances
                    ? ['required', 'regex:' . \App\Services\UpiPaymentService::VPA_PATTERN]
                    : ['nullable'],
                ['string', 'max:255']
            ),
            'upi_name' => 'nullable|string|max:100',
            // decimal(8,2): anything larger will not fit the column.
            'advance_amount' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'vendor_type' => 'nullable|exists:vendor_categories,slug',
            'appointment_mode' => 'nullable|in:time_slot,token',
            'global_opening_time' => 'nullable|date_format:H:i',
            'global_closing_time' => 'nullable|date_format:H:i',
        ], [
            'contact_number.unique' => 'That mobile number is already registered to another account. Please use a different one.',
            'address.required_without_all' => 'Enter the shop address, or set the location on the map.',
            'latitude.required'  => 'Shop location is required — tap "Use My Location" to fill the coordinates.',
            'longitude.required' => 'Shop location is required — tap "Use My Location" to fill the coordinates.',
            'latitude.between'   => 'Latitude must be between -90 and 90.',
            'longitude.between'  => 'Longitude must be between -180 and 180.',
            'upi_id.required'       => 'Enter your UPI ID before enabling direct payments — customers need somewhere to send the money.',
            'upi_id.regex'          => 'That does not look like a UPI ID. It should read like name@bank (for example clinic@okaxis).',
            'advance_amount.min'    => 'The advance cannot be negative. Leave it empty to charge the full booking amount.',
        ]);

        $data = $request->except(['shop_photo', 'token_amount', 'service_fee', 'emergency_fee', 'avg_consultation_time']);
        $data['show_contact_number'] = $request->has('show_contact_number') ? 1 : 0;

        // The form pairs the checkbox with a hidden "0", so an unticked box still
        // posts a value — read it as a boolean rather than by presence.
        $data['require_customer_details'] = $request->boolean('require_customer_details') ? 1 : 0;

        /*
        | Direct-to-vendor UPI advances.
        |
        | Same hidden-0 pairing as the checkbox above. The fee is normalised to
        | a real number rather than left as an empty string, because a blank
        | would be written to a decimal column as 0.00 anyway and the toggle
        | reads that as "not set up" — so it may as well say so honestly.
        */
        $data['is_direct_payment_enabled'] = $takingAdvances ? 1 : 0;
        $data['advance_amount'] = round((float) $request->input('advance_amount', 0), 2);
        // An empty textarea posts "" — stored as NULL so `filled()` on the model
        // and `trim()` on the customer page agree about what "no address" means.
        $data['address'] = trim((string) $request->input('address')) ?: null;
        $data['upi_id'] = trim((string) $request->input('upi_id')) ?: null;
        $data['upi_name'] = trim((string) $request->input('upi_name')) ?: null;


        if ($request->hasFile('shop_photo')) {
            if ($vendor->shop_photo) {
                Storage::disk('public')->delete($vendor->shop_photo);
            }
            $data['shop_photo'] = $request->file('shop_photo')->store('shops', 'public');
        }

        $vendor->update($data);

        // Sync updates to User record
        $userUpdates = [];
        if ($request->filled('owner_name')) {
            $userUpdates['name'] = $request->owner_name;
        }
        if ($request->filled('contact_number')) {
            $userUpdates['mobile'] = $request->contact_number;
        }
        if (!empty($userUpdates)) {
            $vendor->user->update($userUpdates);
        }

        // The mirror of the check in EmployeeController::store — a vendor who
        // added their specialist first goes live on THIS save instead, and the
        // celebration belongs to whichever step actually finished the job.
        $vendor->refresh();
        if (! $vendor->live_celebrated_at && empty($vendor->getListingBlockers())) {
            $vendor->forceFill(['live_celebrated_at' => now()])->save();

            return back()
                ->with('business_live', true)
                ->with('success', 'Profile updated successfully!');
        }

        return back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Set or change the password on the vendor's own account — the second half
     * of the two-way login.
     *
     * A shop that registered with Google has no password it knows: the column
     * holds a random string and `password_set_at` is null. Those accounts set
     * their first password here without being asked for a current one, which
     * they could never supply. Everyone else must prove the current password
     * before changing it, so a borrowed session cannot quietly take the
     * account over.
     */
    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'password' => ['required', 'confirmed', Password::min(8)],
        ];

        // Only meaningful when there is a password to prove. `current_password`
        // checks against the signed-in user's hash.
        if ($user->hasPassword()) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $request->validate($rules, [
            'current_password.required'      => 'Enter your current password to change it.',
            'current_password.current_password' => 'That is not your current password.',
            'password.confirmed'             => 'The two passwords do not match.',
        ]);

        // forceFill: `password_set_at` is deliberately outside $fillable, and
        // it is what tells this screen (and the next visit) that the account
        // now has a password of its owner's choosing.
        $user->forceFill([
            'password'        => Hash::make($request->input('password')),
            'password_set_at' => now(),
        ])->save();

        $message = $user->usesGoogleSignIn()
            ? 'Password saved. You can now sign in with Google or with your email and password.'
            : 'Password updated successfully.';

        return back()->with('success', $message);
    }

    /**
     * The live QR preview on the direct-payment settings card.
     *
     * Built from what is currently *typed into the form*, not from what is
     * saved, so a shop can see exactly what its customers will scan before
     * committing to it — the point of the preview is catching a mistyped VPA,
     * and a preview of the saved value could not do that.
     *
     * Rendered server-side because the QR has to encode the same NPCI string
     * the customer's screen will use, `mam` included. Generating it in the
     * browser would mean a second implementation of the deep link, and the two
     * would eventually disagree about the amount lock.
     */
    public function upiQrPreview(Request $request, \App\Services\UpiPaymentService $upi)
    {
        $request->validate([
            'upi_id'         => 'nullable|string|max:255',
            'upi_name'       => 'nullable|string|max:100',
            'advance_amount' => 'nullable|numeric|min:0|max:999999.99',
        ]);

        $link = $upi->previewLink(
            $request->input('upi_id'),
            $request->input('upi_name'),
            $request->input('advance_amount')
        );

        if (! $link) {
            return response()->json([
                'ok'      => false,
                // Shown verbatim under the empty QR frame, so it has to name
                // what is missing rather than just refuse.
                'message' => 'Enter a valid UPI ID (name@bank) and an amount above ₹0 to see the QR code.',
            ]);
        }

        return response()->json([
            'ok'     => true,
            'link'   => $link,
            'svg'    => $upi->qrSvg($link, 220),
            'amount' => $upi->formatAmount($request->input('advance_amount')),
        ]);
    }

    public function plans()
    {
        $vendor = auth()->user()->vendor;
        $plans = \App\Models\SubscriptionPlan::where('is_active', true)->where('price', '>', 0)->get();
        return view('vendor.subscription.plans', compact('vendor', 'plans'));
    }

    public function toggleStatus(Request $request)
    {
        $request->validate(['type' => 'required|in:open,pause,close']);
        $vendor = auth()->user()->vendor;

        if (!$vendor->isProfileComplete()) {
            return back()->with('error', 'Please complete your global settings before activating the shop.');
        }

        match ($request->type) {
            'open'  => $vendor->update(['is_open' => true,  'bookings_paused' => false]),
            'pause' => $vendor->update(['bookings_paused' => true]),
            'close' => $vendor->update(['is_open' => false, 'bookings_paused' => false]),
        };

        // Every customer sitting on this shop's page follows the change live, and
        // closing pushes to anyone still holding a token — otherwise they wait
        // for a turn that is no longer coming.
        app(\App\Services\BookingNotifier::class)->shopStatusChanged($vendor, $request->type);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        $messages = ['open' => 'Shop is now Open.', 'pause' => 'Bookings paused.', 'close' => 'Shop closed for today.'];
        return back()->with('success', $messages[$request->type]);
    }
}
