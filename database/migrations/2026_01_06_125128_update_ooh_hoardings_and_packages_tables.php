<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       

        // --- hoarding_packages table changes ---
        Schema::table('hoarding_packages', function (Blueprint $table) {
            // Add new columns if not exist
            if (!Schema::hasColumn('hoarding_packages', 'ooh_hoarding_id')) {
                $table->unsignedBigInteger('ooh_hoarding_id')->after('vendor_id');
                $table->foreign('ooh_hoarding_id')->references('id')->on('ooh_hoardings')->onDelete('cascade');
                $table->index('ooh_hoarding_id');
            }
            if (!Schema::hasColumn('hoarding_packages', 'description')) {
                $table->text('description')->nullable()->after('package_name');
            }
    
            if (!Schema::hasColumn('hoarding_packages', 'discount_type')) {
                $table->enum('discount_type', ['percentage', 'flat'])->nullable()->after('package_name');
            }
            if (!Schema::hasColumn('hoarding_packages', 'discount_value')) {
                $table->decimal('discount_value', 10, 2)->nullable()->after('discount_type');
            }
            if (!Schema::hasColumn('hoarding_packages', 'auto_apply')) {
                $table->boolean('auto_apply')->default(false)->after('end_date');
            }
           
        });

        // --- dooh_packages table changes ---
        Schema::table('dooh_packages', function (Blueprint $table) {
           
            if (!Schema::hasColumn('dooh_packages', 'discount_type')) {
                $table->enum('discount_type', ['percentage', 'flat'])->nullable()->after('package_name');
            }
            if (!Schema::hasColumn('dooh_packages', 'discount_value')) {
                $table->decimal('discount_value', 10, 2)->nullable()->after('discount_type');
            }
            if (!Schema::hasColumn('dooh_packages', 'slots_per_month')) {
                $table->unsignedInteger('slots_per_month')->nullable()->after('slots_per_day');
            }
            if (!Schema::hasColumn('dooh_packages', 'time_slots')) {
                $table->json('time_slots')->nullable()->after('loop_interval_minutes');
            }
            if (!Schema::hasColumn('dooh_packages', 'auto_apply')) {
                $table->boolean('auto_apply')->default(false)->after('is_active');
            }
            $table->date('start_date')->nullable()->after('auto_apply');
            $table->date('end_date')->nullable()->after('start_date');
       
        });
        Schema::table('enquiries', function (Blueprint $table) {
            $table->string('contact_number')->nullable()->after('customer_note');
        });
        Schema::table('ooh_hoardings', function (Blueprint $table) {
            $table->boolean('remounting_included')->default(false)->after('mounting_charge');
            $table->decimal('remounting_charge', 10, 2)->nullable()->after('remounting_included'); // Includes Mounting + Printing
            $table->boolean('lighting_included')->default(false)->after('remounting_charge');
            $table->decimal('lighting_charge', 10, 2)->nullable()->after('lighting_included');
        });

        // --- hoardings table changes ---
        Schema::table('hoardings', function (Blueprint $table) {
            // Rental Toggles & Weekly Prices
            $table->decimal('weekly_price_1', 12, 2)->nullable()->after('enable_weekly_booking');
            $table->decimal('weekly_price_2', 12, 2)->nullable()->after('weekly_price_1');
            $table->decimal('weekly_price_3', 12, 2)->nullable()->after('weekly_price_2');

            // Service Charges (From the "Services Includes" UI)
           
        });
    }

    public function down(): void
    {
        // --- ooh_hoardings table changes ---
        Schema::table('ooh_hoardings', function (Blueprint $table) {
            $table->dropColumn(['area', 'orientation', 'lighting_included', 'lighting_charge', 'remounting_included', 'remounting_charge']);
        });

        // --- hoarding_packages table changes ---
        Schema::table('hoarding_packages', function (Blueprint $table) {
            $table->dropForeign(['ooh_hoarding_id']);
            $table->dropColumn(['ooh_hoarding_id', 'description', 'discount_type', 'discount_value', 'auto_apply']);
            $table->dropIndex(['is_active', 'package_code', 'ooh_hoarding_id']);
        });

        // --- dooh_packages table changes ---
        Schema::table('dooh_packages', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value', 'slots_per_month', 'time_slots', 'auto_apply']);
            $table->dropIndex(['is_active', 'package_code']);
        });
        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropColumn('contact_number');
        });
        Schema::table('ooh_hoardings', function (Blueprint $table) {
            $table->dropColumn(['remounting_included', 'remounting_charge', 'lighting_included', 'lighting_charge']);
        });
    }
};
