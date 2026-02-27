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
        Schema::create('inventory_import_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->indexed();
            $table->string('media_type')->indexed();
            $table->enum('status', ['uploaded', 'processing', 'processed', 'approved', 'completed', 'failed'])->default('uploaded');
            $table->integer('total_rows')->default(0);
            $table->integer('valid_rows')->default(0);
            $table->integer('invalid_rows')->default(0);
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('vendor_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Additional indexes for query optimization
            $table->index(['vendor_id', 'status']);
            $table->index(['media_type', 'status']);
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
        Schema::dropIfExists('inventory_import_batches');
    }
};
