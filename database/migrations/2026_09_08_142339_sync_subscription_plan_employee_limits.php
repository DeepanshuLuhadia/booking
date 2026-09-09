<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\SubscriptionPlan;

return new class extends Migration
{
    /**
     * Plan name => [new limit, previous limit], so down() can restore exactly
     * what up() overwrote instead of guessing a value.
     */
    private array $limits = [
        'Basic' => [1, 2],
        'Standard' => [3, 5],
        'Premium' => [5, 15],
        'Free Trial' => [1, 2],
    ];

    public function up(): void
    {
        foreach ($this->limits as $name => [$new, $old]) {
            SubscriptionPlan::where('name', $name)->update(['max_employees' => $new]);
        }
    }

    public function down(): void
    {
        foreach ($this->limits as $name => [$new, $old]) {
            SubscriptionPlan::where('name', $name)->update(['max_employees' => $old]);
        }
    }
};
