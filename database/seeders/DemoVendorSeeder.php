<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorCategory;
use App\Services\ShiftService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Demo catalogue: 50 active vendors, each with 1–2 active employees.
 *
 * The listing only shows a vendor when BOTH gates in
 * CustomerDiscoveryController pass — the shop's own global window contains
 * "now", AND at least one non-paused employee is working inside that window.
 * So the opening hours here are not random: they follow a fixed plan
 * (self::shiftPlan()) chosen so that at every instant of the 24h clock at
 * least 30 of these vendors are open and bookable. Everything else — names,
 * owners, addresses, fees — comes from Faker.
 *
 * Employees always cover their vendor's whole window: a single employee gets
 * the full window, two employees split it at the midpoint (so their working
 * hours differ but leave no uncovered gap).
 *
 * Re-runnable: rows are keyed on the demo e-mail addresses (@obai.test), so
 * seeding twice updates the same 50 vendors instead of creating 100.
 *
 *   php artisan db:seed --class=DemoVendorSeeder
 */
class DemoVendorSeeder extends Seeder
{
    /** Vendors trading around the clock (one-second daily gap, staggered). */
    private const ROUND_THE_CLOCK = 20;

    /** Day-shift windows: [open, close, how many vendors]. */
    private const DAY_WINDOWS = [
        ['06:00:00', '22:00:00', 10],
        ['09:00:00', '01:00:00', 6],
        ['12:00:00', '06:00:00', 6],
        ['16:00:00', '08:00:00', 4],
        ['18:00:00', '10:00:00', 4],
    ];

    /** Mobile-number blocks reserved for demo data. */
    private const VENDOR_MOBILE_BASE = 7100000000;
    private const EMPLOYEE_MOBILE_BASE = 7200000000;

    /** Business-name flavour per category slug. */
    private const NAME_PARTS = [
        'barber' => ['Hair Studio', 'Salon & Spa', 'Grooming Lounge', 'Barber House', 'Style Bar'],
        'doctor' => ['Clinic', 'Health Care', 'Poly Clinic', 'Medical Centre', 'Dental Care'],
        'consultant' => ['Advisory', 'Consulting', 'Legal Associates', 'Tax Chambers', 'Financial Services'],
        'activity' => ['Turf & Sports', 'Fitness Club', 'Sports Arena', 'Yoga Studio', 'Gym & Wellness'],
        'training' => ['Academy', 'Institute', 'Learning Hub', 'Coaching Classes', 'Skill Centre'],
    ];

    /** Cities with real coordinates so the distance chip has something to work with. */
    private const CITIES = [
        ['Indore', 'Madhya Pradesh', 22.7196, 75.8577],
        ['Bhopal', 'Madhya Pradesh', 23.2599, 77.4126],
        ['Jaipur', 'Rajasthan', 26.9124, 75.7873],
        ['Pune', 'Maharashtra', 18.5204, 73.8567],
        ['Mumbai', 'Maharashtra', 19.0760, 72.8777],
        ['New Delhi', 'Delhi', 28.6139, 77.2090],
        ['Bengaluru', 'Karnataka', 12.9716, 77.5946],
        ['Hyderabad', 'Telangana', 17.3850, 78.4867],
        ['Ahmedabad', 'Gujarat', 23.0225, 72.5714],
        ['Lucknow', 'Uttar Pradesh', 26.8467, 80.9462],
        ['Chandigarh', 'Chandigarh', 30.7333, 76.7794],
        ['Nagpur', 'Maharashtra', 21.1458, 79.0882],
    ];

