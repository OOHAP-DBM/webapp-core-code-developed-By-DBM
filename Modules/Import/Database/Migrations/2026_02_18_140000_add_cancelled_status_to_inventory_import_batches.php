<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add 'cancelled' status to the enum if not already present
        DB::statement("ALTER TABLE inventory_import_batches MODIFY status ENUM('uploaded', 'processing', 'processed', 'approved', 'completed', 'cancelled', 'failed')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert to original enum without 'cancelled'
        DB::statement("ALTER TABLE inventory_import_batches MODIFY status ENUM('uploaded', 'processing', 'processed', 'approved', 'completed', 'failed')");
    }
};
