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



class HomeController extends Controller
{
    /**
     * Display the public homepage.
     *
     * @return View
     */
    public function index(CartService $cartService): View
    {
        // Get platform statistics
        $stats = [
            'total_hoardings' => Hoarding::where('status', 'active')->count(),
            'total_vendors' => User::whereHas('roles', function($q) {
                $q->where('name', 'vendor');
            })->count(),
            'total_bookings' => 0, // Will be calculated from bookings module
        ];

        // Try to get booking count if Booking model exists
        try {
            if (class_exists('\Modules\Bookings\Models\Booking')) {
                $stats['total_bookings'] = \Modules\Bookings\Models\Booking::whereIn('status', ['active', 'completed'])->count();
            }
        } catch (\Exception $e) {
            // Ignore if table doesn't exist yet
        }

        // Get recently added hoardings
        $bestHoardings = Hoarding::where('status', 'active')
            ->with([
                'vendor',
                'hoardingMedia',     
                'doohScreen.media'
            ])
            ->latest('created_at')
            ->get();

        $bestHoardings = $bestHoardings->map(function ($hoarding) {

            if ($hoarding->hoarding_type === 'dooh') {

                $hoarding->price_type = 'dooh';

                $hoarding->base_price_for_enquiry =
                    (float) (optional($hoarding->doohScreen)->price_per_slot ?? 0);

            } else {

                $hoarding->price_type = 'ooh';

                $hoarding->base_price_for_enquiry =
                    (float) ($hoarding->monthly_price ?? 0);

                $hoarding->monthly_price_display = $hoarding->monthly_price;
                $hoarding->base_monthly_price_display = $hoarding->base_monthly_price;
            }
            $hoarding->grace_period_days = (int) ($hoarding->grace_period_days ?? 0);
            
            return $hoarding;
        });


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

        // Get top cities (extract from addresses)
        $topCities = $this->getTopCities();

        // Check if user has location stored
        $userLocation = session('user_location');
        // ---------------- PAGINATION ADD (WITHOUT REMOVING ANYTHING) ----------------
        $page = request()->get('page', 1);
        $perPage = 8;

        $bestHoardings = $bestHoardings instanceof Collection
            ? $bestHoardings
            : collect($bestHoardings);

        $bestHoardings = new LengthAwarePaginator(
            $bestHoardings->forPage($page, $perPage),
            $bestHoardings->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
        
        
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
            'topDOOHs',
            'topCities',
            'userLocation',
            'cartIds',
            'testimonials',
            'testimonialRole'
        ));
    }

    /**
     * Get top cities from hoarding addresses
     */
    // private function getTopCities(): array
    // {
    //     // For now, return static cities with images
    //     // In production, this would be extracted from actual data
    //     return [
    //         [
    //             'name' => 'JAIPUR',
    //             'count' => Hoarding::where('status', 'active')->where('address', 'like', '%Jaipur%')->count(),
    //             'image' => 'https://images.unsplash.com/photo-1477587458883-47145ed94245?w=400&h=300&fit=crop'
    //         ],
    //         [
    //             'name' => 'BANGALORE',
    //             'count' => Hoarding::where('status', 'active')->where('address', 'like', '%Bangalore%')->count(),
    //             'image' => 'https://images.unsplash.com/photo-1596176530529-78163a4f7af2?w=400&h=300&fit=crop'
    //         ],
    //         [
    //             'name' => 'CHENNAI',
    //             'count' => Hoarding::where('status', 'active')->where('address', 'like', '%Chennai%')->count(),
    //             'image' => 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?w=400&h=300&fit=crop'
    //         ],
    //         [
    //             'name' => 'HYDERABAD',
    //             'count' => Hoarding::where('status', 'active')->where('address', 'like', '%Hyderabad%')->count(),
    //             'image' => 'https://images.unsplash.com/photo-1567157577867-05ccb1388e66?w=400&h=300&fit=crop'
    //         ],
    //         [
    //             'name' => 'MUMBAI',
    //             'count' => Hoarding::where('status', 'active')->where('address', 'like', '%Mumbai%')->count(),
    //             'image' => 'https://images.unsplash.com/photo-1566552881560-0be862a7c445?w=400&h=300&fit=crop'
    //         ],
    //         [
    //             'name' => 'DELHI',
    //             'count' => Hoarding::where('status', 'active')->where('address', 'like', '%Delhi%')->count(),
    //             'image' => 'https://images.unsplash.com/photo-1587474260584-136574528ed5?w=400&h=300&fit=crop'
    //         ],
    //         [
    //             'name' => 'KOLKATA',
    //             'count' => Hoarding::where('status', 'active')->where('address', 'like', '%Kolkata%')->count(),
    //             'image' => 'https://images.unsplash.com/photo-1558431382-27e303142255?w=400&h=300&fit=crop'
    //         ],
    //         [
    //             'name' => 'PUNE',
    //             'count' => Hoarding::where('status', 'active')->where('address', 'like', '%Pune%')->count(),
    //             'image' => 'https://images.unsplash.com/photo-1595658658481-d53d3f999875?w=400&h=300&fit=crop'
    //         ],
    //     ];
    // }

    private function getTopCities(int $limit = 8): array
    {
        // Step 1: Get city-wise hoarding count
        $cities = Hoarding::select(
            DB::raw('UPPER(city) as name'),
            DB::raw('COUNT(*) as count')
        )
            ->where('status', 'active')
            ->whereNotNull('city')
            ->groupBy('city')
            ->orderByDesc('count')
            ->get();

        // Step 2: Move Lucknow to top if present
        $cities = $cities->sortByDesc(function ($city) {
            return $city->name === 'LUCKNOW'
                ? PHP_INT_MAX
                : $city->count;
        });

        // Step 3: Take only top N cities
        $cities = $cities->take($limit);

        // Step 4: Attach images
        return $cities->map(function ($city) {
            return [
                'name'  => $city->name,
                'count' => $city->count,
                'image' => $this->getCityImage($city->name),
            ];
        })->values()->toArray();
    }

    private function getCityImage(string $city): string
    {
        $images = [
            'LUCKNOW'    => 'https://images.unsplash.com/photo-1603262110263-fb0112e7cc33?w=400&h=300&fit=crop',
            'DELHI'      => 'https://images.unsplash.com/photo-1587474260584-136574528ed5?w=400&h=300&fit=crop',
            'MUMBAI'     => 'https://images.unsplash.com/photo-1566552881560-0be862a7c445?w=400&h=300&fit=crop',
            'BANGALORE'  => 'https://images.unsplash.com/photo-1596176530529-78163a4f7af2?w=400&h=300&fit=crop',
            'CHENNAI'    => 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?w=400&h=300&fit=crop',
            'HYDERABAD'  => 'https://images.unsplash.com/photo-1567157577867-05ccb1388e66?w=400&h=300&fit=crop',
            'PUNE'       => 'https://images.unsplash.com/photo-1595658658481-d53d3f999875?w=400&h=300&fit=crop',
            'KOLKATA'    => 'https://images.unsplash.com/photo-1558431382-27e303142255?w=400&h=300&fit=crop',
            'JAIPUR'     => 'https://images.unsplash.com/photo-1477587458883-47145ed94245?w=400&h=300&fit=crop',
        ];

        // Common image for all other cities
        $defaultImage = 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?w=400&h=300&fit=crop';

        return $images[$city] ?? $defaultImage;
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
                'hasMedia' => function() { return true; },
                'getFirstMediaUrl' => function() use ($images, $i) { return $images[$i]; }
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