    public function run(): void
    {
        $faker = fake('en_IN');

        $categories = VendorCategory::orderBy('id')->get();
        if ($categories->isEmpty()) {
            $this->command?->error('No vendor categories found — seed vendor_categories first.');
            return;
        }

        $plans = SubscriptionPlan::where('price', '>', 0)->orderBy('price')->get();
        if ($plans->isEmpty()) {
            $this->command?->error('No paid subscription plans found — run InitialDataSeeder first.');
            return;
        }

        $windows = $this->shiftPlan();
        $vendorIds = [];

        foreach ($windows as $i => $window) {
            [$open, $close] = $window;

            $category = $categories[$i % $categories->count()];
            $city = self::CITIES[$i % count(self::CITIES)];
            $plan = $plans[$i % $plans->count()];

            $ownerName = $faker->name();
            $businessName = $faker->lastName() . ' ' . $faker->randomElement(
                self::NAME_PARTS[$category->slug] ?? ['Services']
            );

            $vendorUser = User::updateOrCreate(
                ['email' => sprintf('demo.vendor%02d@obai.test', $i + 1)],
                [
                    'name' => $ownerName,
                    'mobile' => (string) (self::VENDOR_MOBILE_BASE + $i + 1),
                    'role' => 'vendor',
                    'status' => 'active',
                    'password' => Hash::make('password'),
                    'mobile_verified_at' => now(),
                ]
            );

            $serviceFee = $faker->numberBetween(3, 20) * 50; // ₹150 – ₹1000
            $mode = $faker->randomElement(['time_slot', 'time_slot', 'time_slot', 'hybrid', 'token']);

            $vendor = Vendor::updateOrCreate(
                ['user_id' => $vendorUser->id],
                [
                    'vendor_category_id' => $category->id,
                    'business_name' => $businessName,
                    'owner_name' => $ownerName,
                    'contact_number' => $vendorUser->mobile,
                    'show_contact_number' => true,
                    'address' => sprintf(
                        '%s, %s, %s, %s',
                        $faker->buildingNumber(),
                        $faker->streetName(),
                        $city[0],
                        $city[1]
                    ),
                    'latitude' => round($city[2] + $faker->randomFloat(4, -0.06, 0.06), 7),
                    'longitude' => round($city[3] + $faker->randomFloat(4, -0.06, 0.06), 7),
                    'is_open' => true,
                    'bookings_paused' => false,
                    'token_booking_enabled' => $mode !== 'time_slot',
                    'token_amount' => $mode !== 'time_slot' ? $faker->numberBetween(1, 4) * 50 : null,
                    'appointment_mode' => $mode,
                    'avg_consultation_time' => $faker->randomElement([15, 20, 30, 45]),
                    'global_opening_time' => $open,
                    'global_closing_time' => $close,
                    'allow_booking_until_closing' => $faker->boolean(70),
                    'service_fee' => $serviceFee,
                    'emergency_fee' => $serviceFee + $faker->numberBetween(1, 6) * 50,
                    'subscription_plan_id' => $plan->id,
                    'subscription_expires_at' => now()->addMonths($faker->numberBetween(2, 12)),
                    'status' => 'active',
                    'is_verified' => (float) $plan->price >= 399,
                    'vendor_type' => $this->vendorTypeFor($category->slug),
                ]
            );

            $vendorIds[] = $vendor->id;

            // Derived from the index rather than randomised, so a re-run lands
            // on the same head-count instead of accumulating extra staff.
            $this->seedEmployees(
                $vendor,
                $faker,
                $open,
                $close,
                $i,
                $i % 3 === 0 ? 1 : 2,
                $serviceFee
            );
        }

        $this->report($vendorIds);
    }

    /**
     * Build the 50 opening windows.
     *
     * Twenty vendors trade round the clock; their one-second daily changeover
     * is staggered across different hours so the shops are never all shut at
     * the same instant. The remaining thirty run overlapping long shifts,
     * weighted towards the night so the small hours stay covered too.
     *
     * @return array<int, array{0: string, 1: string}>
     */
    private function shiftPlan(): array
    {
        $plan = [];

        for ($i = 0; $i < self::ROUND_THE_CLOCK; $i++) {
            $hour = intdiv($i * 24, self::ROUND_THE_CLOCK);
            $plan[] = [
                sprintf('%02d:00:00', $hour),
                sprintf('%02d:59:59', ($hour + 23) % 24),
            ];
        }

        foreach (self::DAY_WINDOWS as [$open, $close, $count]) {
            for ($i = 0; $i < $count; $i++) {
                $plan[] = [$open, $close];
            }
        }

        // Interleave so consecutive vendors don't share a window — the listing
        // orders by newest first and this keeps that page varied.
        $ordered = [];
        $half = (int) ceil(count($plan) / 2);
        for ($i = 0; $i < $half; $i++) {
            $ordered[] = $plan[$i];
            if (isset($plan[$i + $half])) {
                $ordered[] = $plan[$i + $half];
            }
        }

        return $ordered;
    }

