<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SubscriptionPlan;

return new class extends Migration
{
    public function up(): void
    {
        SubscriptionPlan::updateOrCreate(
            ['name' => 'Free Trial'],
            [
                'price' => 0.00,
                'max_employees' => 2,
                'features' => ['1 Month Free Trial', 'Online Booking', 'QR Code'],
                'is_active' => true,
            ]
        );
    }

    public function down(): void
    {
        SubscriptionPlan::where('name', 'Free Trial')->delete();
    }
};
