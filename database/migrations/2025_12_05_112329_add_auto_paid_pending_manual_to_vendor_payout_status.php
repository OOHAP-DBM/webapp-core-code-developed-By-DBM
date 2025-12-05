<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite doesn't support ALTER COLUMN, so we need to recreate the table
        Schema::table('booking_payments', function (Blueprint $table) {
            // Note: For SQLite, we'll handle this with raw SQL
            // For MySQL/PostgreSQL, you would use the MODIFY COLUMN syntax
        });

        // Get the database driver
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE booking_payments MODIFY COLUMN vendor_payout_status ENUM('pending', 'processing', 'completed', 'failed', 'on_hold', 'auto_paid', 'pending_manual_payout') DEFAULT 'pending'");
        } elseif ($driver === 'sqlite') {
            // SQLite: We'll just allow the new values - SQLite doesn't enforce ENUM
            // The column is already text-based, so no migration needed
            // We just document the new allowed values
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE booking_payments MODIFY COLUMN vendor_payout_status ENUM('pending', 'processing', 'completed', 'failed', 'on_hold') DEFAULT 'pending'");
        }
    }
};
