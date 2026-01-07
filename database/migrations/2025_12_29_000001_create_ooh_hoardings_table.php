<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ooh_hoardings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoarding_id')->constrained()->cascadeOnDelete();

            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->string('measurement_unit')->default('Sq.ft');
            $table->enum('orientation', ['horizontal', 'vertical'])->nullable();
            $table->string('lighting_type')->nullable();
            $table->string('material_type')->nullable();

            $table->boolean('printing_included')->default(false);
            $table->decimal('printing_charge', 10, 2)->nullable();
            $table->boolean('mounting_included')->default(false);
            $table->decimal('mounting_charge', 10, 2)->nullable();
            $table->boolean('remounting_included')->default(false);
            $table->decimal('remounting_charge', 10, 2)->nullable(); // Includes Mounting + Printing
            $table->boolean('lighting_included')->default(false);
            $table->decimal('lighting_charge', 10, 2)->nullable();
//             $table->decimal('designing_charge', 10, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('ooh_hoardings');
    }
};
