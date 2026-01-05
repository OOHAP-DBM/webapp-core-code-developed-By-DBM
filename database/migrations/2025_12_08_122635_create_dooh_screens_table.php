<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{


    public function up(): void
    {
        Schema::create('dooh_screens', function (Blueprint $table) {
            $table->id();

                $table->foreignId('hoarding_id')
                    ->constrained('hoardings')
                    ->cascadeOnDelete();
                $table->string('external_screen_id')->nullable();
                // $table->string('name')->nullable();
                $table->string('screen_type')->nullable();

                $table->integer('resolution_width')->nullable();
                $table->integer('resolution_height')->nullable();
                $table->string('resolution_unit')->default('pixels');
                $table->decimal('screen_size', 10, 2)->nullable();
                $table->string('screen_unit')->default('inches');

                /* SLOT LOGIC */
                $table->integer('slot_duration_seconds')->nullable();
                $table->integer('loop_duration_seconds')->nullable();
                $table->integer('slots_per_loop')->nullable();
                $table->integer('total_slots_per_day')->nullable();
                $table->integer('available_slots_per_day')->nullable();

                /* DOOH PRICING */
                $table->decimal('price_per_slot', 12, 2)->nullable();
                $table->decimal('display_price_per_30s', 12, 2)->nullable();

                /* MEDIA */
                $table->json('allowed_formats')->nullable();
                $table->integer('max_file_size_mb')->nullable();
                $table->integer('video_length')->nullable();
                $table->json('services_included')->nullable();
                $table->json('long_term_offers')->nullable();

                /* SYNC */
                $table->enum('sync_status', ['pending', 'synced', 'failed'])->default('pending');
                $table->timestamp('last_synced_at')->nullable();
                $table->json('sync_metadata')->nullable();

                $table->timestamps();
                $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dooh_screens');
    }
};
