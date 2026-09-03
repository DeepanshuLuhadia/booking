<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // A fresh install has no subscription plans and no vendor categories,
        // and without either the sign-up form and the category listing come up
        // empty. Both are keyed on natural identifiers, so this is re-runnable.
        $this->call([
            InitialDataSeeder::class,
            VendorCategorySeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
