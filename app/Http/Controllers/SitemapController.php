<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SitemapController extends Controller
{
    /**
     * Generate and serve XML sitemap index
     */
    public function index()
    {
        $sitemaps = [
            [
                'loc' => route('sitemap.static'),
                'lastmod' => Carbon::now()->toAtomString(),
            ],
            [
                'loc' => route('sitemap.hoardings'),
                'lastmod' => $this->getLastModified('hoardings'),
            ],
            [
                'loc' => route('sitemap.locations'),
                'lastmod' => $this->getLastModified('hoardings'),
            ],
        ];

        return response()->view('sitemap.index', compact('sitemaps'))
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Generate static pages sitemap
     */
    public function static()
    {
        $urls = [
            [
                'loc' => route('home'),
                'lastmod' => Carbon::now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
            [
                'loc' => route('hoardings.index'),
                'lastmod' => Carbon::now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '0.9',
            ],
            [
                'loc' => route('search'),
                'lastmod' => Carbon::now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ],
            [
                'loc' => route('hoardings.map'),
                'lastmod' => Carbon::now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ],
            [
                'loc' => route('dooh.index'),
                'lastmod' => Carbon::now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ],
        ];

        return response()->view('sitemap.urlset', compact('urls'))
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Generate hoardings sitemap
     */
    public function hoardings()
    {
        $hoardings = DB::table('hoardings')
            ->where('approval_status', 'approved')
            ->where('status', 'available')
            ->where('index_page', true)
            ->select('id', 'slug', 'updated_at', 'view_count')
            ->orderBy('updated_at', 'desc')
            ->limit(10000) // Google's limit per sitemap
            ->get();

        $urls = $hoardings->map(function ($hoarding) {
            return [
                'loc' => route('hoardings.show', $hoarding->slug ?? $hoarding->id),
                'lastmod' => Carbon::parse($hoarding->updated_at)->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => $this->calculatePriority($hoarding->view_count),
            ];
        })->toArray();

        return response()->view('sitemap.urlset', compact('urls'))
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Generate locations sitemap
     */
    public function locations()
    {
        $locations = DB::table('hoardings')
            ->where('approval_status', 'approved')
            ->select('city', DB::raw('MAX(updated_at) as updated_at'), DB::raw('COUNT(*) as count'))
            ->groupBy('city')
            ->having('count', '>', 0)
            ->get();

        $urls = $locations->map(function ($location) {
            return [
                'loc' => route('hoardings.index', ['city' => $location->city]),
                'lastmod' => Carbon::parse($location->updated_at)->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => $location->count > 10 ? '0.8' : '0.7',
            ];
        })->toArray();

        return response()->view('sitemap.urlset', compact('urls'))
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Get last modified date for a resource type
     */
    protected function getLastModified(string $table): string
    {
        $lastMod = DB::table($table)
            ->where('approval_status', 'approved')
            ->max('updated_at');

        return $lastMod ? Carbon::parse($lastMod)->toAtomString() : Carbon::now()->toAtomString();
    }

    /**
     * Calculate priority based on view count
     */
    protected function calculatePriority(int $viewCount): string
    {
        if ($viewCount > 1000) return '0.9';
        if ($viewCount > 500) return '0.8';
        if ($viewCount > 100) return '0.7';
        if ($viewCount > 10) return '0.6';
        return '0.5';
    }
}
