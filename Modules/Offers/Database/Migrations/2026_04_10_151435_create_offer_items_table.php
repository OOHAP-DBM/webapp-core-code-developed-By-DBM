<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('offer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')
                  ->constrained('offers')
                  ->cascadeOnDelete();
 
            // Link back to the enquiry item this was created from (nullable: vendor can create offer without enquiry)
            $table->foreignId('enquiry_item_id')
                  ->nullable()
                  ->constrained('enquiry_items')
                  ->nullOnDelete();
 
            $table->foreignId('hoarding_id')
                  ->constrained('hoardings');
 
            $table->enum('hoarding_type', ['ooh', 'dooh'])->default('ooh');
 
            // Package (optional) — polymorphic via type column
            $table->unsignedBigInteger('package_id')->nullable();
            $table->string('package_type')->nullable();  // 'hoarding_package' | 'dooh_package'
            $table->string('package_label')->nullable();
 
            // Campaign dates — per item because multi-vendor enquiries can differ
            $table->date('preferred_start_date');
            $table->date('preferred_end_date');
            $table->unsignedSmallInteger('duration_months')->nullable();
 
            // Pricing — no snapshot here; snapshot is taken at quotation stage
            $table->decimal('price_per_month', 12, 2)->nullable();  // reference price at time of offer
            $table->decimal('offered_price', 12, 2)->nullable();     // vendor's total offered price for this item
            $table->decimal('discount_percent', 5, 2)->nullable();
 
            $table->json('services')->nullable();
            $table->json('meta')->nullable();   // lightweight: location, dimensions — NOT a full snapshot
 
            $table->timestamps();
 
            $table->index('offer_id');
            $table->index('hoarding_id');
            $table->index('enquiry_item_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('offer_items');
    }
};
