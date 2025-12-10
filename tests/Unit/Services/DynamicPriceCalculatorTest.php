<?php

namespace Tests\Unit\Services;

use App\Services\DynamicPriceCalculator;
use App\Services\TaxService;
use App\Models\Hoarding;
use App\Models\User;
use Modules\DOOH\Models\DOOHPackage;
use Modules\DOOH\Models\DOOHScreen;
use Modules\Settings\Services\SettingsService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;

class DynamicPriceCalculatorTest extends TestCase
{
    use RefreshDatabase;

    protected DynamicPriceCalculator $calculator;
    protected User $vendor;
    protected Hoarding $hoarding;
    protected SettingsService $settingsService;
    protected TaxService $taxService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create vendor role if it doesn't exist
        Role::firstOrCreate(['name' => 'vendor', 'guard_name' => 'web']);

        $this->settingsService = app(SettingsService::class);
        $this->taxService = app(TaxService::class);
        $this->calculator = new DynamicPriceCalculator($this->settingsService, $this->taxService);

        // Create vendor
        $this->vendor = User::factory()->create([
            'email' => 'vendor@test.com',
        ]);
        $this->vendor->assignRole('vendor');

        // Create test hoarding
        $this->hoarding = Hoarding::factory()->create([
            'vendor_id' => $this->vendor->id,
            'title' => 'Test Billboard',
            'status' => Hoarding::STATUS_ACTIVE,
            'monthly_price' => 30000.00,
            'weekly_price' => 8000.00,
            'enable_weekly_booking' => true,
        ]);
    }

    /** @test */
    public function it_calculates_basic_price_for_monthly_booking()
    {
        $startDate = Carbon::today()->addDays(5)->format('Y-m-d');
        $endDate = Carbon::today()->addDays(35)->format('Y-m-d'); // 31 days (1 month + 1 day)

        $result = $this->calculator->calculate(
            $this->hoarding->id,
            $startDate,
            $endDate
        );

        // Expected: 1 month (30000) + 1 day (1000) = 31000
        // Plus 18% GST = 31000 + 5580 = 36580
        $this->assertEquals(31000.00, $result['base_price']);
        $this->assertEquals(5580.00, $result['gst']);
        $this->assertEquals(36580.00, $result['final_price']);
        $this->assertEquals(0, $result['discount_applied']);
    }

    /** @test */
    public function it_calculates_price_with_percent_discount()
    {
        $startDate = Carbon::today()->addDays(5)->format('Y-m-d');
        $endDate = Carbon::today()->addDays(35)->format('Y-m-d'); // 31 days

        $vendorDiscounts = [
            'type' => 'percent',
            'value' => 10, // 10% discount
        ];

        $result = $this->calculator->calculate(
            $this->hoarding->id,
            $startDate,
            $endDate,
            null,
            $vendorDiscounts
        );

        // Base: 31000
        // Discount (10%): 3100
        // After discount: 27900
        // GST (18%): 5022
        // Final: 32922
        $this->assertEquals(31000.00, $result['base_price']);
        $this->assertEquals(3100.00, $result['discount_applied']);
        $this->assertEquals(5022.00, $result['gst']);
        $this->assertEquals(32922.00, $result['final_price']);
        $this->assertNotNull($result['vendor_offer_applied']);
        $this->assertEquals('percent', $result['vendor_offer_applied']['type']);
        $this->assertEquals(10, $result['vendor_offer_applied']['value']);
    }

    /** @test */
    public function it_calculates_price_with_fixed_discount()
    {
        $startDate = Carbon::today()->addDays(5)->format('Y-m-d');
        $endDate = Carbon::today()->addDays(35)->format('Y-m-d');

        $vendorDiscounts = [
            'type' => 'fixed',
            'value' => 5000, // Fixed Rs. 5000 discount
        ];

        $result = $this->calculator->calculate(
            $this->hoarding->id,
            $startDate,
            $endDate,
            null,
            $vendorDiscounts
        );

        // Base: 31000
        // Discount: 5000
        // After discount: 26000
        // GST (18%): 4680
        // Final: 30680
        $this->assertEquals(31000.00, $result['base_price']);
        $this->assertEquals(5000.00, $result['discount_applied']);
        $this->assertEquals(4680.00, $result['gst']);
        $this->assertEquals(30680.00, $result['final_price']);
        $this->assertEquals('fixed', $result['vendor_offer_applied']['type']);
    }

    /** @test */
    public function it_prevents_discount_exceeding_base_price()
    {
        $startDate = Carbon::today()->addDays(5)->format('Y-m-d');
        $endDate = Carbon::today()->addDays(35)->format('Y-m-d');

        $vendorDiscounts = [
            'type' => 'fixed',
            'value' => 50000, // Discount more than base price
        ];

        $result = $this->calculator->calculate(
            $this->hoarding->id,
            $startDate,
            $endDate,
            null,
            $vendorDiscounts
        );

        // Discount should be capped at base price
        $this->assertEquals(31000.00, $result['discount_applied']);
        $this->assertEquals(0, $result['gst']); // No GST on zero amount
        $this->assertEquals(0, $result['final_price']);
    }

    /** @test */
    public function it_calculates_weekly_pricing_when_applicable()
    {
        $startDate = Carbon::today()->addDays(5)->format('Y-m-d');
        $endDate = Carbon::today()->addDays(19)->format('Y-m-d'); // 15 days (2 weeks + 1 day)

        $result = $this->calculator->calculate(
            $this->hoarding->id,
            $startDate,
            $endDate
        );

        // For 15 days, monthly pricing is more economical
        // Expected: (30000/30) * 15 = 15000
        // Plus 18% GST = 15000 + 2700 = 17700
        $this->assertEquals(15000.00, $result['base_price']);
        $this->assertEquals(17700.00, $result['final_price']);
    }

    /** @test */
    public function it_throws_exception_for_past_start_date()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Start date cannot be in the past');

        $startDate = Carbon::yesterday()->format('Y-m-d');
        $endDate = Carbon::today()->addDays(30)->format('Y-m-d');

        $this->calculator->calculate(
            $this->hoarding->id,
            $startDate,
            $endDate
        );
    }

    /** @test */
    public function it_throws_exception_for_invalid_date_range()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('End date must be after start date');

        $startDate = Carbon::today()->addDays(30)->format('Y-m-d');
        $endDate = Carbon::today()->addDays(20)->format('Y-m-d');

        $this->calculator->calculate(
            $this->hoarding->id,
            $startDate,
            $endDate
        );
    }

    /** @test */
    public function it_throws_exception_for_inactive_hoarding()
    {
        $this->hoarding->update(['status' => Hoarding::STATUS_INACTIVE]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Hoarding not found or not available');

        $startDate = Carbon::today()->addDays(5)->format('Y-m-d');
        $endDate = Carbon::today()->addDays(35)->format('Y-m-d');

        $this->calculator->calculate(
            $this->hoarding->id,
            $startDate,
            $endDate
        );
    }

    /** @test */
    public function it_throws_exception_for_nonexistent_hoarding()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Hoarding not found or not available');

        $startDate = Carbon::today()->addDays(5)->format('Y-m-d');
        $endDate = Carbon::today()->addDays(35)->format('Y-m-d');

        $this->calculator->calculate(
            99999, // Non-existent ID
            $startDate,
            $endDate
        );
    }

    /** @test */
    public function it_calculates_with_dooh_package()
    {
        $this->markTestSkipped('DOOHScreenFactory not yet created');
        
        // Create DOOH screen and package
        $screen = DOOHScreen::factory()->create([
            'hoarding_id' => $this->hoarding->id,
            'vendor_id' => $this->vendor->id,
        ]);

        $package = DOOHPackage::factory()->create([
            'dooh_screen_id' => $screen->id,
            'package_name' => 'Premium Package',
            'price_per_month' => 25000.00,
            'discount_percent' => 5.00, // 5% package discount
            'min_booking_months' => 1,
            'max_booking_months' => 12,
            'is_active' => true,
        ]);

        $startDate = Carbon::today()->addDays(5)->format('Y-m-d');
        $endDate = Carbon::today()->addDays(65)->format('Y-m-d'); // ~60 days (2 months)

        $result = $this->calculator->calculate(
            $this->hoarding->id,
            $startDate,
            $endDate,
            $package->id
        );

        // Expected: 2 months * 25000 = 50000
        // Package discount (5%): 2500
        // After package discount: 47500
        // GST (18%): 8550
        // Final: 56050
        $this->assertEquals(47500.00, $result['base_price']); // Already includes package discount
        $this->assertEquals(8550.00, $result['gst']);
        $this->assertEquals(56050.00, $result['final_price']);
        $this->assertTrue($result['breakdown']['package_used']);
        $this->assertEquals($package->id, $result['breakdown']['package_id']);
    }

    /** @test */
    public function it_validates_package_minimum_duration()
    {
        $this->markTestSkipped('DOOHScreenFactory not yet created');
        
        $screen = DOOHScreen::factory()->create([
            'hoarding_id' => $this->hoarding->id,
            'vendor_id' => $this->vendor->id,
        ]);

        $package = DOOHPackage::factory()->create([
            'dooh_screen_id' => $screen->id,
            'price_per_month' => 25000.00,
            'min_booking_months' => 3, // Minimum 3 months
            'max_booking_months' => 12,
            'is_active' => true,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('requires minimum 3 months');

        $startDate = Carbon::today()->addDays(5)->format('Y-m-d');
        $endDate = Carbon::today()->addDays(35)->format('Y-m-d'); // Only ~1 month

        $this->calculator->calculate(
            $this->hoarding->id,
            $startDate,
            $endDate,
            $package->id
        );
    }

    /** @test */
    public function it_validates_package_maximum_duration()
    {
        $this->markTestSkipped('DOOHScreenFactory not yet created');
        
        $screen = DOOHScreen::factory()->create([
            'hoarding_id' => $this->hoarding->id,
            'vendor_id' => $this->vendor->id,
        ]);

        $package = DOOHPackage::factory()->create([
            'dooh_screen_id' => $screen->id,
            'price_per_month' => 25000.00,
            'min_booking_months' => 1,
            'max_booking_months' => 6, // Maximum 6 months
            'is_active' => true,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('allows maximum 6 months');

        $startDate = Carbon::today()->addDays(5)->format('Y-m-d');
        $endDate = Carbon::today()->addMonths(8)->format('Y-m-d'); // 8 months

        $this->calculator->calculate(
            $this->hoarding->id,
            $startDate,
            $endDate,
            $package->id
        );
    }

    /** @test */
    public function it_provides_quick_estimate()
    {
        $result = $this->calculator->quickEstimate($this->hoarding->id, 15);

        // Daily rate: 30000 / 30 = 1000
        // Base: 1000 * 15 = 15000
        // GST: 2700
        // Total: 17700
        $this->assertEquals(1000.00, $result['daily_rate']);
        $this->assertEquals(15000.00, $result['base_price']);
        $this->assertEquals(2700.00, $result['gst_amount']);
        $this->assertEquals(17700.00, $result['estimated_price']);
    }

    /** @test */
    public function it_compares_prices_with_and_without_discount()
    {
        $startDate = Carbon::today()->addDays(5)->format('Y-m-d');
        $endDate = Carbon::today()->addDays(35)->format('Y-m-d');

        $vendorDiscounts = [
            'type' => 'percent',
            'value' => 15, // 15% discount
        ];

        $comparison = $this->calculator->compareWithDiscount(
            $this->hoarding->id,
            $startDate,
            $endDate,
            $vendorDiscounts
        );

        $this->assertArrayHasKey('without_discount', $comparison);
        $this->assertArrayHasKey('with_discount', $comparison);
        $this->assertArrayHasKey('savings', $comparison);
        $this->assertArrayHasKey('savings_percent', $comparison);

        // Verify savings
        $expectedSavings = $comparison['without_discount']['final_price'] - $comparison['with_discount']['final_price'];
        $this->assertEquals($expectedSavings, $comparison['savings']);
        $this->assertGreaterThan(0, $comparison['savings']);
        $this->assertEquals(15, $comparison['savings_percent']); // Should be approximately 15%
    }

    /** @test */
    public function it_includes_detailed_breakdown_in_results()
    {
        $startDate = Carbon::today()->addDays(5)->format('Y-m-d');
        $endDate = Carbon::today()->addDays(35)->format('Y-m-d');

        $result = $this->calculator->calculate(
            $this->hoarding->id,
            $startDate,
            $endDate
        );

        // Verify breakdown structure
        $this->assertArrayHasKey('breakdown', $result);
        $breakdown = $result['breakdown'];

        $this->assertArrayHasKey('hoarding', $breakdown);
        $this->assertEquals($this->hoarding->id, $breakdown['hoarding']['id']);
        $this->assertEquals($this->hoarding->title, $breakdown['hoarding']['title']);

        $this->assertArrayHasKey('duration', $breakdown);
        $this->assertEquals(31, $breakdown['duration']['days']);

        $this->assertArrayHasKey('pricing', $breakdown);
        $this->assertArrayHasKey('discount', $breakdown['pricing']);
        $this->assertArrayHasKey('gst', $breakdown['pricing']);

        $this->assertArrayHasKey('calculated_at', $breakdown);
    }

    /** @test */
    public function it_handles_edge_case_of_single_day_booking()
    {
        $startDate = Carbon::today()->addDays(5)->format('Y-m-d');
        $endDate = Carbon::today()->addDays(5)->format('Y-m-d'); // Same day

        $result = $this->calculator->calculate(
            $this->hoarding->id,
            $startDate,
            $endDate
        );

        // Expected: 1 day * (30000/30) = 1000
        // Plus 18% GST = 1180
        $this->assertEquals(1000.00, $result['base_price']);
        $this->assertEquals(180.00, $result['gst']);
        $this->assertEquals(1180.00, $result['final_price']);
        $this->assertEquals(1, $result['breakdown']['duration']['days']);
    }

    /** @test */
    public function it_calculates_for_exact_multi_month_duration()
    {
        $startDate = Carbon::today()->addDays(5)->format('Y-m-d');
        $endDate = Carbon::today()->addDays(95)->format('Y-m-d'); // ~90 days (3 months)

        $result = $this->calculator->calculate(
            $this->hoarding->id,
            $startDate,
            $endDate
        );

        // Expected: 3 months * 30000 = 90000 (for 90 days)
        // Plus 18% GST = 106200
        $baseExpected = (30000 / 30) * 91; // 91 days
        $gstExpected = $baseExpected * 0.18;
        $finalExpected = $baseExpected + $gstExpected;

        $this->assertEquals(round($baseExpected, 2), $result['base_price']);
        $this->assertEquals(round($gstExpected, 2), $result['gst']);
        $this->assertEquals(round($finalExpected, 2), $result['final_price']);
    }

    /** @test */
    public function it_applies_combined_package_and_vendor_discounts()
    {
        $this->markTestSkipped('DOOHScreenFactory not yet created');
        
        $screen = DOOHScreen::factory()->create([
            'hoarding_id' => $this->hoarding->id,
            'vendor_id' => $this->vendor->id,
        ]);

        $package = DOOHPackage::factory()->create([
            'dooh_screen_id' => $screen->id,
            'price_per_month' => 25000.00,
            'discount_percent' => 5.00, // 5% package discount
            'min_booking_months' => 1,
            'max_booking_months' => 12,
            'is_active' => true,
        ]);

        $vendorDiscounts = [
            'type' => 'percent',
            'value' => 10, // Additional 10% vendor discount
        ];

        $startDate = Carbon::today()->addDays(5)->format('Y-m-d');
        $endDate = Carbon::today()->addDays(35)->format('Y-m-d'); // ~1 month

        $result = $this->calculator->calculate(
            $this->hoarding->id,
            $startDate,
            $endDate,
            $package->id,
            $vendorDiscounts
        );

        // Package price for 1 month: 25000
        // Package discount (5%): 1250
        // After package discount: 23750 (this becomes base_price)
        // Vendor discount (10%): 2375
        // After vendor discount: 21375
        // GST (18%): 3847.50
        // Final: 25222.50
        $this->assertEquals(23750.00, $result['base_price']);
        $this->assertEquals(2375.00, $result['discount_applied']);
        $this->assertEquals(3847.50, $result['gst']);
        $this->assertEquals(25222.50, $result['final_price']);
    }

    /** @test */
    public function it_respects_custom_gst_rate_from_settings()
    {
        // Mock settings service to return custom GST rate
        $this->settingsService->set('booking_tax_rate', 12.00); // 12% instead of default 18%

        $startDate = Carbon::today()->addDays(5)->format('Y-m-d');
        $endDate = Carbon::today()->addDays(35)->format('Y-m-d');

        $result = $this->calculator->calculate(
            $this->hoarding->id,
            $startDate,
            $endDate
        );

        // Base: 31000
        // GST (12%): 3720
        // Final: 34720
        $this->assertEquals(12.00, $result['gst_rate']);
        $this->assertEquals(3720.00, $result['gst']);
        $this->assertEquals(34720.00, $result['final_price']);
    }
}
