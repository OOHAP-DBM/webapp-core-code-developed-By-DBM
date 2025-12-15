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
        Schema::create('vendor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Onboarding status
            $table->enum('onboarding_status', [
                'draft',                // Onboarding started but not completed
                'pending_approval',     // Submitted and waiting for admin review
                'approved',             // Admin approved, full access granted
                'rejected',             // Admin rejected
                'suspended'             // Account suspended by admin
            ])->default('draft');
            
            $table->integer('onboarding_step')->default(1); // Current step (1-5)
            $table->timestamp('onboarding_completed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            
            // Step 1: Company Details
            $table->string('company_name')->nullable();
            $table->string('company_registration_number')->nullable();
            $table->enum('company_type', [
                'proprietorship',
                'partnership',
                'private_limited',
                'public_limited',
                'llp',
                'other'
            ])->nullable();
            $table->string('gstin', 15)->nullable();
            $table->string('pan', 10)->nullable();
            $table->text('registered_address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode', 10)->nullable();
            $table->string('website')->nullable();
            
            // Step 2: Business Information
            $table->year('year_established')->nullable();
            $table->integer('total_hoardings')->default(0);
            $table->json('service_cities')->nullable(); // Array of cities
            $table->json('hoarding_types')->nullable(); // billboard, unipole, gantry, etc.
            $table->text('business_description')->nullable();
            $table->string('contact_person_name')->nullable();
            $table->string('contact_person_designation')->nullable();
            $table->string('contact_person_phone', 15)->nullable();
            $table->string('contact_person_email')->nullable();
            
            // Step 3: KYC / Document Upload
            $table->string('pan_card_document')->nullable();
            $table->string('gst_certificate')->nullable();
            $table->string('company_registration_certificate')->nullable();
            $table->string('address_proof')->nullable(); // Electricity bill, rent agreement
            $table->string('cancelled_cheque')->nullable();
            $table->string('owner_id_proof')->nullable(); // Aadhar/Passport
            $table->string('other_documents')->nullable(); // JSON array of additional docs
            $table->boolean('kyc_verified')->default(false);
            $table->timestamp('kyc_verified_at')->nullable();
            
            // Step 4: Bank Details
            $table->string('bank_name')->nullable();
            $table->string('account_holder_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('ifsc_code', 11)->nullable();
            $table->string('branch_name')->nullable();
            $table->enum('account_type', ['savings', 'current'])->nullable();
            $table->boolean('bank_verified')->default(false);
            
            // Step 5: Agreement / Terms
            $table->boolean('terms_accepted')->default(false);
            $table->timestamp('terms_accepted_at')->nullable();
            $table->string('terms_ip_address', 45)->nullable();
            $table->boolean('commission_agreement_accepted')->default(false);
            $table->decimal('commission_percentage', 5, 2)->default(10.00); // Default 10%
            $table->text('special_terms')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['user_id', 'onboarding_status']);
            $table->index('onboarding_status');
            $table->index('gstin');
            $table->index('pan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_profiles');
    }
};
