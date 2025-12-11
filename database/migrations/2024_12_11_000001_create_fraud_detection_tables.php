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
        // Fraud alerts table
        Schema::create('fraud_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('alert_type'); // high_value_frequency, gst_mismatch, failed_payments, suspicious_pattern, etc.
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['pending', 'reviewing', 'resolved', 'false_positive', 'confirmed_fraud'])->default('pending');
            
            // Target entity (polymorphic)
            $table->morphs('alertable'); // Can be User, Booking, Quotation, etc.
            
            // User/Entity details
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_type')->nullable(); // customer, vendor, admin
            $table->string('user_email')->nullable();
            $table->string('user_phone')->nullable();
            
            // Alert details
            $table->text('description');
            $table->json('metadata')->nullable(); // Store additional context
            $table->decimal('risk_score', 5, 2)->default(0); // 0-100 risk score
            $table->integer('confidence_level')->default(0); // 0-100 confidence
            
            // Related entities
            $table->json('related_bookings')->nullable();
            $table->json('related_transactions')->nullable();
            
            // Review information
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            
            // Actions taken
            $table->boolean('user_blocked')->default(false);
            $table->boolean('automatic_block')->default(false);
            $table->text('action_taken')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['alert_type', 'severity']);
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('risk_score');
            
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
        });

        // Fraud events log
        Schema::create('fraud_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type'); // booking_attempt, payment_attempt, profile_update, etc.
            $table->string('event_category')->default('general'); // booking, payment, authentication, profile
            
            // User information
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('session_id')->nullable();
            
            // Event details
            $table->json('event_data'); // Store all relevant data
            $table->boolean('is_suspicious')->default(false);
            $table->decimal('risk_score', 5, 2)->default(0);
            
            // Related entities
            $table->morphs('eventable'); // Polymorphic relation
            
            // Fraud alert reference
            $table->unsignedBigInteger('fraud_alert_id')->nullable();
            
            // Geolocation
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'created_at']);
            $table->index(['event_type', 'created_at']);
            $table->index(['is_suspicious', 'created_at']);
            $table->index('ip_address');
            $table->index('fraud_alert_id');
            
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('fraud_alert_id')->references('id')->on('fraud_alerts')->onDelete('set null');
        });

        // Risk profiles (track user behavior patterns)
        Schema::create('risk_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            
            // Overall risk assessment
            $table->decimal('overall_risk_score', 5, 2)->default(0); // 0-100
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->default('low');
            
            // Behavioral statistics
            $table->integer('total_bookings')->default(0);
            $table->integer('cancelled_bookings')->default(0);
            $table->integer('successful_payments')->default(0);
            $table->integer('failed_payments')->default(0);
            $table->integer('disputed_transactions')->default(0);
            
            // Financial metrics
            $table->decimal('total_spent', 12, 2)->default(0);
            $table->decimal('average_booking_value', 10, 2)->default(0);
            $table->decimal('highest_booking_value', 10, 2)->default(0);
            
            // Fraud indicators
            $table->integer('fraud_alerts_count')->default(0);
            $table->integer('confirmed_fraud_count')->default(0);
            $table->json('known_ip_addresses')->nullable();
            $table->json('known_devices')->nullable();
            
            // Verification status
            $table->boolean('email_verified')->default(false);
            $table->boolean('phone_verified')->default(false);
            $table->boolean('gst_verified')->default(false);
            $table->boolean('identity_verified')->default(false);
            
            // Trust indicators
            $table->integer('account_age_days')->default(0);
            $table->timestamp('first_booking_at')->nullable();
            $table->timestamp('last_booking_at')->nullable();
            $table->timestamp('last_fraud_check_at')->nullable();
            
            // Flags
            $table->boolean('is_blocked')->default(false);
            $table->boolean('requires_manual_review')->default(false);
            $table->text('block_reason')->nullable();
            $table->timestamp('blocked_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('risk_level');
            $table->index('overall_risk_score');
            $table->index(['is_blocked', 'requires_manual_review']);
            
            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Payment anomalies tracking
        Schema::create('payment_anomalies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('payment_id')->nullable();
            $table->string('anomaly_type'); // repeated_failure, velocity_check, amount_spike, etc.
            
            // Payment details
            $table->decimal('amount', 10, 2);
            $table->string('payment_method')->nullable();
            $table->string('status'); // failed, success, pending
            
            // Anomaly metrics
            $table->integer('failure_count_24h')->default(0);
            $table->integer('attempt_count_1h')->default(0);
            $table->decimal('amount_deviation_percent', 5, 2)->nullable();
            
            // Context
            $table->json('context')->nullable();
            $table->boolean('flagged_for_review')->default(false);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'created_at']);
            $table->index('anomaly_type');
            
            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // GST verification log
        Schema::create('gst_verifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('gst_number');
            
            // Verification details
            $table->enum('status', ['pending', 'verified', 'failed', 'mismatch'])->default('pending');
            $table->json('api_response')->nullable();
            
            // Company details from GST API
            $table->string('registered_name')->nullable();
            $table->string('registered_address')->nullable();
            $table->string('registered_state')->nullable();
            
            // Mismatch flags
            $table->boolean('name_mismatch')->default(false);
            $table->boolean('address_mismatch')->default(false);
            $table->text('mismatch_details')->nullable();
            
            // User provided details
            $table->string('user_provided_name')->nullable();
            $table->string('user_provided_address')->nullable();
            
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index('gst_number');
            
            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gst_verifications');
        Schema::dropIfExists('payment_anomalies');
        Schema::dropIfExists('risk_profiles');
        Schema::dropIfExists('fraud_events');
        Schema::dropIfExists('fraud_alerts');
    }
};
