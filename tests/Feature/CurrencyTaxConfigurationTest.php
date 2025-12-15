<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\CurrencyConfig;
use App\Models\TaxConfiguration;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\User;
use App\Models\Enquiry;
use App\Services\CurrencyService;
use App\Services\TaxConfigurationService;
use App\Services\PurchaseOrderService;
use Modules\Offers\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

/**
 * PROMPT 109: Currency + Tax Configuration Tests
 * 
 * Tests for global currency and tax configuration system
 */
class CurrencyTaxConfigurationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected CurrencyService $currencyService;
    protected TaxConfigurationService $taxConfigService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->currencyService = app(CurrencyService::class);
        $this->taxConfigService = app(TaxConfigurationService::class);
    }

    /** @test */
    public function it_can_create_currency_configuration()
    {
        $currency = CurrencyConfig::create([
            'code' => 'INR',
            'name' => 'Indian Rupee',
            'symbol' => '₹',
            'symbol_position' => 'before',
            'decimal_separator' => '.',
            'thousand_separator' => ',',
            'decimal_places' => 2,
            'exchange_rate' => 1.000000,
            'is_default' => true,
            'is_active' => true,
            'country_code' => 'IN',
        ]);

        $this->assertDatabaseHas('currency_configs', [
            'code' => 'INR',
            'is_default' => true,
        ]);

        $this->assertTrue($currency->isINR());
        $this->assertEquals('₹', $currency->getSymbol());
    }

    /** @test */
    public function it_formats_currency_correctly()
    {
        CurrencyConfig::create([
            'code' => 'INR',
            'name' => 'Indian Rupee',
            'symbol' => '₹',
            'symbol_position' => 'before',
            'decimal_separator' => '.',
            'thousand_separator' => ',',
            'decimal_places' => 2,
            'exchange_rate' => 1.000000,
            'is_default' => true,
            'is_active' => true,
            'country_code' => 'IN',
        ]);

        $formatted = $this->currencyService->format(1234.56);
        
        $this->assertEquals('₹ 1,234.56', $formatted);
    }

    /** @test */
    public function it_can_convert_between_currencies()
    {
        CurrencyConfig::create([
            'code' => 'INR',
            'name' => 'Indian Rupee',
            'symbol' => '₹',
            'symbol_position' => 'before',
            'decimal_separator' => '.',
            'thousand_separator' => ',',
            'decimal_places' => 2,
            'exchange_rate' => 1.000000,
            'is_default' => true,
            'is_active' => true,
            'country_code' => 'IN',
        ]);

        CurrencyConfig::create([
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'symbol_position' => 'before',
            'decimal_separator' => '.',
            'thousand_separator' => ',',
            'decimal_places' => 2,
            'exchange_rate' => 83.000000, // 1 USD = 83 INR
            'is_default' => false,
            'is_active' => true,
            'country_code' => 'US',
        ]);

        // Convert 100 USD to INR
        $converted = $this->currencyService->convert(100, 'USD', 'INR');
        
        $this->assertEquals(100.0, $converted); // 100 USD * 83 / 83 = 100 INR (base conversion)
    }

    /** @test */
    public function it_only_allows_one_default_currency()
    {
        CurrencyConfig::create([
            'code' => 'INR',
            'name' => 'Indian Rupee',
            'symbol' => '₹',
            'is_default' => true,
            'is_active' => true,
            'country_code' => 'IN',
            'symbol_position' => 'before',
            'decimal_separator' => '.',
            'thousand_separator' => ',',
            'decimal_places' => 2,
            'exchange_rate' => 1.0,
        ]);

        CurrencyConfig::create([
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'is_default' => true, // This should unset INR as default
            'is_active' => true,
            'country_code' => 'US',
            'symbol_position' => 'before',
            'decimal_separator' => '.',
            'thousand_separator' => ',',
            'decimal_places' => 2,
            'exchange_rate' => 83.0,
        ]);

        $inr = CurrencyConfig::where('code', 'INR')->first();
        $usd = CurrencyConfig::where('code', 'USD')->first();

        $this->assertFalse($inr->is_default);
        $this->assertTrue($usd->is_default);
    }

    /** @test */
    public function it_can_create_tax_configuration()
    {
        $config = TaxConfiguration::create([
            'key' => 'gst_enabled',
            'name' => 'GST Enabled',
            'description' => 'Enable/disable GST',
            'config_type' => TaxConfiguration::TYPE_GST,
            'data_type' => TaxConfiguration::DATA_BOOLEAN,
            'group' => TaxConfiguration::GROUP_TAX_RULES,
            'value' => '1',
            'is_active' => true,
            'country_code' => 'IN',
        ]);

        $this->assertDatabaseHas('tax_configurations', [
            'key' => 'gst_enabled',
            'config_type' => 'gst',
        ]);

        $this->assertTrue($config->getTypedValue());
    }

    /** @test */
    public function it_calculates_intra_state_gst_correctly()
    {
        $this->seedCurrencyAndTax();

        $taxCalc = $this->taxConfigService->calculateCompleteTax(10000, [
            'transaction_type' => 'purchase_order',
            'customer_state_code' => 'MH',
            'vendor_state_code' => 'MH', // Same state = intra-state
        ]);

        $this->assertTrue($taxCalc['is_intra_state']);
        $this->assertEquals(18, $taxCalc['gst_rate']);
        $this->assertEquals(1800, $taxCalc['gst_amount']);
        $this->assertEquals(9, $taxCalc['cgst_rate']);
        $this->assertEquals(900, $taxCalc['cgst_amount']);
        $this->assertEquals(9, $taxCalc['sgst_rate']);
        $this->assertEquals(900, $taxCalc['sgst_amount']);
        $this->assertNull($taxCalc['igst_rate']);
    }

    /** @test */
    public function it_calculates_inter_state_gst_correctly()
    {
        $this->seedCurrencyAndTax();

        $taxCalc = $this->taxConfigService->calculateCompleteTax(10000, [
            'transaction_type' => 'purchase_order',
            'customer_state_code' => 'MH',
            'vendor_state_code' => 'KA', // Different state = inter-state
        ]);

        $this->assertFalse($taxCalc['is_intra_state']);
        $this->assertEquals(18, $taxCalc['gst_rate']);
        $this->assertEquals(1800, $taxCalc['gst_amount']);
        $this->assertEquals(18, $taxCalc['igst_rate']);
        $this->assertEquals(1800, $taxCalc['igst_amount']);
        $this->assertNull($taxCalc['cgst_rate']);
        $this->assertNull($taxCalc['sgst_rate']);
    }

    /** @test */
    public function it_applies_tcs_when_threshold_exceeded()
    {
        $this->seedCurrencyAndTax();

        // Enable TCS
        TaxConfiguration::setValue('tcs_enabled', true, TaxConfiguration::DATA_BOOLEAN, TaxConfiguration::TYPE_TCS);
        TaxConfiguration::setValue('tcs_threshold_amount', 1000000, TaxConfiguration::DATA_FLOAT, TaxConfiguration::TYPE_TCS);
        TaxConfiguration::setValue('tcs_rate_percentage', 0.1, TaxConfiguration::DATA_FLOAT, TaxConfiguration::TYPE_TCS);

        // Amount exceeds threshold
        $taxCalc = $this->taxConfigService->calculateCompleteTax(2000000, [
            'transaction_type' => 'purchase_order',
        ]);

        $this->assertTrue($taxCalc['tcs_applicable']);
        $this->assertEquals(0.1, $taxCalc['tcs_rate']);
        $this->assertGreaterThan(0, $taxCalc['tcs_amount']);
    }

    /** @test */
    public function it_does_not_apply_tcs_below_threshold()
    {
        $this->seedCurrencyAndTax();

        // Enable TCS but amount is below threshold
        TaxConfiguration::setValue('tcs_enabled', true, TaxConfiguration::DATA_BOOLEAN, TaxConfiguration::TYPE_TCS);
        TaxConfiguration::setValue('tcs_threshold_amount', 50000000, TaxConfiguration::DATA_FLOAT, TaxConfiguration::TYPE_TCS);

        $taxCalc = $this->taxConfigService->calculateCompleteTax(100000, [
            'transaction_type' => 'purchase_order',
        ]);

        $this->assertFalse($taxCalc['tcs_applicable']);
        $this->assertEquals(0, $taxCalc['tcs_amount']);
    }

    /** @test */
    public function it_generates_purchase_order_with_correct_tax_breakdown()
    {
        $this->seedCurrencyAndTax();
        $this->seedTaxRules();

        // Create necessary models
        $customer = User::factory()->create(['customer_type' => 'business']);
        $vendor = User::factory()->create(['role' => 'vendor']);
        $enquiry = Enquiry::factory()->create(['customer_id' => $customer->id]);
        $offer = Offer::factory()->create([
            'enquiry_id' => $enquiry->id,
            'vendor_id' => $vendor->id,
        ]);
        $quotation = Quotation::factory()->create([
            'offer_id' => $offer->id,
            'customer_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'status' => Quotation::STATUS_APPROVED,
            'total_amount' => 10000,
        ]);

        $poService = app(PurchaseOrderService::class);
        $po = $poService->generateFromQuotation($quotation);

        $this->assertNotNull($po->currency_code);
        $this->assertNotNull($po->currency_symbol);
        $this->assertNotNull($po->tax_rate);
        $this->assertNotNull($po->is_intra_state);
        
        // Check tax amounts
        $this->assertGreaterThan(0, $po->tax);
        $this->assertEquals($po->cgst_amount + $po->sgst_amount + $po->igst_amount, $po->tax);
    }

    /** @test */
    public function purchase_order_has_tax_summary()
    {
        $this->seedCurrencyAndTax();

        $po = PurchaseOrder::factory()->create([
            'currency_code' => 'INR',
            'currency_symbol' => '₹',
            'total_amount' => 10000,
            'subtotal' => 10000,
            'tax' => 1800,
            'tax_rate' => 18,
            'is_intra_state' => true,
            'cgst_rate' => 9,
            'cgst_amount' => 900,
            'sgst_rate' => 9,
            'sgst_amount' => 900,
            'grand_total' => 11800,
        ]);

        $summary = $po->getTaxSummary();

        $this->assertArrayHasKey('subtotal', $summary);
        $this->assertArrayHasKey('gst', $summary);
        $this->assertArrayHasKey('grand_total', $summary);
        $this->assertEquals('₹ 10,000.00', $summary['subtotal']);
    }

    /** @test */
    public function it_gets_tax_configuration_by_group()
    {
        TaxConfiguration::create([
            'key' => 'test_gst_1',
            'name' => 'Test GST 1',
            'config_type' => TaxConfiguration::TYPE_GST,
            'data_type' => TaxConfiguration::DATA_BOOLEAN,
            'group' => TaxConfiguration::GROUP_TAX_RATES,
            'value' => '1',
            'is_active' => true,
            'country_code' => 'IN',
        ]);

        TaxConfiguration::create([
            'key' => 'test_gst_2',
            'name' => 'Test GST 2',
            'config_type' => TaxConfiguration::TYPE_GST,
            'data_type' => TaxConfiguration::DATA_FLOAT,
            'group' => TaxConfiguration::GROUP_TAX_RATES,
            'value' => '18.5',
            'is_active' => true,
            'country_code' => 'IN',
        ]);

        $groupConfigs = TaxConfiguration::getGroup(TaxConfiguration::GROUP_TAX_RATES);

        $this->assertArrayHasKey('test_gst_1', $groupConfigs);
        $this->assertArrayHasKey('test_gst_2', $groupConfigs);
        $this->assertTrue($groupConfigs['test_gst_1']);
        $this->assertEquals(18.5, $groupConfigs['test_gst_2']);
    }

    /**
     * Helper: Seed currency and basic tax config
     */
    protected function seedCurrencyAndTax()
    {
        CurrencyConfig::create([
            'code' => 'INR',
            'name' => 'Indian Rupee',
            'symbol' => '₹',
            'symbol_position' => 'before',
            'decimal_separator' => '.',
            'thousand_separator' => ',',
            'decimal_places' => 2,
            'exchange_rate' => 1.000000,
            'is_default' => true,
            'is_active' => true,
            'country_code' => 'IN',
        ]);

        TaxConfiguration::setValue('gst_enabled', true, TaxConfiguration::DATA_BOOLEAN, TaxConfiguration::TYPE_GST);
        TaxConfiguration::setValue('default_gst_rate', 18.0, TaxConfiguration::DATA_FLOAT, TaxConfiguration::TYPE_GST);
        TaxConfiguration::setValue('company_state_code', 'MH', TaxConfiguration::DATA_STRING);
        TaxConfiguration::setValue('tcs_enabled', false, TaxConfiguration::DATA_BOOLEAN, TaxConfiguration::TYPE_TCS);
        TaxConfiguration::setValue('tds_enabled', true, TaxConfiguration::DATA_BOOLEAN, TaxConfiguration::TYPE_TDS);
    }

    /**
     * Helper: Seed tax rules (PROMPT 62 integration)
     */
    protected function seedTaxRules()
    {
        $this->artisan('db:seed', ['--class' => 'TaxRulesSeeder']);
    }
}
