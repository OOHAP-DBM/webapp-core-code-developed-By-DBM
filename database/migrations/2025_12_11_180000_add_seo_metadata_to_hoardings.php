<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /* ================= SEO FIELDS ================= */
        Schema::table('hoardings', function (Blueprint $table) {
            // $table->string('slug')->unique()->nullable();
            // $table->string('meta_title', 70)->nullable();
            // $table->text('meta_description')->nullable();
            // $table->text('meta_keywords')->nullable();
            // $table->string('og_image')->nullable();
            // $table->string('canonical_url')->nullable();
            // $table->boolean('noindex')->default(false);

            // $table->integer('view_count')->default(0);
            $table->timestamp('last_viewed_at')->nullable();
        });

        /* ================= PAGE VIEWS ================= */
        Schema::create('hoarding_page_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoarding_id')->constrained('hoardings')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referer')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('device_type')->nullable();
            $table->timestamp('viewed_at');

            $table->index(['hoarding_id', 'viewed_at']);
            $table->index(['hoarding_id', 'user_id']);
        });

        /* ================= SITEMAP ================= */
        Schema::create('sitemap_entries', function (Blueprint $table) {
            $table->id();
            $table->string('loc');
            $table->timestamp('lastmod')->nullable();
            $table->enum('changefreq', ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'])->default('weekly');
            $table->decimal('priority', 2, 1)->default(0.5);
            $table->string('type');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'reference_id']);
            $table->index('is_active');
        });

        /* ================= LOCATION PAGES ================= */
        Schema::create('location_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('area')->nullable();
            $table->string('meta_title', 70);
            $table->text('meta_description');
            $table->text('meta_keywords')->nullable();
            $table->text('content')->nullable();
            $table->string('header_image')->nullable();
            $table->json('highlights')->nullable();
            $table->integer('hoarding_count')->default(0);
            $table->decimal('min_price', 10, 2)->nullable();
            $table->decimal('max_price', 10, 2)->nullable();
            $table->integer('view_count')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index('city');
            $table->index('is_published');
        });

        /* ================= BREADCRUMBS ================= */
        Schema::create('breadcrumb_configs', function (Blueprint $table) {
            $table->id();
            $table->string('route_name');
            $table->string('label');
            $table->string('parent_route')->nullable();
            $table->integer('position')->default(0);
            $table->json('params')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('route_name');
        });

        DB::table('breadcrumb_configs')->insert([
            ['route_name' => 'home', 'label' => 'Home',   'parent_route' => null, 'position' => 0, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['route_name' => 'hoardings.index', 'label' => 'Hoardings', 'parent_route' => 'home', 'position' => 1, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['route_name' => 'hoardings.show', 'label' => '{location_name}', 'parent_route' => 'hoardings.index', 'position' => 2, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['route_name' => 'hoardings.city', 'label' => 'Hoardings in {city}', 'parent_route' => 'hoardings.index', 'position' => 2, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('breadcrumb_configs');
        Schema::dropIfExists('location_pages');
        Schema::dropIfExists('sitemap_entries');
        Schema::dropIfExists('hoarding_page_views');

        Schema::table('hoardings', function (Blueprint $table) {
            $table->dropColumn([
                'slug',
                'meta_title',
                'meta_description',
                'meta_keywords',
                'og_image',
                'canonical_url',
                'noindex',
                'view_count',
                'last_viewed_at',
            ]);
        });
    }
};
