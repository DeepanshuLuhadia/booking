<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Mail\EmployeeCredentialsMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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

        return view('vendor.employees.create', compact('vendor'));
    }

    public function store(Request $request)
    {
        $vendor = auth()->user()->vendor;
        $planLimit = $vendor->subscriptionPlan->max_employees ?? 0;

        if ($vendor->employees()->count() >= $planLimit) {
            return redirect()->route('vendor.employees.index')->with('error', "Limit reached.");
        }

        $this->normalizeWorkingTimes($request);

        $request->validate([
            'name' => 'required|string|max:255',
            'working_start_time' => 'required|date_format:H:i',
            'working_end_time' => 'required|date_format:H:i',
            'slot_duration' => 'required|integer|min:15|max:120',
            'photo' => 'nullable|image|max:1024',
            'service_fee_override' => 'nullable|numeric|min:0',
            'premium_fee' => 'nullable|numeric|min:0',
            'premium_bookings_count' => 'nullable|integer|min:0',
            'max_daily_tokens' => 'nullable|integer|min:1|max:500',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'nullable|string|min:8',
        ]);

        if ($vendor->global_opening_time) {
            $globalStart = \Carbon\Carbon::parse($vendor->global_opening_time)->format('H:i');
            if ($request->working_start_time < $globalStart) {
                return back()->withInput()->withErrors(['working_start_time' => "Start time cannot be earlier than shop opening time ($globalStart)."]);
            }
        }
        
        if ($vendor->global_closing_time) {
            $globalEnd = \Carbon\Carbon::parse($vendor->global_closing_time)->format('H:i');
            if ($request->working_end_time > $globalEnd) {
                return back()->withInput()->withErrors(['working_end_time' => "End time cannot be later than shop closing time ($globalEnd)."]);
            }
        }

        $data = $request->except(['photo', 'email', 'password', '_token']);
        $data['vendor_id'] = $vendor->id;

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('employees', 'public');
        }

        if ($request->filled('email') && $request->filled('password')) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'employee',
                'status' => 'active'
            ]);
            $data['user_id'] = $user->id;

            Mail::to($request->email)->send(new EmployeeCredentialsMail(
                $data['name'],
                $request->email,
                $request->password,
                $vendor->business_name
            ));
        }

        $employee = Employee::create($data);
        app(\App\Services\QRCodeService::class)->generateForEmployee($employee);

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

        $this->normalizeWorkingTimes($request);

        $request->validate([
            'name' => 'required|string|max:255',
            'working_start_time' => 'required|date_format:H:i',
            'working_end_time' => 'required|date_format:H:i',
            'slot_duration' => 'required|integer|min:15|max:120',
            'photo' => 'nullable|image|max:1024',
            'is_active' => 'required|boolean',
            'service_fee_override' => 'nullable|numeric|min:0',
            'premium_fee' => 'nullable|numeric|min:0',
            'premium_bookings_count' => 'nullable|integer|min:0',
            'max_daily_tokens' => 'nullable|integer|min:1|max:500',
            'email' => 'nullable|email|unique:users,email,' . ($employee->user_id ?? 'null'),
            'password' => 'nullable|string|min:8',
        ]);

        $vendor = auth()->user()->vendor;
        if ($vendor->global_opening_time) {
            $globalStart = \Carbon\Carbon::parse($vendor->global_opening_time)->format('H:i');
            if ($request->working_start_time < $globalStart) {
                return back()->withInput()->withErrors(['working_start_time' => "Start time cannot be earlier than shop opening time ($globalStart)."]);
            }
        }
        
        if ($vendor->global_closing_time) {
            $globalEnd = \Carbon\Carbon::parse($vendor->global_closing_time)->format('H:i');
            if ($request->working_end_time > $globalEnd) {
                return back()->withInput()->withErrors(['working_end_time' => "End time cannot be later than shop closing time ($globalEnd)."]);
            }
        }

        $data = $request->except('photo');

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('employees', 'public');
        }

        if ($request->filled('email')) {
            if ($employee->user) {
                $employee->user->update([
                    'name' => $data['name'],
                    'email' => $request->email,
                    'password' => $request->filled('password') ? Hash::make($request->password) : $employee->user->password,
                ]);

                if ($request->filled('password')) {
                    Mail::to($request->email)->send(new EmployeeCredentialsMail(
                        $data['name'],
                        $request->email,
                        $request->password,
                        $vendor->business_name
                    ));
                }
            } else if ($request->filled('password')) {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => 'employee',
                    'status' => 'active'
                ]);
                $data['user_id'] = $user->id;

                Mail::to($request->email)->send(new EmployeeCredentialsMail(
                    $data['name'],
                    $request->email,
                    $request->password,
                    $vendor->business_name
                ));
            }
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

    /**
     * Time inputs may arrive as H:i or H:i:s (the form prefills from the
     * vendor's TIME columns, which serialize with seconds). Normalize both
     * working-time fields to H:i so validation/storage stays consistent.
     */
    private function normalizeWorkingTimes(Request $request): void
    {
        foreach (['working_start_time', 'working_end_time'] as $field) {
            $value = $request->input($field);
            if (!$value) {
                continue;
            }
            try {
                $request->merge([$field => \Carbon\Carbon::parse($value)->format('H:i')]);
            } catch (\Throwable $e) {
                // Leave as-is so validation surfaces the bad value.
            }
        }
    }
}
