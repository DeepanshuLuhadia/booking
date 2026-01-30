<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $vendor = auth()->user()->vendor;
        $employees = $vendor->employees;
        return view('vendor.employees.index', compact('employees', 'vendor'));
    }

    public function create()
    {
        $vendor = auth()->user()->vendor;
        $planLimit = $vendor->subscriptionPlan->max_employees ?? 0;
        
        if ($vendor->employees()->count() >= $planLimit) {
            return redirect()->route('vendor.employees.index')->with('error', "You have reached the maximum employee limit ({$planLimit}) for your plan. Please upgrade to add more.");
        }

        return view('vendor.employees.create');
    }

    public function store(Request $request)
    {
        $vendor = auth()->user()->vendor;
        $planLimit = $vendor->subscriptionPlan->max_employees ?? 0;

        if ($vendor->employees()->count() >= $planLimit) {
            return redirect()->route('vendor.employees.index')->with('error', "Limit reached.");
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'working_start_time' => 'required|date_format:H:i',
            'working_end_time' => 'required|date_format:H:i',
            'slot_duration' => 'required|integer|min:15|max:120',
            'photo' => 'nullable|image|max:1024',
            'service_fee_override' => 'nullable|numeric|min:0',
        ]);

        $data = $request->except('photo');
        $data['vendor_id'] = $vendor->id;

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('employees', 'public');
        }

        Employee::create($data);

        return redirect()->route('vendor.employees.index')->with('success', 'Employee added successfully!');
    }

    public function edit(Employee $employee)
    {
        if ($employee->vendor_id !== auth()->user()->vendor->id) abort(403);
        return view('vendor.employees.edit', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        if ($employee->vendor_id !== auth()->user()->vendor->id) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'working_start_time' => 'required|date_format:H:i',
            'working_end_time' => 'required|date_format:H:i',
            'slot_duration' => 'required|integer|min:15|max:120',
            'photo' => 'nullable|image|max:1024',
            'is_active' => 'required|boolean',
            'service_fee_override' => 'nullable|numeric|min:0',
        ]);

        $data = $request->except('photo');

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('employees', 'public');
        }

        $employee->update($data);

        return redirect()->route('vendor.employees.index')->with('success', 'Employee updated successfully!');
    }

    public function destroy(Employee $employee)
    {
        if ($employee->vendor_id !== auth()->user()->vendor->id) abort(403);
        $employee->delete();
        return redirect()->route('vendor.employees.index')->with('success', 'Employee removed.');
    }
}
