<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
         Schema::table('pos_bookings', function (Blueprint $table) {

            /*
             |-------------------------------------------------
             | 1. Fix customer_id foreign key
             |-------------------------------------------------
             */
            $table->dropForeign(['customer_id']);
            $table->unsignedBigInteger('customer_id')->nullable(false)->change();
            $table->foreign('customer_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            /*
             |-------------------------------------------------
             | 2. Add NEW columns (only what was missing)
             |-------------------------------------------------
             */
            if (!Schema::hasColumn('pos_bookings', 'payment_due_at')) {
                $table->timestamp('payment_due_at')->nullable()->after('paid_amount');
            }

            if (!Schema::hasColumn('pos_bookings', 'reminder_count')) {
                $table->unsignedTinyInteger('reminder_count')->default(0)->after('payment_due_at');
            }

            if (!Schema::hasColumn('pos_bookings', 'last_reminder_at')) {
                $table->timestamp('last_reminder_at')->nullable()->after('reminder_count');
            }

            /*
             |-------------------------------------------------
             | 3. Modify EXISTING columns
             |-------------------------------------------------
             */
            $table->decimal('paid_amount', 12, 2)->default(0)->change();
            $table->decimal('total_amount', 12, 2)->change();

            /*
             |-------------------------------------------------
             | 4. Update ENUM status
             |-------------------------------------------------
             | MySQL only
             */
            $table->enum('status', [
                'draft',
                'pending_payment',
                'paid',
                'released',
                'confirmed',
                'active',
                'completed',
                'cancelled',
            ])->default('draft')->change();

            /*
             |-------------------------------------------------
             | 5. Index
             |-------------------------------------------------
             */
            $table->index(['vendor_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::table('pos_bookings', function (Blueprint $table) {

            // revert FK
            $table->dropForeign(['customer_id']);
            $table->unsignedBigInteger('customer_id')->nullable()->change();
            $table->foreign('customer_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            // remove added columns
            $table->dropColumn([
                'payment_due_at',
                'reminder_count',
                'last_reminder_at',
            ]);

            // revert enum (original)
            $table->enum('status', [
                'draft',
                'confirmed',
                'active',
                'completed',
                'cancelled'
            ])->default('draft')->change();
        });
    }
};
