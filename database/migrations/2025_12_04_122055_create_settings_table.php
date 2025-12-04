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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->index();
            $table->text('value');
            $table->string('type')->default('string'); // string, integer, float, boolean, json, array
            $table->text('description')->nullable();
            $table->string('group')->default('general'); // general, booking, payment, dooh, notification, commission
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->timestamps();

            // Unique constraint: one key per tenant (or global if tenant_id is null)
            $table->unique(['key', 'tenant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
