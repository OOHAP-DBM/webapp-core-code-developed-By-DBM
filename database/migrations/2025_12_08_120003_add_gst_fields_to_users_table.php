<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // GST and business details
            $table->string('gstin', 15)->nullable()->unique()->after('email')->comment('GST Identification Number (15 chars)');
            $table->string('company_name')->nullable()->after('name')->comment('Business/company name');
            $table->string('pan', 10)->nullable()->after('gstin')->comment('PAN card number');
            $table->enum('customer_type', ['individual', 'business'])->default('individual')->after('pan');
            
            // Billing address
            $table->text('billing_address')->nullable()->after('address');
            $table->string('billing_city', 100)->nullable()->after('billing_address');
            $table->string('billing_state', 100)->nullable()->after('billing_city');
            $table->string('billing_state_code', 2)->nullable()->after('billing_state')->comment('2-digit state code for GST');
            $table->string('billing_pincode', 6)->nullable()->after('billing_state_code');
            
            // Add indexes
            $table->index('gstin');
            $table->index('customer_type');
            $table->index('billing_state_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['gstin']);
            $table->dropIndex(['customer_type']);
            $table->dropIndex(['billing_state_code']);
            
            $table->dropColumn([
                'gstin',
                'company_name',
                'pan',
                'customer_type',
                'billing_address',
                'billing_city',
                'billing_state',
                'billing_state_code',
                'billing_pincode',
            ]);
        });
    }
};
