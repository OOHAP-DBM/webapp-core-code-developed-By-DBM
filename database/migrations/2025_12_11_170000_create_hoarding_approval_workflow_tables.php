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
        // Add approval workflow columns to hoardings table
        Schema::table('hoardings', function (Blueprint $table) {
            $table->enum('approval_status', ['draft', 'pending', 'under_verification', 'approved', 'rejected'])
                  ->default('draft')
                  ->after('status');
            $table->unsignedBigInteger('current_version')->default(1)->after('approval_status');
            $table->timestamp('submitted_at')->nullable()->after('current_version');
            $table->timestamp('verified_at')->nullable()->after('submitted_at');
            $table->timestamp('approved_at')->nullable()->after('verified_at');
            $table->timestamp('rejected_at')->nullable()->after('approved_at');
            $table->foreignId('verified_by')->nullable()->constrained('users')->after('rejected_at');
            $table->foreignId('approved_by')->nullable()->constrained('users')->after('verified_by');
            $table->foreignId('rejected_by')->nullable()->constrained('users')->after('approved_by');
            $table->text('rejection_reason')->nullable()->after('rejected_by');
            $table->text('admin_notes')->nullable()->after('rejection_reason');
            
            $table->index('approval_status');
            $table->index(['vendor_id', 'approval_status']);
            $table->index('submitted_at');
        });

        // Hoarding versions table - tracks edit history
        Schema::create('hoarding_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoarding_id')->constrained('hoardings')->onDelete('cascade');
            $table->unsignedBigInteger('version_number');
            $table->enum('status', ['draft', 'pending', 'under_verification', 'approved', 'rejected']);
            
            // Snapshot of hoarding data at this version
            $table->string('location_name');
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('pincode');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('width', 8, 2);
            $table->decimal('height', 8, 2);
            $table->string('size_category')->nullable();
            $table->enum('board_type', ['billboard', 'digital', 'transit', 'street_furniture', 'wallscape', 'mobile']);
            $table->boolean('is_lit')->default(false);
            $table->decimal('price_per_month', 10, 2);
            $table->json('images')->nullable();
            $table->text('description')->nullable();
            $table->json('amenities')->nullable();
            $table->string('traffic_density')->nullable();
            $table->string('visibility_rating')->nullable();
            $table->json('target_audience')->nullable();
            
            // Version metadata
            $table->string('change_type')->nullable(); // 'create', 'edit', 'resubmit'
            $table->text('change_summary')->nullable();
            $table->json('changed_fields')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->foreignId('rejected_by')->nullable()->constrained('users');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            
            $table->unique(['hoarding_id', 'version_number']);
            $table->index(['hoarding_id', 'status']);
            $table->index('created_at');
        });

        // Approval workflow logs - tracks all status changes and actions
        Schema::create('hoarding_approval_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoarding_id')->constrained('hoardings')->onDelete('cascade');
            $table->unsignedBigInteger('version_number')->nullable();
            $table->enum('action', [
                'submitted',
                'verification_started',
                'verification_completed',
                'approved',
                'rejected',
                'resubmitted',
                'auto_approved',
                'flagged',
                'unflagged',
                'assigned',
                'unassigned',
                'comment_added'
            ]);
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->foreignId('performed_by')->constrained('users');
            $table->string('performer_role')->nullable(); // 'vendor', 'admin', 'super_admin', 'system'
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Additional context (IP, device, etc.)
            $table->timestamp('performed_at');
            
            $table->index(['hoarding_id', 'performed_at']);
            $table->index(['performed_by', 'performed_at']);
            $table->index('action');
        });

        // Admin verification assignments
        Schema::create('hoarding_verification_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoarding_id')->constrained('hoardings')->onDelete('cascade');
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['assigned', 'in_progress', 'completed', 'reassigned']);
            $table->timestamp('assigned_at');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('assigned_by')->constrained('users');
            $table->text('assignment_notes')->nullable();
            $table->integer('priority')->default(3); // 1=urgent, 2=high, 3=normal, 4=low
            $table->timestamps();
            
            $table->index(['admin_id', 'status']);
            $table->index(['hoarding_id', 'status']);
            $table->index('assigned_at');
        });

        // Approval checklist items
        Schema::create('hoarding_approval_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoarding_id')->constrained('hoardings')->onDelete('cascade');
            $table->unsignedBigInteger('version_number');
            $table->string('checklist_item'); // e.g., 'location_verified', 'images_quality', 'pricing_reasonable'
            $table->enum('status', ['pending', 'passed', 'failed', 'na']);
            $table->text('notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            
            $table->index(['hoarding_id', 'version_number']);
            $table->index('checklist_item');
        });

        // Rejection reasons (predefined templates)
        Schema::create('hoarding_rejection_templates', function (Blueprint $table) {
            $table->id();
            $table->string('category'); // 'location', 'images', 'pricing', 'description', 'compliance'
            $table->string('title');
            $table->text('message');
            $table->boolean('requires_action')->default(true); // Does vendor need to fix this?
            $table->json('suggested_actions')->nullable(); // What vendor should do
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0);
            $table->timestamps();
            
            $table->index('category');
            $table->index('is_active');
        });

        // Approval workflow settings
        Schema::create('hoarding_approval_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->string('type')->default('string'); // 'string', 'integer', 'boolean', 'json'
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default checklist items
        DB::table('hoarding_approval_settings')->insert([
            [
                'key' => 'auto_approve_trusted_vendors',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Automatically approve hoardings from trusted vendors',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'trusted_vendor_rating_threshold',
                'value' => '4.5',
                'type' => 'float',
                'description' => 'Minimum rating for auto-approval eligibility',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'trusted_vendor_min_approved',
                'value' => '10',
                'type' => 'integer',
                'description' => 'Minimum approved hoardings for trusted status',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'verification_sla_hours',
                'value' => '48',
                'type' => 'integer',
                'description' => 'SLA for verification process (hours)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'approval_sla_hours',
                'value' => '24',
                'type' => 'integer',
                'description' => 'SLA for final approval (hours)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'required_checklist_items',
                'value' => json_encode([
                    'location_verified',
                    'images_quality',
                    'dimensions_accurate',
                    'pricing_reasonable',
                    'description_complete',
                    'legal_compliance'
                ]),
                'type' => 'json',
                'description' => 'Required checklist items for approval',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Insert default rejection templates
        DB::table('hoarding_rejection_templates')->insert([
            [
                'category' => 'images',
                'title' => 'Poor Image Quality',
                'message' => 'The uploaded images are of poor quality or unclear. Please upload high-resolution images showing the hoarding clearly.',
                'requires_action' => true,
                'suggested_actions' => json_encode(['Upload clear, high-resolution images', 'Include multiple angles of the hoarding']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'location',
                'title' => 'Location Not Verified',
                'message' => 'The hoarding location could not be verified. Please ensure the address and GPS coordinates are accurate.',
                'requires_action' => true,
                'suggested_actions' => json_encode(['Verify and update the exact address', 'Confirm GPS coordinates on map']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'dimensions',
                'title' => 'Incorrect Dimensions',
                'message' => 'The hoarding dimensions appear to be incorrect or not matching the images provided.',
                'requires_action' => true,
                'suggested_actions' => json_encode(['Measure and update accurate dimensions', 'Ensure dimensions match the actual hoarding']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'pricing',
                'title' => 'Pricing Outside Market Range',
                'message' => 'The pricing is significantly higher or lower than market rates for similar hoardings in this location.',
                'requires_action' => true,
                'suggested_actions' => json_encode(['Review market rates for similar hoardings', 'Adjust pricing to competitive levels']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'description',
                'title' => 'Incomplete Description',
                'message' => 'The hoarding description is incomplete or lacks important details about the location, visibility, or features.',
                'requires_action' => true,
                'suggested_actions' => json_encode(['Add detailed description', 'Include traffic information and visibility details']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'compliance',
                'title' => 'Legal Compliance Issues',
                'message' => 'There are concerns regarding legal compliance or permits for this hoarding location.',
                'requires_action' => true,
                'suggested_actions' => json_encode(['Provide valid permits/licenses', 'Ensure compliance with local regulations']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'duplicate',
                'title' => 'Duplicate Listing',
                'message' => 'This hoarding appears to be a duplicate of an existing listing.',
                'requires_action' => false,
                'suggested_actions' => json_encode(['Remove duplicate listing', 'Update existing listing if needed']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hoarding_approval_settings');
        Schema::dropIfExists('hoarding_rejection_templates');
        Schema::dropIfExists('hoarding_approval_checklists');
        Schema::dropIfExists('hoarding_verification_assignments');
        Schema::dropIfExists('hoarding_approval_logs');
        Schema::dropIfExists('hoarding_versions');
        
        Schema::table('hoardings', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['rejected_by']);
            $table->dropIndex(['approval_status']);
            $table->dropIndex(['vendor_id', 'approval_status']);
            $table->dropIndex(['submitted_at']);
            $table->dropColumn([
                'approval_status',
                'current_version',
                'submitted_at',
                'verified_at',
                'approved_at',
                'rejected_at',
                'verified_by',
                'approved_by',
                'rejected_by',
                'rejection_reason',
                'admin_notes',
            ]);
        });
    }
};
