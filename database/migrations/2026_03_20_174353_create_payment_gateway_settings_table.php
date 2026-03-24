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
        Schema::create('payment_gateway_settings', function (Blueprint $table) {
            $table->id();
            $table->string('gateway')->default('razorpay'); // future: stripe, payu etc
            $table->string('key_id')->nullable();
            $table->text('key_secret')->nullable();        // encrypted
            $table->string('webhook_secret')->nullable();  // encrypted
            $table->string('currency')->default('INR');
            $table->string('mode')->default('test');       // test | live
            $table->boolean('is_active')->default(false);
            $table->string('business_name')->nullable();
            $table->string('business_logo')->nullable();
            $table->string('theme_color')->default('#009A5C');
            $table->json('meta')->nullable();              // extra config
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_settings');
    }
};
