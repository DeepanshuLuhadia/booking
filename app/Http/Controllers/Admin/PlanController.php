<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::all();
        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.plans.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['features'] = $this->cleanFeatures($request);

        SubscriptionPlan::create($data);

        return redirect()->route('admin.dashboard')->with('success', 'Plan created successfully');
    }

    public function edit(SubscriptionPlan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, SubscriptionPlan $plan)
    {
        $data = $this->validated($request);
        $data['features'] = $this->cleanFeatures($request);

        // Razorpay Plans are immutable — a price or period change can't be
        // applied to the one already created, so drop the cached id and let
        // getOrCreateRazorpayPlan() mint a fresh one on the next checkout.
        // Vendors already subscribed keep charging against the OLD Razorpay
        // Plan at the old price/period until they explicitly re-subscribe;
        // this only affects new checkouts from this point on.
        if ((float) $data['price'] !== (float) $plan->price || $data['billing_period'] !== $plan->billing_period) {
            $data['razorpay_plan_id'] = null;
        }

        $plan->update($data);

        return redirect()->route('admin.dashboard')->with('success', 'Plan updated successfully');
    }

    /** Hides a plan from new signups/upgrades without touching vendors already on it. */
    public function toggleActive(SubscriptionPlan $plan)
    {
        $plan->update(['is_active' => !$plan->is_active]);

        $message = $plan->is_active
            ? "{$plan->name} is now visible to vendors."
            : "{$plan->name} is hidden — existing subscribers are unaffected.";

        return redirect()->route('admin.dashboard')->with('success', $message);
    }

    public function destroy(SubscriptionPlan $plan)
    {
        if ($plan->vendors()->exists()) {
            return redirect()->route('admin.dashboard')->with('error', "Can't delete {$plan->name} — vendors are still on it. Deactivate it instead to hide it from new signups.");
        }

        $plan->delete();
        return redirect()->route('admin.dashboard')->with('success', 'Plan deleted successfully');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'max_employees' => 'required|integer|min:1',
            'features' => 'required|array',
            'billing_period' => 'required|in:monthly,yearly',
        ]);

        // An unchecked checkbox is simply absent from the request, not "false" —
        // read it from the request directly rather than validating its presence.
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    /** The features repeater posts its empty rows too — drop them so the stored list holds only real feature lines. */
    private function cleanFeatures(Request $request): array
    {
        return array_values(array_filter($request->input('features', []), fn ($f) => filled($f)));
    }
}
