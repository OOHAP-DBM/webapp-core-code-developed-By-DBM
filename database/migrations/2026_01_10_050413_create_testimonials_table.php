<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();

            /**
             * Relations
             */
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('User who submitted the testimonial');

            /**
             * Role context
             * Stored intentionally to preserve context
             * even if user switches role later
             */
            $table->enum('role', ['customer', 'vendor'])
                ->index()
                ->comment('Role of user at the time of testimonial');

            /**
             * Content
             */
            $table->text('message')
                ->comment('Testimonial feedback message');

            $table->unsignedTinyInteger('rating')
                ->default(5)
                ->comment('Rating out of 5');

            /**
             * Moderation
             */
            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending')
                ->index()
                ->comment('Admin moderation status');

            $table->boolean('show_on_homepage')
                ->default(true)
                ->index()
                ->comment('Should be visible on homepage');

            /**
             * Admin actions
             */
            $table->timestamp('approved_at')
                ->nullable()
                ->comment('When testimonial was approved');

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Admin who approved the testimonial');

            /**
             * Soft delete for safety
             */
            $table->softDeletes();

            $table->timestamps();

            /**
             * Composite index for homepage queries
             */
            $table->index(
                ['role', 'status', 'show_on_homepage'],
                'idx_testimonials_homepage_filter'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
