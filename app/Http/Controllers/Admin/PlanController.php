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
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'max_employees' => 'required|integer|min:1',
            'features' => 'required|array',
        ]);

        SubscriptionPlan::create($request->all());

        return redirect()->route('admin.dashboard')->with('success', 'Plan created successfully');
    }

    public function edit(SubscriptionPlan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, SubscriptionPlan $plan)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'max_employees' => 'required|integer|min:1',
            'features' => 'required|array',
        ]);

        $plan->update($request->all());

        return redirect()->route('admin.dashboard')->with('success', 'Plan updated successfully');
    }

    public function destroy(SubscriptionPlan $plan)
    {
        $plan->delete();
        return redirect()->route('admin.dashboard')->with('success', 'Plan deleted successfully');
    }
}
