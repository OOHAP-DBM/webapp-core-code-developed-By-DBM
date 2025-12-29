<?php

namespace App\Services;

use App\Models\Hoarding;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SEOService
{
    /**
     * Generate SEO metadata for a hoarding
     */
    public function generateHoardingMetadata(Hoarding $hoarding): array
    {
        $metaTitle = $this->generateMetaTitle($hoarding);
        $metaDescription = $this->generateMetaDescription($hoarding);
        $keywords = $this->generateKeywords($hoarding);
        $slug = $this->generateSlug($hoarding);
        
        return [
            'slug' => $slug,
            'meta_title' => Str::limit($metaTitle, 70, ''),
            'meta_description' => Str::limit($metaDescription, 160, ''),
            'meta_keywords' => $keywords,
        ];
    }

    /**
     * Generate SEO-friendly slug
     */
    public function generateSlug(Hoarding $hoarding): string
    {
        $base = Str::slug($hoarding->location_name . ' ' . $hoarding->city);
        $slug = $base;
        $counter = 1;
        
        // Ensure uniqueness
        while (DB::table('hoardings')
            ->where('slug', $slug)
            ->where('id', '!=', $hoarding->id)
            ->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Generate meta title
     */
    protected function generateMetaTitle(Hoarding $hoarding): string
    {
        $size = $hoarding->width . 'x' . $hoarding->height;
        $type = ucfirst(str_replace('_', ' ', $hoarding->board_type));
        
        return "{$type} Hoarding at {$hoarding->location_name}, {$hoarding->city} - {$size}m";
    }

    /**
     * Generate meta description
     */
    protected function generateMetaDescription(Hoarding $hoarding): string
    {
        $size = $hoarding->width . 'x' . $hoarding->height;
        $price = '₹' . number_format($hoarding->price_per_month, 0);
        $type = ucfirst(str_replace('_', ' ', $hoarding->board_type));
        $lit = $hoarding->is_lit ? 'illuminated ' : '';
        
        $description = "Book {$lit}{$type} hoarding in {$hoarding->location_name}, {$hoarding->city}. ";
        $description .= "Size: {$size}m. Price: {$price}/month. ";
        
        if ($hoarding->traffic_density) {
            $description .= ucfirst($hoarding->traffic_density) . " traffic area. ";
        }
        
        if ($hoarding->description) {
            $description .= Str::limit($hoarding->description, 50, '');
        }
        
        return $description;
    }

    /**
     * Generate meta keywords
     */
    protected function generateKeywords(Hoarding $hoarding): array
    {
        $keywords = [
            $hoarding->board_type . ' hoarding',
            'hoarding in ' . $hoarding->city,
            'outdoor advertising ' . $hoarding->city,
            $hoarding->location_name,
            $hoarding->city . ' billboard',
        ];
        
        if ($hoarding->is_lit) {
            $keywords[] = 'illuminated hoarding';
            $keywords[] = 'lit billboard';
        }
        
        if ($hoarding->traffic_density) {
            $keywords[] = $hoarding->traffic_density . ' traffic hoarding';
        }
        
        if ($hoarding->state) {
            $keywords[] = 'hoarding in ' . $hoarding->state;
        }
        
        // Add size-based keywords
        $size = $hoarding->width * $hoarding->height;
        if ($size >= 100) {
            $keywords[] = 'large hoarding';
        } elseif ($size >= 50) {
            $keywords[] = 'medium hoarding';
        }
        
        return array_unique($keywords);
    }

    /**
     * Generate structured data (JSON-LD) for hoarding
     */
    public function generateStructuredData(Hoarding $hoarding): array
    {
        $images = is_array($hoarding->images) ? $hoarding->images : json_decode($hoarding->images ?? '[]', true);
        
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $hoarding->location_name,
            'description' => $hoarding->description ?? $this->generateMetaDescription($hoarding),
            'image' => !empty($images) ? asset('storage/' . $images[0]) : null,
            'brand' => [
                '@type' => 'Brand',
                'name' => $hoarding->vendor->name ?? 'OohApp',
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => $hoarding->price_per_month,
                'priceCurrency' => 'INR',
                'availability' => $hoarding->status === 'available' 
                    ? 'https://schema.org/InStock' 
                    : 'https://schema.org/OutOfStock',
                'priceSpecification' => [
                    '@type' => 'UnitPriceSpecification',
                    'price' => $hoarding->price_per_month,
                    'priceCurrency' => 'INR',
                    'unitText' => 'MONTH',
                ],
            ],
            'category' => ucfirst($hoarding->board_type),
            'additionalProperty' => [
                [
                    '@type' => 'PropertyValue',
                    'name' => 'Width',
                    'value' => $hoarding->width . ' meters',
                ],
                [
                    '@type' => 'PropertyValue',
                    'name' => 'Height',
                    'value' => $hoarding->height . ' meters',
                ],
                [
                    '@type' => 'PropertyValue',
                    'name' => 'Location',
                    'value' => $hoarding->location_name . ', ' . $hoarding->city,
                ],
                [
                    '@type' => 'PropertyValue',
                    'name' => 'Illuminated',
                    'value' => $hoarding->is_lit ? 'Yes' : 'No',
                ],
            ],
        ];
        
        // Add geo coordinates if available
        if ($hoarding->latitude && $hoarding->longitude) {
            $structuredData['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => $hoarding->latitude,
                'longitude' => $hoarding->longitude,
            ];
        }
        
        return $structuredData;
    }

    /**
     * Generate Open Graph metadata
     */
    public function generateOpenGraphData(Hoarding $hoarding): array
    {
        $images = is_array($hoarding->images) ? $hoarding->images : json_decode($hoarding->images ?? '[]', true);
        $image = !empty($images) ? asset('storage/' . $images[0]) : asset('images/default-hoarding.jpg');
        
        return [
            'og:title' => $hoarding->meta_title ?? $this->generateMetaTitle($hoarding),
            'og:description' => $hoarding->meta_description ?? $this->generateMetaDescription($hoarding),
            'og:image' => $hoarding->og_image ?? $image,
            'og:url' => route('hoardings.show', $hoarding->slug ?? $hoarding->id),
            'og:type' => 'product',
            'og:site_name' => config('app.name'),
            'og:locale' => 'en_IN',
            'product:price:amount' => $hoarding->price_per_month,
            'product:price:currency' => 'INR',
        ];
    }

    /**
     * Generate Twitter Card metadata
     */
    public function generateTwitterCardData(Hoarding $hoarding): array
    {
        $images = is_array($hoarding->images) ? $hoarding->images : json_decode($hoarding->images ?? '[]', true);
        $image = !empty($images) ? asset('storage/' . $images[0]) : asset('images/default-hoarding.jpg');
        
        return [
            'twitter:card' => 'summary_large_image',
            'twitter:title' => $hoarding->meta_title ?? $this->generateMetaTitle($hoarding),
            'twitter:description' => $hoarding->meta_description ?? $this->generateMetaDescription($hoarding),
            'twitter:image' => $hoarding->og_image ?? $image,
        ];
    }

    /**
     * Track page view
     */
    public function trackPageView(Hoarding $hoarding, ?array $additionalData = []): void
    {
        // Increment view count
        $hoarding->increment('view_count');
        $hoarding->update(['last_viewed_at' => now()]);
        
        // Record detailed view
        DB::table('hoarding_page_views')->insert([
            'hoarding_id' => $hoarding->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referer' => request()->header('referer'),
            'utm_source' => request('utm_source'),
            'utm_medium' => request('utm_medium'),
            'utm_campaign' => request('utm_campaign'),
            'device_type' => $this->detectDeviceType(),
            'user_id' => auth()->id(),
            'viewed_at' => now(),
        ]);
    }

    /**
     * Detect device type from user agent
     */
    protected function detectDeviceType(): string
    {
        $userAgent = request()->userAgent();
        
        if (preg_match('/mobile/i', $userAgent)) {
            return 'mobile';
        } elseif (preg_match('/tablet|ipad/i', $userAgent)) {
            return 'tablet';
        }
        
        return 'desktop';
    }

    /**
     * Generate breadcrumbs for hoarding page
     */
    public function generateBreadcrumbs(Hoarding $hoarding): array
    {
        return [
            [
                'label' => 'Home',
                'url' => route('home'),
            ],
            [
                'label' => 'Hoardings',
                'url' => route('hoardings.index'),
            ],
            [
                'label' => $hoarding->city,
                'url' => route('hoardings.index', ['city' => $hoarding->city]),
            ],
            [
                'label' => $hoarding->location_name,
                'url' => null, // Current page
            ],
        ];
    }

    /**
     * Update sitemap entry for hoarding
     */
    public function updateSitemapEntry(Hoarding $hoarding): void
    {
        $url = route('hoardings.show', $hoarding->slug ?? $hoarding->id);
        
        DB::table('sitemap_entries')->updateOrInsert(
            [
                'type' => 'hoarding',
                'reference_id' => $hoarding->id,
            ],
            [
                'loc' => $url,
                'lastmod' => $hoarding->updated_at,
                'changefreq' => 'weekly',
                'priority' => 0.8,
                'is_active' => $hoarding->status === 'approved' && $hoarding->status === 'available',
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Generate location page metadata
     */
    public function generateLocationPageMetadata(string $city, ?string $area = null): array
    {
        $hoardingCount = DB::table('hoardings')
            ->where('city', $city)
            ->when($area, fn($q) => $q->where('address', 'LIKE', "%{$area}%"))
            ->where('approval_status', 'approved')
            ->count();
        
        $priceRange = DB::table('hoardings')
            ->where('city', $city)
            ->when($area, fn($q) => $q->where('address', 'LIKE', "%{$area}%"))
            ->where('approval_status', 'approved')
            ->selectRaw('MIN(price_per_month) as min_price, MAX(price_per_month) as max_price')
            ->first();
        
        $location = $area ? "{$area}, {$city}" : $city;
        
        $metaTitle = "Outdoor Advertising Hoardings in {$location} - {$hoardingCount}+ Locations Available";
        $metaDescription = "Find the best outdoor advertising hoardings in {$location}. ";
        $metaDescription .= "Choose from {$hoardingCount}+ verified locations. ";
        
        if ($priceRange && $priceRange->min_price && $priceRange->max_price) {
            $minPrice = number_format($priceRange->min_price, 0);
            $maxPrice = number_format($priceRange->max_price, 0);
            $metaDescription .= "Price range: ₹{$minPrice} - ₹{$maxPrice}/month. ";
        }
        
        $metaDescription .= "Billboard, digital screens, transit advertising and more.";
        
        return [
            'meta_title' => Str::limit($metaTitle, 70, ''),
            'meta_description' => Str::limit($metaDescription, 160, ''),
            'hoarding_count' => $hoardingCount,
            'min_price' => $priceRange->min_price ?? null,
            'max_price' => $priceRange->max_price ?? null,
        ];
    }

    /**
     * Get popular locations for sitemap/navigation
     */
    public function getPopularLocations(int $limit = 20): array
    {
        return DB::table('hoardings')
            ->select('city', DB::raw('COUNT(*) as hoarding_count'))
            ->where('approval_status', 'approved')
            ->groupBy('city')
            ->orderBy('hoarding_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($location) {
                return [
                    'city' => $location->city,
                    'hoarding_count' => $location->hoarding_count,
                    'slug' => Str::slug($location->city),
                    'url' => route('hoardings.index', ['city' => $location->city]),
                ];
            })
            ->toArray();
    }
}
