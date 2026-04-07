<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Hoarding;
use App\Models\User;
use Modules\DOOH\Models\DOOHScreen;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Cart\Services\CartService;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Cache;


class HomeController extends Controller
{
    /**
     * Display the public homepage.
     *
     * @return View
     */
    public function index(Request $request, CartService $cartService)
    {
        // Filters (add more as needed)
        $page = $request->get('page', 1);
        $bestHoardings = Hoarding::select([
            'id',
            'title',
            'slug',
            'address',
            'city',
            'monthly_price',
            'base_monthly_price',
            'hoarding_type',
            'vendor_id',
            'created_at',
            'is_recommended',
            'view_count',
            'expected_eyeball'
        ])
            ->where('status', 'active')
            ->with([
                'vendor:id,name,company_name',
                'hoardingMedia:id,hoarding_id,file_path',
                'doohScreen:id,hoarding_id,price_per_slot',
                'doohScreen.media:id,dooh_screen_id,file_path'
            ])
            ->orderByDesc('created_at')
            ->paginate(8, ['*'], 'page', $page);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('components.customer.hoarding-grid', compact('bestHoardings'))->render(),
                'pagination' => view('pagination.vendor-compact', ['paginator' => $bestHoardings])->render(),
            ]);
        }

        // Get platform statistics with caching (unchanged)
        $stats = cache()->remember('homepage_stats', 600, function () {
            $result = [
                'total_hoardings' => Hoarding::where('status', 'active')->count(),
                'total_vendors' => User::whereHas('roles', function ($q) {
                    $q->where('name', 'vendor');
                })->count(),
                'total_bookings' => 0,
            ];
            if (class_exists('\\Modules\\Bookings\\Models\\Booking')) {
                $result['total_bookings'] = \Modules\Bookings\Models\Booking::whereIn('status', ['active', 'completed'])->count();
            }
            return $result;
        });

        // Add availability status and next available date using HoardingAvailabilityService
        $availabilityService = app(\Modules\Hoardings\Services\HoardingAvailabilityService::class);
        $today = now()->toDateString();

        $bestHoardings->setCollection(
            $bestHoardings->getCollection()->map(function ($hoarding) use ($availabilityService, $today) {
                if ($hoarding->hoarding_type === 'dooh') {
                    $hoarding->price_type = 'dooh';
                    $hoarding->base_price_for_enquiry = (float) (optional($hoarding->doohScreen)->price_per_slot ?? 0);
                } else {
                    $hoarding->price_type = 'ooh';
                    $hoarding->base_price_for_enquiry = (float) ($hoarding->monthly_price ?? 0);
                    $hoarding->monthly_price_display = $hoarding->monthly_price;
                    $hoarding->base_monthly_price_display = $hoarding->base_monthly_price;
                }
                $hoarding->grace_period_days = (int) ($hoarding->grace_period_days ?? 0);

                // Get today's availability status
                $calendar = $availabilityService->getAvailabilityCalendar($hoarding->id, $today, $today);
                $todayStatus = $calendar['calendar'][0]['status'] ?? 'unknown';
                $hoarding->today_availability_status = $todayStatus;

                // If not available today, get next available date
                if ($todayStatus !== 'available') {
                    $next = $availabilityService->getNextAvailableDates($hoarding->id, 1, $today);
                    $hoarding->next_available_date = $next['dates'][0]['date'] ?? null;
                } else {
                    $hoarding->next_available_date = null;
                }

                return $hoarding;
            })
        );


        // If no hoardings, use dummy data
        // if ($bestHoardings->isEmpty()) {
        //     $bestHoardings = collect($this->getDummyHoardings());
        // }

        // Get top DOOH screens
        $topDOOHs = DOOHScreen::whereHas('hoarding', function ($q) {
            $q->where('status', 'approved')
                ->where('hoarding_type', 'dooh');
        })
            ->with(['hoarding.vendor'])
            ->latest()
            ->get();

        // If no DOOH screens, use dummy data
        if ($topDOOHs->isEmpty()) {
            $topDOOHs = collect($this->getDummyDOOH());
        }

        // Get top states (extract from addresses)
        $topStates = $this->getTopStates();

        // Check if user has location stored
        $userLocation = session('user_location');
        // ---------------- PAGINATION ADD (WITHOUT REMOVING ANYTHING) ----------------
        // Remove manual paginator wrapping; use Eloquent paginator directly


        $cartIds = app(CartService::class)
            ->getCartHoardingIds();
        $testimonialRole = 'customer';

        if (auth()->check() && auth()->user()->active_role === 'vendor') {
            $testimonialRole = 'vendor';
        }

        // Static testimonials with Indian people images - completely without database
        $staticTestimonials = [
            // Customer testimonials
            (object) [
                'id' => 1,
                'user' => (object) [
                    'name' => 'Rajesh Kumar',
                    'avatar' => 'https://i.pravatar.cc/150?img=1'
                ],
                'role' => 'customer',
                'message' => 'Outstanding platform for outdoor advertising! Found the perfect hoarding location for my business within minutes. Highly recommended!',
                'rating' => 5,
                'status' => 'approved',
                'show_on_homepage' => true,
            ],
            (object) [
                'id' => 2,
                'user' => (object) [
                    'name' => 'Priya Singh',
                    'avatar' => ''
                ],
                'role' => 'customer',
                'message' => 'Best investment I made for my campaign. The process is transparent and customer support is exceptional.',
                'rating' => 5,
                'status' => 'approved',
                'show_on_homepage' => true,
            ],
            (object) [
                'id' => 3,
                'user' => (object) [
                    'name' => 'Amit Patel',
                    'avatar' => 'https://i.pravatar.cc/150?img=3'
                ],
                'role' => 'customer',
                'message' => 'Easy booking, great rates, and excellent visibility. My ROI exceeded expectations.',
                'rating' => 4,
                'status' => 'approved',
                'show_on_homepage' => true,
            ],
            (object) [
                'id' => 4,
                'user' => (object) [
                    'name' => 'Neha Sharma',
                    'avatar' => ''
                ],
                'role' => 'customer',
                'message' => 'Professional team and seamless experience. Would definitely use this platform again for future campaigns.',
                'rating' => 5,
                'status' => 'approved',
                'show_on_homepage' => true,
            ],
            // Vendor testimonials
            (object) [
                'id' => 5,
                'user' => (object) [
                    'name' => 'Vikram Advertising',
                    'avatar' => 'https://i.pravatar.cc/150?img=12'
                ],
                'role' => 'vendor',
                'message' => 'Great platform to monetize our hoardings. The payment system is reliable and transparent.',
                'rating' => 5,
                'status' => 'approved',
                'show_on_homepage' => true,
            ],
            (object) [
                'id' => 6,
                'user' => (object) [
                    'name' => 'Anjali Gupta',
                    'avatar' => ''
                ],
                'role' => 'vendor',
                'message' => 'Increased our revenue significantly. The dashboard is user-friendly and reporting is accurate.',
                'rating' => 4,
                'status' => 'approved',
                'show_on_homepage' => true,
            ],
            (object) [
                'id' => 7,
                'user' => (object) [
                    'name' => 'Deepak Singh',
                    'avatar' => 'https://i.pravatar.cc/150?img=5'
                ],
                'role' => 'vendor',
                'message' => 'Professional platform with excellent support team. Booking management has never been easier.',
                'rating' => 5,
                'status' => 'approved',
                'show_on_homepage' => true,
            ],
        ];

        // Filter static testimonials based on role and conditions
        $testimonials = collect($staticTestimonials)->filter(function ($item) use ($testimonialRole) {
            return $item->role === $testimonialRole &&
                $item->status === 'approved' &&
                $item->show_on_homepage === true;
        })->values();

        return view('home.index', compact(
            'stats',
            'bestHoardings',
            // 'topDOOHs',
            'topStates',
            'userLocation',
            'cartIds',
            'testimonials',
            'testimonialRole'
        ));
    }

    /**
     * Get top states from hoarding addresses
     */
    private function getTopStates(int $limit = 8): array
    {
        $priorityStates = [
            'UTTAR PRADESH',
            'DELHI',
            'MAHARASHTRA',
            'GOA',
            'HIMACHAL PRADESH',
            'JAMMU & KASHMIR',
            'GUJARAT',
            'RAJASTHAN',
        ];

        // ✅ LOCAL IMAGE MAPPING
        $images = [
            'UTTAR PRADESH'   => asset('images/states/up.jpeg'),
            'DELHI'           => asset('images/states/delhi.jpg'),
            'MAHARASHTRA'     => asset('images/states/mumbai.jpg'),
            'GOA'             => asset('images/states/goa.jpg'),
            'HIMACHAL PRADESH'=> asset('images/states/himanchal.jpeg'),
            'JAMMU & KASHMIR' => asset('images/states/jammu.jpeg'),
            'GUJARAT'         => asset('images/states/gujrat.jpg'),
            'RAJASTHAN'       => asset('images/states/Rajasthan.jpg'),
        ];

        $defaultImage = asset('images/states/default.jpg');

        // ✅ STATE COUNT
        $stateCounts = Hoarding::select(
            DB::raw('UPPER(TRIM(state)) as name'),
            DB::raw('COUNT(*) as count')
        )
        ->where('status', 'active')
        ->whereNotNull('state')
        ->groupBy(DB::raw('UPPER(TRIM(state))'))
        ->pluck('count', 'name');

        // ✅ CITY COUNT (important 🔥)
        $cityCounts = Hoarding::select(
            DB::raw('UPPER(TRIM(city)) as name'),
            DB::raw('COUNT(*) as count')
        )
        ->where('status', 'active')
        ->whereNotNull('city')
        ->groupBy(DB::raw('UPPER(TRIM(city))'))
        ->pluck('count', 'name');

        $result = [];

        foreach ($priorityStates as $stateName) {

            $stateCount = (int) ($stateCounts[$stateName] ?? 0);

            // 🔥 city match logic (Delhi, Mumbai etc.)
            $cityMatch = $cityCounts->keys()->first(function ($city) use ($stateName) {
                return str_contains($city, $stateName) || str_contains($stateName, $city);
            });

            // ❌ skip if neither state nor city exists
            if ($stateCount === 0 && !$cityMatch) {
                continue;
            }

            $result[] = [
                'name'  => $stateName,
                'count' => $stateCount,
                'image' => $images[$stateName] ?? $defaultImage,
            ];
        }

        return array_slice($result, 0, $limit);
    }



    /**
     * Get dummy hoarding data for display when no real data exists
     */
    private function getDummyHoardings(): array
    {
        $dummyData = [];
        $locations = [
            'Udaipur | Hiramagri Chouraha',
            'Mumbai | Andheri West',
            'Bangalore | MG Road',
            'Delhi | Connaught Place',
            'Chennai | T Nagar',
            'Hyderabad | Hi-Tech City',
            'Pune | Koregaon Park',
            'Jaipur | MI Road'
        ];

        $images = [
            'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&h=300&fit=crop',
            'https://images.unsplash.com/photo-1562577309-4932fdd64cd1?w=400&h=300&fit=crop',
            'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=400&h=300&fit=crop',
            'https://images.unsplash.com/photo-1573152143286-0c422b4d2175?w=400&h=300&fit=crop',
            'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=400&h=300&fit=crop',
            'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=400&h=300&fit=crop',
            'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&h=300&fit=crop',
            'https://images.unsplash.com/photo-1562577309-4932fdd64cd1?w=400&h=300&fit=crop'
        ];

        for ($i = 0; $i < 8; $i++) {
            $dummyData[] = (object) [
                'id' => $i + 1,
                'title' => $locations[$i],
                'address' => $locations[$i],
                'type' => 'billboard',
                'status' => 'active',
                'monthly_price' => 10999 + ($i * 1000),
                'weekly_price' => 3500 + ($i * 300),
                'enable_weekly_booking' => true,
                'image' => $images[$i],
                'hasMedia' => function () {
                    return true;
                },
                'getFirstMediaUrl' => function () use ($images, $i) {
                    return $images[$i];
                }
            ];
        }

        return $dummyData;
    }

    /**
     * Get dummy DOOH data for display when no real data exists
     */
    private function getDummyDOOH(): array
    {
        $dummyData = [];
        $locations = [
            ['city' => 'Mumbai', 'state' => 'Maharashtra'],
            ['city' => 'Delhi', 'state' => 'Delhi'],
            ['city' => 'Bangalore', 'state' => 'Karnataka'],
            ['city' => 'Hyderabad', 'state' => 'Telangana'],
            ['city' => 'Chennai', 'state' => 'Tamil Nadu'],
            ['city' => 'Pune', 'state' => 'Maharashtra'],
            ['city' => 'Kolkata', 'state' => 'West Bengal'],
            ['city' => 'Jaipur', 'state' => 'Rajasthan']
        ];

        for ($i = 0; $i < 8; $i++) {
            $dummyData[] = (object) [
                'id' => $i + 1,
                'name' => 'Digital Screen - ' . $locations[$i]['city'],
                'city' => $locations[$i]['city'],
                'state' => $locations[$i]['state'],
                'screen_type' => 'led',
                'status' => 'active',
                'resolution' => '1920x1080',
                'total_slots_per_day' => 48,
                'price_per_slot' => 500 + ($i * 100),
            ];
        }

        return $dummyData;
    }
}
