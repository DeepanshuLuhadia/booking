<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Search-as-you-type under the discovery and category search bars.
 *
 * The two guarantees worth pinning down: the panel never fires on a fragment
 * short enough to match half the catalogue, and a category page's suggestions
 * stay inside that category — otherwise the dropdown would offer businesses
 * the page itself cannot show.
 */
class SearchSuggestionsTest extends TestCase
{
    use RefreshDatabase;

    private Vendor $clinic;
    private Vendor $salon;

    protected function setUp(): void
    {
        parent::setUp();

        $plan = SubscriptionPlan::create([
            'name' => 'Test', 'price' => 0, 'max_employees' => 5,
            'features' => ['Test plan'], 'duration_days' => 30, 'is_active' => true,
        ]);

        $doctors = VendorCategory::create(['name' => 'Doctor', 'slug' => 'doctor']);
        $barbers = VendorCategory::create(['name' => 'Barber', 'slug' => 'barber']);

        $this->clinic = $this->makeVendor($plan, $doctors, 'Sunrise Dental Clinic', 'sunrise@example.com', '9000000001');
        $this->salon  = $this->makeVendor($plan, $barbers, 'Sunrise Beauty Studio', 'studio@example.com', '9000000002');
    }

    private function makeVendor(SubscriptionPlan $plan, VendorCategory $category, string $name, string $email, string $phone): Vendor
    {
        $owner = User::create([
            'name' => $name, 'email' => $email, 'mobile' => $phone,
            'password' => bcrypt('secret'), 'role' => 'vendor',
        ]);

        $vendor = Vendor::create([
            'user_id' => $owner->id,
            'vendor_category_id' => $category->id,
            'business_name' => $name,
            'owner_name' => 'Owner',
            'contact_number' => $phone,
            'address' => '1 Test Road',
            'is_open' => true,
            'status' => 'active',
            'is_profile_complete' => true,
            // The enum on this column predates the category table and knows
            // nothing of its slugs, so it is mapped rather than copied.
            'vendor_type' => $category->slug === 'doctor' ? 'doctor' : 'salon',
            'appointment_mode' => 'token',
            'subscription_plan_id' => $plan->id,
            'subscription_expires_at' => now()->addDays(30),
            'global_opening_time' => '00:00', 'global_closing_time' => '23:59',
            'service_fee' => 500, 'token_amount' => 0,
        ]);

        Employee::create([
            'vendor_id' => $vendor->id,
            'name' => 'Staff',
            'working_start_time' => '00:00', 'working_end_time' => '23:59',
            'service_fee_override' => 300,
            'is_active' => true,
            'avg_consultation_time' => 15,
        ]);

        return $vendor;
    }

    public function test_a_fragment_shorter_than_the_minimum_returns_nothing(): void
    {
        $this->getJson('/discover/suggestions?q=su')
            ->assertOk()
            ->assertJson(['html' => '', 'total' => 0, 'shown' => 0]);
    }

    public function test_the_landing_page_suggests_across_every_category(): void
    {
        $response = $this->getJson('/discover/suggestions?q=sunrise')->assertOk();

        $this->assertSame(2, $response->json('total'));
        $this->assertStringContainsString('Sunrise Dental Clinic', $response->json('html'));
        $this->assertStringContainsString('Sunrise Beauty Studio', $response->json('html'));
    }

    public function test_a_category_page_suggests_only_its_own_businesses(): void
    {
        $response = $this->getJson('/discover/suggestions?q=sunrise&type=doctor')->assertOk();

        $this->assertSame(1, $response->json('total'));
        $this->assertStringContainsString('Sunrise Dental Clinic', $response->json('html'));
        $this->assertStringNotContainsString('Sunrise Beauty Studio', $response->json('html'));
    }

    public function test_the_catalogue_slug_is_not_treated_as_a_category(): void
    {
        $response = $this->getJson('/discover/suggestions?q=sunrise&type=all')->assertOk();

        $this->assertSame(2, $response->json('total'));
    }

    public function test_a_term_nobody_matches_renders_the_empty_state(): void
    {
        $response = $this->getJson('/discover/suggestions?q=zzzznope')->assertOk();

        $this->assertSame(0, $response->json('total'));
        $this->assertStringContainsString('No matches', $response->json('html'));
    }

    /** The panel is wired up on both pages that carry a search bar. */
    public function test_both_search_bars_carry_the_suggestion_panel(): void
    {
        foreach (['/', '/category/doctor'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('data-suggest-url', false)
                ->assertSee('id="bvSuggestPanel"', false);
        }
    }
}
