<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use App\Models\SubscriptionPlan;
use App\Services\ThemeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed a basic plan for registration tests
        SubscriptionPlan::create([
            'name' => 'Pro Plan',
            'price' => 199,
            'max_employees' => 10,
            'is_active' => true
        ]);
    }

    /** @test */
    public function theme_service_returns_correct_config_for_all_roles()
    {
        $roles = ['doctor', 'salon', 'sports', 'training', 'consultancy'];
        
        foreach ($roles as $role) {
            $theme = ThemeService::getTheme($role);
            $this->assertIsArray($theme);
            $this->assertEquals($role, $theme['key']);
            $this->assertArrayHasKey('primary', $theme);
            $this->assertArrayHasKey('services', $theme);
        }
    }

    /** @test */
    public function vendor_listing_filters_by_role()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Vendor::create([
            'user_id' => $user1->id,
            'business_name' => 'Doctor Clinic',
            'owner_name' => 'Dr Smith',
            'contact_number' => '1234567890',
            'vendor_type' => 'doctor',
            'status' => 'active',
            'is_open' => true,
        ]);

        Vendor::create([
            'user_id' => $user2->id,
            'business_name' => 'Beauty Salon',
            'owner_name' => 'Jane Doe',
            'contact_number' => '0987654321',
            'vendor_type' => 'salon',
            'status' => 'active',
            'is_open' => true,
        ]);

        $response = $this->get('/?type=doctor');
        $response->assertStatus(200);
        $response->assertSee('Doctor Clinic');
        $response->assertDontSee('Beauty Salon');
    }

    /** @test */
    public function vendor_details_page_loads_correct_theme()
    {
        $user = User::factory()->create();
        $vendor = Vendor::create([
            'user_id' => $user->id,
            'business_name' => 'Sport Center',
            'owner_name' => 'Coach John',
            'contact_number' => '1234567890',
            'vendor_type' => 'sports',
            'status' => 'active',
            'is_open' => true,
            'slug' => 'sport-center'
        ]);

        $response = $this->get("/vendors/{$vendor->slug}");
        $response->assertStatus(200);
        
        // Check for theme-specific CSS variables or strings
        $theme = ThemeService::getTheme('sports');
        $response->assertSee($theme['primary']);
        $response->assertSee('theme-sports');
    }

    /** @test */
    public function vendor_profile_update_saves_role()
    {
        $user = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::create([
            'user_id' => $user->id,
            'business_name' => 'Old Name',
            'owner_name' => 'Old Owner',
            'contact_number' => '1234567890',
            'vendor_type' => 'consultancy',
            'status' => 'active',
        ]);

        $this->actingAs($user);

        $response = $this->post('/vendor/profile', [
            'vendor_type' => 'salon',
            'business_name' => 'New Salon',
            'owner_name' => 'New Owner'
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('salon', $vendor->fresh()->vendor_type);
    }
}
