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
        Schema::create('enquiry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enquiry_id')->constrained('enquiries')->cascadeOnDelete();
            $table->foreignId('hoarding_id')->constrained('hoardings');

            /* TYPE RESOLUTION */
            $table->enum('hoarding_type', ['ooh', 'dooh']);

            /* PACKAGE (OOH or DOOH) */
            $table->unsignedBigInteger('package_id')->nullable();
            $table->string('package_type', 30);
            // monthly, weekly, slot, loop, custom

            /* DATE RANGE (PER HOARDING) */
            $table->date('preferred_start_date');
            $table->date('preferred_end_date');
            $table->string('expected_duration')->nullable(); // in weeks/months
            /* SERVICES (PAID + FREE) */
            $table->json('services')->nullable();
            /*
                [
                    {
                    "code": "printing",
                    "included": true,
                    "price": 0
                    },
                    {
                    "code": "mounting",
                    "included": false,
                    "price": 5000
                    }
                ]
            */

            /* QUANTITY / SLOT DETAILS (DOOH SAFE) */
            $table->json('meta')->nullable();
            /*
                {
                    "slots_per_day": 120,
                    "slot_duration": 10,
                    "loops": 20
                }
            */

            /* STATUS */
            $table->enum('status', [
                'pending',
                'new',
                'rejected',
                'resend',
                'offer_send',
                'offer_reject',
                'offer_send_again',
                'offer_accept',
                'quotation_send',
                'quotation_reject',
                'quotation_send_again',
                'quotation_accepted',
                'purchase_order_send',
            ])->default('new');

            $table->timestamps();

            /* INDEXES */
            $table->index(['enquiry_id']);
            $table->index(['hoarding_id']);
            $table->index(['hoarding_type']);
            // $table->index(['package_type']);
            $table->index(['preferred_start_date', 'preferred_end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enquiry_items');
    }
};
