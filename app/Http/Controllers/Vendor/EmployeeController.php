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

        $messages = [
            'slot_duration.min' => 'Slot duration must be at least 1 minute.',
            'slot_duration.max' => 'Slot duration cannot be longer than 480 minutes (a full working day).',
        ];

        $request->validate([
            'name' => 'required|string|max:255',
            'working_start_time' => 'required|date_format:H:i',
            'working_end_time' => 'required|date_format:H:i',
            /*
            | Any whole number of minutes the shop likes, as long as a slot is
            | a real amount of time. It used to be locked to 15–120 in steps of
            | 15, which shut out real cadences (a 10-minute OPD queue, a
            | 3-hour turf session). The floor matters mechanically as well as
            | commercially: SlotGenerationService steps through the shift by
            | this figure, and a zero would walk in place forever. The ceiling
            | is a sanity bound — one slot can fill a whole working day, but a
            | five-digit typo should not save.
            */
            'slot_duration' => 'required|integer|min:1|max:480',
            'photo' => 'nullable|image|max:1024',
            'service_fee_override' => 'nullable|numeric|min:0',
            'premium_fee' => 'nullable|numeric|min:0',
            'premium_bookings_count' => 'nullable|integer|min:0',
            'max_daily_tokens' => 'nullable|integer|min:1|max:500',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'nullable|string|min:8',
        ], $messages);

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
            $user->forceFill(['password_set_at' => now()])->save();
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

        /*
        | Did this employee just take the shop live?
        |
        | The onboarding prompts walk a new vendor here as their last step, so
        | the moment the listing has nothing left to block on, the panel says
        | so out loud — once. `live_celebrated_at` is the once: it survives
        | log-outs and devices, and the pre-live vendors that existed before it
        | were backfilled, so nobody established is congratulated for nothing.
        */
        $vendor->refresh();
        if (! $vendor->live_celebrated_at && empty($vendor->getListingBlockers())) {
            $vendor->forceFill(['live_celebrated_at' => now()])->save();

            return redirect()->route('vendor.employees.index')
                ->with('business_live', true)
                ->with('success', 'Employee added successfully!');
        }

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

        $messages = [
            'slot_duration.min' => 'Slot duration must be at least 1 minute.',
            'slot_duration.max' => 'Slot duration cannot be longer than 480 minutes (a full working day).',
        ];

        $request->validate([
            'name' => 'required|string|max:255',
            'working_start_time' => 'required|date_format:H:i',
            'working_end_time' => 'required|date_format:H:i',
            /*
            | Any whole number of minutes the shop likes, as long as a slot is
            | a real amount of time. It used to be locked to 15–120 in steps of
            | 15, which shut out real cadences (a 10-minute OPD queue, a
            | 3-hour turf session). The floor matters mechanically as well as
            | commercially: SlotGenerationService steps through the shift by
            | this figure, and a zero would walk in place forever. The ceiling
            | is a sanity bound — one slot can fill a whole working day, but a
            | five-digit typo should not save.
            */
            'slot_duration' => 'required|integer|min:1|max:480',
            'photo' => 'nullable|image|max:1024',
            'is_active' => 'required|boolean',
            'service_fee_override' => 'nullable|numeric|min:0',
            'premium_fee' => 'nullable|numeric|min:0',
            'premium_bookings_count' => 'nullable|integer|min:0',
            'max_daily_tokens' => 'nullable|integer|min:1|max:500',
            'email' => 'nullable|email|unique:users,email,' . ($employee->user_id ?? 'null'),
            'password' => 'nullable|string|min:8',
        ], $messages);

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
                $user->forceFill(['password_set_at' => now()])->save();
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
