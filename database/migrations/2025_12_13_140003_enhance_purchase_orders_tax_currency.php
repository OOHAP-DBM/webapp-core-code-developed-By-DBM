<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PROMPT 109: Enhance PurchaseOrder with currency and detailed tax breakdown
     */
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Currency fields (after grand_total)
            $table->string('currency_code', 3)->default('INR')->after('grand_total');
            $table->string('currency_symbol', 10)->default('₹')->after('currency_code');
            
            // Detailed tax breakdown (after tax)
            $table->decimal('subtotal', 12, 2)->default(0)->after('total_amount');
            $table->decimal('tax_rate', 5, 2)->nullable()->after('tax')->comment('GST rate percentage');
            
            // GST breakdown (CGST+SGST or IGST)
            $table->decimal('cgst_rate', 5, 2)->nullable()->after('tax_rate');
            $table->decimal('cgst_amount', 12, 2)->default(0)->after('cgst_rate');
            $table->decimal('sgst_rate', 5, 2)->nullable()->after('cgst_amount');
            $table->decimal('sgst_amount', 12, 2)->default(0)->after('sgst_rate');
            $table->decimal('igst_rate', 5, 2)->nullable()->after('sgst_amount');
            $table->decimal('igst_amount', 12, 2)->default(0)->after('igst_rate');
            
            // TCS (Tax Collected at Source)
            $table->boolean('has_tcs')->default(false)->after('igst_amount');
            $table->decimal('tcs_rate', 5, 2)->nullable()->after('has_tcs');
            $table->decimal('tcs_amount', 12, 2)->default(0)->after('tcs_rate');
            $table->string('tcs_section')->nullable()->after('tcs_amount')->comment('Section code');
            
            // TDS (Tax Deducted at Source) - applicable for vendor payments
            $table->boolean('has_tds')->default(false)->after('tcs_section');
            $table->decimal('tds_rate', 5, 2)->nullable()->after('has_tds');
            $table->decimal('tds_amount', 12, 2)->default(0)->after('tds_rate');
            $table->string('tds_section')->nullable()->after('tds_amount')->comment('Section code: 194C, 194J');
            
            // Tax metadata
            $table->boolean('is_intra_state')->nullable()->after('tds_section')->comment('Same state transaction');
            $table->boolean('is_reverse_charge')->default(false)->after('is_intra_state');
            $table->string('place_of_supply')->nullable()->after('is_reverse_charge');
            $table->json('tax_calculation_details')->nullable()->after('place_of_supply')->comment('Complete tax breakdown');
            
            // Add index for currency queries
            $table->index('currency_code');
            $table->index(['has_tcs', 'has_tds']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn([
                'currency_code',
                'currency_symbol',
                'subtotal',
                'tax_rate',
                'cgst_rate',
                'cgst_amount',
                'sgst_rate',
                'sgst_amount',
                'igst_rate',
                'igst_amount',
                'has_tcs',
                'tcs_rate',
                'tcs_amount',
                'tcs_section',
                'has_tds',
                'tds_rate',
                'tds_amount',
                'tds_section',
                'is_intra_state',
                'is_reverse_charge',
                'place_of_supply',
                'tax_calculation_details',
            ]);
        });
    }
};
