<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Super Admin',
                'mobile' => '9876543210',
                'role' => 'admin',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );

        // Subscription Plans
        $plans = [
            [
                'name' => 'Basic',
                'price' => 999,
                'max_employees' => 2,
                'features' => ['Up to 2 employees', 'Basic analytics', 'Online bookings', 'QR Code'],
            ],
            [
                'name' => 'Standard',
                'price' => 1999,
                'max_employees' => 5,
                'features' => ['Up to 5 employees', 'Pro analytics', 'Online bookings', 'Priority support', 'QR Code'],
            ],
            [
                'name' => 'Premium',
                'price' => 3999,
                'max_employees' => 15,
                'features' => ['Up to 15 employees', 'Advanced analytics', 'Online bookings', 'Dedicated account manager', 'QR Code'],
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['name' => $plan['name']],
                $plan
            );
        }
    }
}
