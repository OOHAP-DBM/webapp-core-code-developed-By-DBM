<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_import_staging', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id')->indexed();
            $table->unsignedBigInteger('vendor_id')->indexed();
            $table->string('media_type')->indexed();
            $table->string('code')->indexed();
            $table->string('city')->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('height', 10, 2)->nullable();
            $table->string('image_name')->nullable();
            $table->json('extra_attributes')->nullable();
            $table->enum('status', ['valid', 'invalid'])->default('valid');
            $table->text('error_message')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('batch_id')
                ->references('id')
                ->on('inventory_import_batches')
                ->onDelete('cascade');

            $table->foreign('vendor_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Composite indexes for efficient queries
            $table->index(['batch_id', 'status']);
            $table->index(['vendor_id', 'media_type']);
            $table->index(['code', 'vendor_id']);
            $table->index(['status', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_import_staging');
    }
};
