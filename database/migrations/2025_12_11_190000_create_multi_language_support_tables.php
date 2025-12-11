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
        // 1. Languages table - Store supported languages
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // en, hi, ta, te, kn, etc.
            $table->string('name', 100); // English, Hindi, Tamil, etc.
            $table->string('native_name', 100); // English, हिन्दी, தமிழ், etc.
            $table->string('flag_icon', 50)->nullable(); // Flag emoji or icon class
            $table->enum('direction', ['ltr', 'rtl'])->default('ltr'); // Text direction
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index('is_default');
        });

        // 2. Translations table - Store dynamic content translations
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('key', 500); // Translation key (e.g., 'hoarding.location')
            $table->string('locale', 10); // Language code
            $table->text('value'); // Translated text
            $table->string('group', 100)->nullable(); // Group (customer, vendor, admin)
            $table->enum('type', ['string', 'text', 'html'])->default('string');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['key', 'locale', 'group']);
            $table->index('locale');
            $table->index('group');
        });

        // 3. User language preferences
        Schema::table('users', function (Blueprint $table) {
            $table->string('preferred_language', 10)->default('en')->after('email');
            $table->index('preferred_language');
        });

        // 4. Model translations - For hoarding, vendor profiles, etc.
        Schema::create('model_translations', function (Blueprint $table) {
            $table->id();
            $table->morphs('translatable'); // translatable_type, translatable_id
            $table->string('locale', 10);
            $table->string('field', 100); // Field name (title, description, etc.)
            $table->text('value');
            $table->timestamps();

            $table->unique(['translatable_type', 'translatable_id', 'locale', 'field'], 'model_trans_unique');
            $table->index('locale');
        });

        // 5. Translation requests - For vendor/user submitted translations
        Schema::create('translation_requests', function (Blueprint $table) {
            $table->id();
            $table->string('key', 500);
            $table->string('locale', 10);
            $table->string('group', 100)->nullable();
            $table->text('current_value')->nullable();
            $table->text('suggested_value');
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('locale');
        });

        // 6. Language usage analytics
        Schema::create('language_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 10);
            $table->string('user_type', 50)->nullable(); // customer, vendor, admin, guest
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('country', 2)->nullable(); // Country code
            $table->string('browser_language', 50)->nullable(); // Browser's preferred language
            $table->enum('detection_method', ['manual', 'browser', 'user_preference', 'ip_location'])->default('manual');
            $table->timestamp('used_at');

            $table->index('locale');
            $table->index('used_at');
            $table->index(['user_type', 'locale']);
        });

        // Insert default languages
        DB::table('languages')->insert([
            [
                'code' => 'en',
                'name' => 'English',
                'native_name' => 'English',
                'flag_icon' => '🇬🇧',
                'direction' => 'ltr',
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'hi',
                'name' => 'Hindi',
                'native_name' => 'हिन्दी',
                'flag_icon' => '🇮🇳',
                'direction' => 'ltr',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ta',
                'name' => 'Tamil',
                'native_name' => 'தமிழ்',
                'flag_icon' => '🇮🇳',
                'direction' => 'ltr',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'te',
                'name' => 'Telugu',
                'native_name' => 'తెలుగు',
                'flag_icon' => '🇮🇳',
                'direction' => 'ltr',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'kn',
                'name' => 'Kannada',
                'native_name' => 'ಕನ್ನಡ',
                'flag_icon' => '🇮🇳',
                'direction' => 'ltr',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'mr',
                'name' => 'Marathi',
                'native_name' => 'मराठी',
                'flag_icon' => '🇮🇳',
                'direction' => 'ltr',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'bn',
                'name' => 'Bengali',
                'native_name' => 'বাংলা',
                'flag_icon' => '🇮🇳',
                'direction' => 'ltr',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'gu',
                'name' => 'Gujarati',
                'native_name' => 'ગુજરાતી',
                'flag_icon' => '🇮🇳',
                'direction' => 'ltr',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 8,
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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('preferred_language');
        });

        Schema::dropIfExists('language_usage_logs');
        Schema::dropIfExists('translation_requests');
        Schema::dropIfExists('model_translations');
        Schema::dropIfExists('translations');
        Schema::dropIfExists('languages');
    }
};
