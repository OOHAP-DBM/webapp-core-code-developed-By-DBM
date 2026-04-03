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
 public function generateMetaDescription(array $data): string
    {

        $brand = 'OOHAPP';
        $type = $data['type'] ?? null;
        $city = $data['city'] ?? null;
        $locality = $data['locality'] ?? null;
        $landmark = $data['landmark'] ?? null;
        $road = $data['road'] ?? null;
        $price = $data['price'] ?? null;
        // $title = $data['title'] ?? null;
        $count = $data['count'] ?? null;
        $vendor = $data['vendor'] ?? null;
        $inventory = $data['inventory'] ?? null;
        $campaign = $data['campaign'] ?? null;
        $locations = $data['locations'] ?? null;
        $duration = $data['duration'] ?? null;

        $description = '';

        switch ($type) {
            case 'hoarding':                
                if (  $locality && $city && $landmark && $road) {
                    $description = "Book premium hoardings in {$locality},{$city} near {$landmark[0]} and {$road} .Explore top outdoor advertising locations.";

                } elseif ($locality && $city && $landmark) {

                    $description = "Book premium hoardings in {$locality}, {$city} near {$landmark[0]}.Explore top outdoor advertising locations.";
                } elseif ($locality && $city) {
                    $description = "Book premium hoardings in {$locality}, {$city}.Explore top outdoor advertising locations.";
                } elseif ($city) {
                    $description = "Book premium hoardings in {$city}. Explore top outdoor advertising locations.";
                } else {
                    $description = "Book premium hoardings across India. Explore top outdoor advertising locations.";
                }
                break;
            case 'city':
                if ($city && $count) {
                    $description = "Explore {$count}+ hoardings in {$city} for outdoor advertising. Compare OOH & DOOH options on {$brand}.";
                } elseif ($city) {
                    $description = "Find hoardings and outdoor advertising in {$city} with {$brand}.";
                }
                break;
            case 'vendor':
                if ($vendor && $city && $inventory) {
                    $description = "View hoardings by {$vendor} in {$city}. Discover premium outdoor advertising locations on {$brand}.";
                } elseif ($vendor && $city) {
                    $description = "View outdoor advertising by {$vendor} in {$city} on {$brand}.";
                }
                break;
            case 'campaign':
                if ($campaign && $locations && $duration) {
                    $description = "Manage your advertising campaign across {$locations} for {$duration} with real-time tracking and flexible booking on {$brand}.";
                } elseif ($campaign && $locations) {
                    $description = "Manage your {$campaign} campaign in {$locations} with {$brand}.";
                }
                break;
            default:
                // fallback
                break;
        }

        // Fallback if not enough data
        if (!$description) {
            $description = "Discover outdoor advertising opportunities across India with {$brand}.";
        }

        // Ensure length 150–160 chars, trim intelligently
        $description = $this->trimToLength($description, 160);
        return $description;
    }

    /**
     * Trim string to max length without cutting words
     */
    protected function trimToLength(string $text, int $max = 160): string
    {
        if (mb_strlen($text) <= $max) {
            return $text;
        }
        $trimmed = mb_substr($text, 0, $max);
        // Avoid cutting last word
        if (($lastSpace = mb_strrpos($trimmed, ' ')) !== false) {
            $trimmed = mb_substr($trimmed, 0, $lastSpace);
        }
        return rtrim($trimmed, ',. ') . '.';
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
    public function generateMetaTitle(array $data): string
    {
        // $size = $hoarding->width . 'x' . $hoarding->height;
        // $type = ucfirst(str_replace('_', ' ', $hoarding->board_type));

          $brand = 'OOHAPP';
        $type = $data['type'] ?? null;
        $city = $data['city'] ?? null;
        $locality = $data['locality'] ?? null;
        $landmark = $data['landmark'] ?? null;
        $category = $data['category'] ?? null;
         $hoarding_type = $data['hoarding_type'] ?? null;
        //  $title = $data['title'] ?? null;
        //  $road = $data['road'] ?? null;
        //  $price = $data['price'] ?? null;
        // $road = $data['road'] ?? null;
        // $price = $data['price'] ?? null;
        // // $title = $data['title'] ?? null;
        // $count = $data['count'] ?? null;
        // $vendor = $data['vendor'] ?? null;
        // $inventory = $data['inventory'] ?? null;
        // $campaign = $data['campaign'] ?? null;
        // $locations = $data['locations'] ?? null;
        // $duration = $data['duration'] ?? null;
        $brand = 'OOHAPP';

        $title = '';

        switch ($type) {
            case 'hoarding':                
                if (  $locality && $city && $category) {
                    $title = "Hoarding in {$locality}, {$city} | {$category}  Advertising";

                } elseif ($category && $city) {

                    $title = "Hoarding in {$city} | {$category}  Advertising";
                }elseif ($category) {
                     $title = "{$category}  Advertising";
                }
                elseif ($city) {
                    $title = "Hoarding in {$city} | Outdoor Advertising";
                } else {
                    $title = "Book premium hoardings across India. Explore top outdoor advertising locations.";
                }
                break;
            case 'city':
                if ($city && $count) {
                    $title = "Explore {$count}+ hoardings in {$city} for outdoor advertising. Compare OOH & DOOH options on {$brand}.";
                } elseif ($city) {
                    $title = "Find hoardings and outdoor advertising in {$city} with {$brand}.";
                }
                break;
            case 'vendor':
                if ($vendor && $city && $inventory) {
                    $title = "View hoardings by {$vendor} in {$city}. Discover premium outdoor advertising locations on {$brand}.";
                } elseif ($vendor && $city) {
                    $title = "View outdoor advertising by {$vendor} in {$city} on {$brand}.";
                }
                break;
            case 'campaign':
                if ($campaign && $locations && $duration) {
                    $title = "Manage your advertising campaign across {$locations} for {$duration} with real-time tracking and flexible booking on {$brand}.";
                } elseif ($campaign && $locations) {
                    $title = "Manage your {$campaign} campaign in {$locations} with {$brand}.";
                }
                break;
            default:
                // fallback
                break;
        }

        // Fallback if not enough data
        if (!$title) {
            $title = "Discover outdoor advertising opportunities across India with {$brand}.";
        }

        // Ensure length 150–160 chars, trim intelligently
        $title = $this->trimToLength($title, 160);
        return $title;
    }

        
    //     return " Hoarding in {$hoarding->locality}, {$hoarding->city} | {$hoarding->hoarding_type}  Advertising";
    // }

    /**
     * Generate meta description
     */
    // protected function generateMetaDescription(Hoarding $hoarding): string
    // {
    //     $size = $hoarding->width . 'x' . $hoarding->height;
    //     $price = '₹' . number_format($hoarding->price_per_month, 0);
    //     $type = ucfirst(str_replace('_', ' ', $hoarding->board_type));
    //     $lit = $hoarding->is_lit ? 'illuminated ' : '';
        
    //     $description = "Book {$lit}{$type} hoarding in {$hoarding->location_name}, {$hoarding->city}. ";
    //     $description .= "Size: {$size}m. Price: {$price}/month. ";
        
    //     if ($hoarding->traffic_density) {
    //         $description .= ucfirst($hoarding->traffic_density) . " traffic area. ";
    //     }
        
    //     if ($hoarding->description) {
    //         $description .= Str::limit($hoarding->description, 50, '');
    //     }
        
    //     return $description;
    // }

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
