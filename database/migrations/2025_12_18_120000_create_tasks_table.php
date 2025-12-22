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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Vendor user who owns the task');
            $table->foreignId('booking_id')
                ->nullable()
                ->constrained('bookings')
                ->nullOnDelete()
                ->comment('Related booking, if any');
            $table->enum('type', ['graphics', 'printing', 'mounting', 'maintenance'])
                ->comment('Type of task as per business process');
            $table->string('title')->index()->comment('Short task title');
            $table->text('description')->nullable()->comment('Detailed task description');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])
                ->index()
                ->comment('Task status for workflow management');
            $table->enum('priority', ['low', 'medium', 'high'])
                ->index()
                ->comment('Business priority for scheduling');
            $table->unsignedTinyInteger('progress')->default(0)->comment('Completion percentage (0-100)');
            $table->date('due_date')->index()->comment('Due date for task completion');
            $table->timestamp('started_at')->nullable()->comment('When task was started');
            $table->timestamp('completed_at')->nullable()->comment('When task was completed');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tasks');
    }
};