    /**
     * One or two employees whose shifts together cover the vendor's window.
     */
    private function seedEmployees(
        Vendor $vendor,
        \Faker\Generator $faker,
        string $open,
        string $close,
        int $vendorIndex,
        int $count,
        int $serviceFee
    ): void {
        $shifts = $count === 1
            ? [[$open, $close]]
            : [[$open, $this->midpoint($open, $close)], [$this->midpoint($open, $close), $close]];

        $keep = [];

        foreach ($shifts as $n => [$start, $end]) {
            $employeeUser = User::updateOrCreate(
                ['email' => sprintf('demo.emp%02d-%d@obai.test', $vendorIndex + 1, $n + 1)],
                [
                    'name' => $faker->name(),
                    'mobile' => (string) (self::EMPLOYEE_MOBILE_BASE + ($vendorIndex * 2) + $n + 1),
                    'role' => 'employee',
                    'status' => 'active',
                    'password' => Hash::make('password'),
                    'mobile_verified_at' => now(),
                ]
            );

            Employee::updateOrCreate(
                ['vendor_id' => $vendor->id, 'user_id' => $employeeUser->id],
                [
                    'name' => $employeeUser->name,
                    'working_start_time' => $start,
                    'working_end_time' => $end,
                    'slot_duration' => $faker->randomElement([15, 20, 30, 45]),
                    // Must stay above zero: the discovery query filters on it.
                    'service_fee_override' => $serviceFee + ($n * $faker->numberBetween(0, 4) * 50),
                    'premium_fee' => $faker->randomElement([0, 50, 100, 150]),
                    'premium_bookings_count' => $faker->numberBetween(0, 4),
                    'is_active' => true,
                    'is_paused' => false,
                    'max_daily_tokens' => $faker->randomElement([null, 20, 30, 40]),
                ]
            );

            $keep[] = $employeeUser->id;
        }

        // Drop staff left behind by an earlier run that gave this vendor more
        // employees, so the seeded shifts stay exactly as planned.
        Employee::where('vendor_id', $vendor->id)
            ->whereNotIn('user_id', $keep)
            ->whereHas('user', fn($q) => $q->where('email', 'like', 'demo.emp%@obai.test'))
            ->get()
            ->each(function (Employee $stale) {
                $stale->user?->delete();
                $stale->delete();
            });
    }

    /**
     * Halfway through a window, minute-rounded. Handles windows that cross
     * midnight (22:00 → 06:00 → 02:00).
     */
    private function midpoint(string $open, string $close): string
    {
        $start = Carbon::parse("2000-01-01 $open");
        $end = Carbon::parse("2000-01-01 $close");

        if ($end->lte($start)) {
            $end->addDay();
        }

        return $start->copy()
            ->addSeconds(intdiv($start->diffInSeconds($end), 2))
            ->format('H:i:00');
    }

    private function vendorTypeFor(string $categorySlug): string
    {
        return match ($categorySlug) {
            'doctor', 'training', 'barber', 'activity', 'consultant' => $categorySlug,
            default => 'consultant',
        };
    }

    /**
     * Walk the clock in five-minute steps and count how many of the seeded
     * vendors would actually surface on the listing at that moment, using the
     * same two gates CustomerDiscoveryController applies.
     */
    private function report(array $vendorIds): void
    {
        $vendors = Vendor::with('employees')->whereIn('id', $vendorIds)->get();
        $shifts = app(ShiftService::class);

        $min = PHP_INT_MAX;
        $minAt = null;
        $max = 0;
        $day = Carbon::today();

        for ($minute = 0; $minute < 1440; $minute += 5) {
            $at = $day->copy()->addMinutes($minute);
            Carbon::setTestNow($at);

            $openNow = $vendors->filter(
                fn(Vendor $vendor) => $this->isBookableAt($vendor, $at, $shifts)
            )->count();

            if ($openNow < $min) {
                $min = $openNow;
                $minAt = $at->format('H:i');
            }
            $max = max($max, $openNow);
        }

        Carbon::setTestNow();

        $this->command?->info(sprintf(
            'Seeded %d active vendors and %d active employees.',
            $vendors->count(),
            $vendors->sum(fn(Vendor $vendor) => $vendor->employees->count())
        ));
        $this->command?->info(sprintf(
            'Bookable right now across the clock: min %d (at %s), max %d.',
            $min,
            $minAt,
            $max
        ));

        if ($min < 30) {
            $this->command?->error('Coverage dropped below the required 30 vendors — check the shift plan.');
        }
    }

    /**
     * Mirrors the listing's Gate 1 (shop window) + Gate 2 (an employee is
     * actually working) so the report reflects what a customer would see.
     */
    private function isBookableAt(Vendor $vendor, Carbon $now, ShiftService $shifts): bool
    {
        if (!$vendor->is_open || $vendor->status !== 'active') {
            return false;
        }

        [, $vOpen, $vClose] = $shifts->resolveShift(
            $now,
            $vendor->global_opening_time,
            $vendor->global_closing_time
        );

        if (!$now->between($vOpen, $vClose)) {
            return false;
        }

        foreach ($vendor->employees as $employee) {
            if (!$employee->is_active || $employee->is_paused) {
                continue;
            }

            $start = $vOpen->copy()->setTimeFromTimeString($employee->working_start_time);
            $end = $vOpen->copy()->setTimeFromTimeString($employee->working_end_time);

            if ($end->lte($start)) {
                $end->addDay();
            }

            if ($vClose->isNextDay($vOpen) && $start->lt($vOpen)) {
                $start->addDay();
                $end->addDay();
            }

            $start = $start->max($vOpen);
            $end = $end->min($vClose);

            if ($start->gte($end)) {
                continue;
            }

            if ($now->gte($start) && $now->lt($end)) {
                return true;
            }
        }

        return false;
    }
}
