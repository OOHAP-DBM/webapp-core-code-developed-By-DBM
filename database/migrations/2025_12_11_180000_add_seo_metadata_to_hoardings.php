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
        // Add SEO columns to hoardings table
        Schema::table('hoardings', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable()->after('location_name');
            $table->string('meta_title', 70)->nullable()->after('slug');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->json('meta_keywords')->nullable()->after('meta_description');
            $table->string('og_image')->nullable()->after('meta_keywords');
            $table->boolean('index_page')->default(true)->after('og_image');
            $table->integer('view_count')->default(0)->after('index_page');
            $table->timestamp('last_viewed_at')->nullable()->after('view_count');
            
            $table->index('slug');
            $table->index(['city', 'board_type']);
            $table->index('view_count');
        });

        // Create hoarding_page_views table for analytics
        Schema::create('hoarding_page_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoarding_id')->constrained('hoardings')->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referer')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('device_type')->nullable(); // mobile, tablet, desktop
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('viewed_at');
            
            $table->index('hoarding_id');
            $table->index('viewed_at');
            $table->index(['hoarding_id', 'viewed_at']);
        });

        // Create sitemap tracking table
        Schema::create('sitemap_entries', function (Blueprint $table) {
            $table->id();
            $table->string('loc'); // URL
            $table->timestamp('lastmod')->nullable();
            $table->enum('changefreq', ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'])->default('weekly');
            $table->decimal('priority', 2, 1)->default(0.5);
            $table->string('type'); // 'hoarding', 'location', 'static'
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of hoarding/location
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('type');
            $table->index(['type', 'reference_id']);
            $table->index('is_active');
        });

        // Create location pages table for SEO landing pages
        Schema::create('location_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('area')->nullable();
            $table->string('meta_title', 70);
            $table->text('meta_description');
            $table->json('meta_keywords')->nullable();
            $table->text('content')->nullable(); // Rich content for the location page
            $table->string('header_image')->nullable();
            $table->json('highlights')->nullable(); // Key selling points
            $table->integer('hoarding_count')->default(0);
            $table->decimal('min_price', 10, 2)->nullable();
            $table->decimal('max_price', 10, 2)->nullable();
            $table->integer('view_count')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            
            $table->index('slug');
            $table->index('city');
            $table->index('is_published');
        });

        // Create breadcrumb configuration table
        Schema::create('breadcrumb_configs', function (Blueprint $table) {
            $table->id();
            $table->string('route_name');
            $table->string('label');
            $table->string('parent_route')->nullable();
            $table->integer('position')->default(0);
            $table->json('params')->nullable(); // Dynamic params
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('route_name');
        });

        // Insert default breadcrumb configurations
        DB::table('breadcrumb_configs')->insert([
            [
                'route_name' => 'home',
                'label' => 'Home',
                'parent_route' => null,
                'position' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'route_name' => 'hoardings.index',
                'label' => 'Hoardings',
                'parent_route' => 'home',
                'position' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'route_name' => 'hoardings.show',
                'label' => '{location_name}',
                'parent_route' => 'hoardings.index',
                'position' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'route_name' => 'hoardings.city',
                'label' => 'Hoardings in {city}',
                'parent_route' => 'hoardings.index',
                'position' => 2,
                'is_active' => true,
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
        Schema::dropIfExists('breadcrumb_configs');
        Schema::dropIfExists('location_pages');
        Schema::dropIfExists('sitemap_entries');
        Schema::dropIfExists('hoarding_page_views');
        
        Schema::table('hoardings', function (Blueprint $table) {
            $table->dropIndex(['slug']);
            $table->dropIndex(['city', 'board_type']);
            $table->dropIndex(['view_count']);
            $table->dropColumn([
                'slug',
                'meta_title',
                'meta_description',
                'meta_keywords',
                'og_image',
                'index_page',
                'view_count',
                'last_viewed_at',
            ]);
        });
    }
};
