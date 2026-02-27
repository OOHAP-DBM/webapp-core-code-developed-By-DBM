<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE vendor_profiles 
            MODIFY commission_percentage DECIMAL(5,2) NULL DEFAULT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE vendor_profiles 
            MODIFY commission_percentage DECIMAL(5,2) NOT NULL DEFAULT 10.00
        ");
    }
};