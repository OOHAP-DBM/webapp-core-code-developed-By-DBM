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
        Schema::create('vendor_kyc', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            
            // Business Information
            $table->enum('business_type', ['individual', 'proprietorship', 'partnership', 'pvt_ltd', 'public_ltd', 'llp'])->comment('Type of business entity');
            $table->string('business_name', 200);
            $table->string('gst_number', 15)->nullable()->unique();
            $table->string('pan_number', 10)->unique();
            $table->string('legal_name', 200)->comment('Legal entity name as per PAN');
            
            // Contact Information
            $table->string('contact_name', 100);
            $table->string('contact_email', 100);
            $table->string('contact_phone', 15);
            
            // Address
            $table->text('address')->comment('Complete business address');
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('pincode', 6)->nullable();
            
            // Bank Account Details (for vendor payouts)
            $table->string('account_holder_name', 200);
            $table->text('account_number')->comment('Encrypted bank account number');
            $table->string('ifsc', 11);
            $table->string('bank_name', 100);
            $table->enum('account_type', ['savings', 'current'])->default('current');
            
            // Verification
            $table->enum('verification_status', ['pending', 'under_review', 'approved', 'rejected', 'resubmission_required'])->default('pending');
            $table->json('verification_details')->nullable()->comment('Admin remarks, rejection reasons, etc.');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null')->comment('Admin who verified');
            
            // Razorpay Sub-account
            $table->string('razorpay_subaccount_id', 100)->nullable()->comment('Razorpay Route sub-account ID for direct payouts');
            
            $table->timestamps();
            
            // Indexes
            $table->unique('vendor_id');
            $table->index('verification_status');
            $table->index('submitted_at');
            $table->index('gst_number');
            $table->index('pan_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_kyc');
    }
};
