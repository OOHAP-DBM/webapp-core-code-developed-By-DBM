<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * PROMPT 67: DOOH Creative Schedule Management
     */
    public function up(): void
    {
        Schema::create('dooh_creative_schedules', function (Blueprint $table) {
            $table->id();
            
            // Core references
            $table->foreignId('creative_id')->constrained('dooh_creatives')->onDelete('cascade');
            $table->foreignId('dooh_screen_id')->constrained('dooh_screens')->onDelete('cascade');
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            
            // Schedule identification
            $table->string('schedule_name');
            $table->text('description')->nullable();
            
            // Date range
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days');
            
            // Time slots (multiple per day)
            $table->json('time_slots'); // Array of {start_time, end_time, duration_minutes}
            $table->time('daily_start_time')->nullable(); // Overall daily start
            $table->time('daily_end_time')->nullable(); // Overall daily end
            
            // Loop & frequency settings
            $table->integer('slots_per_loop')->default(1); // How many slots in screen's loop
            $table->integer('loop_frequency')->default(1); // How often creative appears per loop cycle
            $table->integer('displays_per_hour')->default(12); // Calculated frequency
            $table->integer('displays_per_day')->default(288); // Total displays per day
            $table->integer('total_displays')->default(0); // Total for entire schedule period
            
            // Priority & ordering
            $table->integer('priority')->default(5); // 1-10, higher = more frequent display
            $table->integer('position_in_loop')->nullable(); // Fixed position or null for dynamic
            
            // Days of week (for recurring schedules)
            $table->json('active_days')->nullable(); // [1,2,3,4,5] for Mon-Fri, null for all days
            $table->boolean('is_recurring')->default(false);
            
            // Cost & billing
            $table->decimal('cost_per_display', 10, 4)->default(0);
            $table->decimal('daily_cost', 10, 2)->default(0);
            $table->decimal('total_cost', 12, 2)->default(0);
            
            // Validation & approval
            $table->enum('validation_status', [
                'pending',
                'checking_availability',
                'approved',
                'rejected',
                'conflicts_found'
            ])->default('pending');
            $table->text('validation_errors')->nullable(); // JSON array of conflicts
            $table->boolean('availability_confirmed')->default(false);
            $table->timestamp('availability_checked_at')->nullable();
            
            // Admin approval
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            
            // Schedule status
            $table->enum('status', [
                'draft',
                'pending_approval',
                'approved',
                'active',
                'paused',
                'completed',
                'cancelled',
                'expired'
            ])->default('draft');
            
            // Execution tracking
            $table->timestamp('scheduled_start_at')->nullable(); // When schedule becomes active
            $table->timestamp('scheduled_end_at')->nullable(); // When schedule ends
            $table->timestamp('activated_at')->nullable(); // When actually started
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            // Performance metrics
            $table->integer('actual_displays')->default(0); // Tracked displays
            $table->decimal('completion_rate', 5, 2)->default(0); // % of planned displays
            $table->json('daily_stats')->nullable(); // Stats per day {date, displays, impressions}
            
            // Conflict resolution
            $table->json('conflict_warnings')->nullable(); // Other schedules that may overlap
            $table->boolean('auto_resolve_conflicts')->default(false);
            $table->integer('conflict_resolution_priority')->default(5);
            
            // Metadata
            $table->json('metadata')->nullable(); // Additional schedule parameters
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('creative_id');
            $table->index('dooh_screen_id');
            $table->index('booking_id');
            $table->index('customer_id');
            $table->index(['start_date', 'end_date']);
            $table->index(['status', 'validation_status']);
            $table->index('activated_at');
            
            // Composite index for availability checks
            $table->index(['dooh_screen_id', 'start_date', 'end_date', 'status'], 'screen_date_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dooh_creative_schedules');
    }
};
