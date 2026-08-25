<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $vendor = auth()->user()->vendor;
        $vendorCategories = \App\Models\VendorCategory::all();
        return view('vendor.profile', compact('vendor', 'vendorCategories'));
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
            'address' => 'sometimes|required|string',
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

        return back()->with('success', 'Profile updated successfully!');
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
