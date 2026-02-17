<?php

namespace Modules\Enquiries\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\OTPService;
use Modules\Enquiries\Models\DirectEnquiry;
use Modules\Enquiries\Mail\AdminDirectEnquiryMail;
use Modules\Enquiries\Mail\UserDirectEnquiryConfirmation;
use Modules\Enquiries\Mail\VendorEnquiryNotification;
use App\Notifications\AdminDirectEnquiryNotification;
use App\Notifications\VendorEnquiryNotification as VendorEnquiryNotif;
use App\Models\User;
use App\Models\Hoarding; // Adjust namespace based on your structure
use Carbon\Carbon;

class DirectEnquiryController extends Controller
{
    /**
     * Display enquiries list for admin
     */
    public function index(Request $request)
    {
        $query = DirectEnquiry::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('location_city', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Hoarding type filter
        if ($request->filled('hoarding_type')) {
            $query->where('hoarding_type', 'like', "%{$request->hoarding_type}%");
        }

        $enquiries = $query
            ->with('assignedVendors')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.enquiries.directenquiry-index', compact('enquiries'));
    }

    /**
     * Generate captcha numbers
     */
    public function regenerateCaptcha()
    {
        $num1 = rand(1, 9);
        $num2 = rand(1, 9);
        session(['captcha_answer' => $num1 + $num2]);
        
        return response()->json([
            'num1' => $num1,
            'num2' => $num2
        ]);
    }

    /**
     * Send OTP to phone number
     */
    public function sendOtp(Request $request, OTPService $otpService)
    {
        $request->validate([
            'identifier' => 'required|string'
        ]);

        $identifier = $request->identifier;

        // Validate phone format (Indian mobile numbers: 10 digits starting with 6-9)
        if (!preg_match('/^[6-9][0-9]{9}$/', $identifier)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid phone number format. Must be 10 digits starting with 6-9.'
            ], 422);
        }

        try {
            // Create or get guest user
            $user = Auth::user() ?? User::firstOrCreate(
                ['phone' => $identifier],
                [
                    'name' => 'Guest User',
                    'status' => 'pending_verification',
                    'active_role' => 'customer'
                ]
            );

            // Rate limiting check (60 seconds between OTPs)
            $recentOtp = DB::table('user_otps')
                ->where('identifier', $identifier)
                ->where('purpose', 'direct_enquiry')
                ->latest('created_at')
                ->first();

            if ($recentOtp) {
                $createdAt = Carbon::parse($recentOtp->created_at);
                $waitTime = config('app.otp_wait_time', 60); // Default 60 seconds

                if (now()->diffInSeconds($createdAt) < $waitTime) {
                    $remaining = $waitTime - now()->diffInSeconds($createdAt);
                    return response()->json([
                        'success' => false,
                        'message' => "Please wait {$remaining} seconds before requesting another OTP",
                        'retry_after' => $remaining
                    ], 429);
                }
            }

            // Generate and send OTP
            $otpService->generate($user->id, $identifier, 'direct_enquiry');

            Log::info('OTP sent for direct enquiry', [
                'phone' => $identifier,
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully to +91-' . substr($identifier, 0, 2) . 'XXXXXX' . substr($identifier, -2)
            ]);

        } catch (\Exception $e) {
            Log::error('OTP Send Failed', [
                'identifier' => $identifier,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again later.'
            ], 500);
        }
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request, OTPService $otpService)
    {
        $request->validate([
            'identifier' => 'required|string',
            'otp' => 'required|digits:4'
        ]);

        try {
            $user = Auth::user() ?? User::where('phone', $request->identifier)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $verified = $otpService->verify(
                $user->id, 
                $request->identifier, 
                $request->otp, 
                'direct_enquiry'
            );

            if (!$verified) {
                Log::warning('Invalid OTP attempt', [
                    'phone' => $request->identifier,
                    'ip' => $request->ip()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP. Please request a new one.'
                ], 422);
            }

            Log::info('OTP verified successfully', [
                'phone' => $request->identifier,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Phone number verified successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('OTP Verification Failed', [
                'identifier' => $request->identifier,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Verification failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Store new enquiry
     */
    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|digits:10|regex:/^[6-9][0-9]{9}$/',
            'hoarding_type' => 'required|array|min:1',
            'hoarding_type.*' => 'in:DOOH,OOH',
            'location_city' => 'required|string|max:255',
            'preferred_locations' => 'nullable|array',
            'preferred_locations.*' => 'nullable|string|max:255',
            'remarks' => 'required|string|min:10|max:2000',
            'preferred_modes' => 'nullable|array',
            'preferred_modes.*' => 'in:Call,WhatsApp,Email',
            'captcha' => 'required|numeric',
            'phone_verified' => 'required|in:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify captcha
        if ((int) $request->captcha !== (int) session('captcha_answer')) {
            return response()->json([
                'success' => false,
                'errors' => ['captcha' => ['Incorrect captcha answer']]
            ], 422);
        }

        // Verify OTP was actually verified in last 10 minutes
        $phoneVerified = DB::table('user_otps')
            ->where('identifier', $request->phone)
            ->where('purpose', 'direct_enquiry')
            ->whereNotNull('verified_at')
            ->where('created_at', '>', now()->subMinutes(10))
            ->exists();

        if (!$phoneVerified) {
            return response()->json([
                'success' => false,
                'errors' => ['phone' => ['Phone number verification expired. Please verify again.']]
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Prepare data
            $data = $validator->validated();
            
            // Normalize city name (handle spelling mistakes with fuzzy matching)
            $normalizedCity = $this->normalizeCityName($data['location_city']);
            
            // Filter and clean preferred locations
            $preferredLocations = !empty($data['preferred_locations']) 
                ? array_values(array_filter(
                    array_map('trim', $data['preferred_locations']), 
                    fn($loc) => !empty($loc)
                ))
                : ['To be discussed'];

            // Normalize locality names to handle spelling mistakes
            $normalizedLocalities = array_map(
                fn($loc) => $this->normalizeLocalityName($loc, $normalizedCity),
                $preferredLocations
            );

            // Create enquiry
            $enquiry = DirectEnquiry::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'hoarding_type' => implode(',', $data['hoarding_type']),
                'location_city' => $normalizedCity,
                'preferred_locations' => $normalizedLocalities,
                'remarks' => $data['remarks'],
                'preferred_modes' => $data['preferred_modes'] ?? ['Call', 'Email'],
                'is_phone_verified' => true,
                'status' => 'new',
                'source' => 'website'
            ]);

            // Find relevant vendors based on their hoardings in the specified location
            $vendors = $this->findRelevantVendors(
                $normalizedCity, 
                $normalizedLocalities,
                $data['hoarding_type']
            );
            
            if ($vendors->isNotEmpty()) {
                // Attach vendors to enquiry
                $enquiry->assignedVendors()->attach($vendors->pluck('id'));
                
                // Send notifications to vendors (queued for better performance)
                foreach ($vendors as $vendor) {
                    Mail::to($vendor->email)->queue(
                        new VendorEnquiryNotification($enquiry, $vendor)
                    );
                    
                    // In-app notification
                    $vendor->notify(new VendorEnquiryNotif($enquiry));
                }
                
                Log::info('Enquiry assigned to vendors', [
                    'enquiry_id' => $enquiry->id,
                    'vendor_count' => $vendors->count(),
                    'vendor_ids' => $vendors->pluck('id')->toArray(),
                    'city' => $normalizedCity,
                    'localities' => $normalizedLocalities
                ]);
            } else {
                Log::warning('No vendors found for enquiry', [
                    'enquiry_id' => $enquiry->id,
                    'city' => $normalizedCity,
                    'localities' => $normalizedLocalities
                ]);
            }

            // Send confirmation to user
            Mail::to($enquiry->email)->queue(
                new UserDirectEnquiryConfirmation($enquiry)
            );

            // Notify admins
            $admins = User::whereIn('active_role', ['admin', 'superadmin'])
                ->where('status', 'active')
                ->get();

            if ($admins->isNotEmpty()) {
                foreach ($admins as $admin) {
                    Mail::to($admin->email)->queue(
                        new AdminDirectEnquiryMail($enquiry, $vendors)
                    );
                    $admin->notify(new AdminDirectEnquiryNotification($enquiry));
                }
            }

            DB::commit();

            // Clear captcha and OTP records
            session()->forget('captcha_answer');
            DB::table('user_otps')
                ->where('identifier', $request->phone)
                ->where('purpose', 'direct_enquiry')
                ->delete();

            Log::info('Direct enquiry created successfully', [
                'enquiry_id' => $enquiry->id,
                'city' => $normalizedCity,
                'vendors_notified' => $vendors->count(),
                'customer_phone' => $enquiry->phone
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Enquiry submitted successfully! You will receive quotes within 24-48 hours.',
                'enquiry_id' => $enquiry->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Enquiry submission failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['captcha', 'phone'])
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit enquiry. Please try again or contact support.'
            ], 500);
        }
    }

    /**
     * Normalize city name to handle spelling mistakes
     * Uses fuzzy matching with Levenshtein distance
     */
    private function normalizeCityName(string $city): string
    {
        $city = trim(strtolower($city));
        
        // Common city name mappings with variations
        $cityMappings = [
            // Uttar Pradesh
            'lucknow' => ['lucknow', 'lko', 'lakhnau', 'lakhnaow', 'lucknaw', 'lukhnow'],
            'kanpur' => ['kanpur', 'cawnpore', 'kanpoor', 'kanpore'],
            'varanasi' => ['varanasi', 'banaras', 'benares', 'kashi', 'varnasi'],
            'agra' => ['agra', 'agrah', 'aagra'],
            'prayagraj' => ['prayagraj', 'allahabad', 'ilahabad', 'prayag'],
            'ballia' => ['ballia', 'balliya', 'balia', 'balya'],
            
            // Punjab
            'mohali' => ['mohali', 'sahibzada ajit singh nagar', 'sas nagar', 'mohalli'],
            'chandigarh' => ['chandigarh', 'chandigrah', 'chandigar'],
            
            // Major Cities
            'mumbai' => ['mumbai', 'bombay', 'mumbay', 'mumby', 'mombai'],
            'delhi' => ['delhi', 'dilli', 'dehli', 'new delhi', 'newdelhi'],
            'bangalore' => ['bangalore', 'bengaluru', 'bangaluru', 'banglore', 'bengaloor'],
            'kolkata' => ['kolkata', 'calcutta', 'kolkatta', 'kalkatta', 'kolkota'],
            'hyderabad' => ['hyderabad', 'hydrabad', 'haidarabad', 'hyderabadh'],
            'chennai' => ['chennai', 'madras', 'chenai', 'chenna'],
            'pune' => ['pune', 'poona', 'puna'],
            'ahmedabad' => ['ahmedabad', 'amdavad', 'ahmadabad', 'ahmdabad'],
            'jaipur' => ['jaipur', 'jaypur', 'jeypore', 'jeypur'],
            'surat' => ['surat', 'surath', 'suratt'],
            'indore' => ['indore', 'indor', 'indaur'],
            'bhopal' => ['bhopal', 'bhopl', 'bhopaal'],
        ];

        // First try exact match
        foreach ($cityMappings as $standard => $variations) {
            if (in_array($city, $variations)) {
                return ucwords($standard);
            }
        }

        // Try fuzzy matching with Levenshtein distance (max 2 character differences)
        foreach ($cityMappings as $standard => $variations) {
            foreach ($variations as $variation) {
                $distance = levenshtein($city, $variation);
                if ($distance <= 2) {
                    return ucwords($standard);
                }
            }
        }

        // If no match found, return capitalized original
        return ucwords($city);
    }

    /**
     * Normalize locality name to handle spelling mistakes
     */
    private function normalizeLocalityName(string $locality, string $city): string
    {
        if ($locality === 'To be discussed') {
            return $locality;
        }

        $locality = trim(strtolower($locality));
        $city = strtolower($city);
        
        // City-specific locality mappings
        $localityMappings = [
            'lucknow' => [
                'hazratganj' => ['hazratganj', 'hazrat ganj', 'hazratganj', 'ganj'],
                'gomti nagar' => ['gomti nagar', 'gomtinagar', 'gomti', 'gomati nagar'],
                'indira nagar' => ['indira nagar', 'indiranagar', 'indra nagar'],
                'aminabad' => ['aminabad', 'amina bad', 'aminaabad'],
                'alambagh' => ['alambagh', 'alam bagh', 'alambag'],
            ],
            'ballia' => [
                'rasra' => ['rasra', 'raasra', 'rasara'],
                'kharuwan' => ['kharuwan', 'kharwan', 'kharuwan'],
            ],
            'mohali' => [
                'sector 70' => ['sector 70', 'sec 70', 'sector-70'],
                'sector 71' => ['sector 71', 'sec 71', 'sector-71'],
            ],
            // Add more city-specific localities as needed
        ];

        // Check if we have mappings for this city
        if (isset($localityMappings[$city])) {
            foreach ($localityMappings[$city] as $standard => $variations) {
                if (in_array($locality, $variations)) {
                    return ucwords($standard);
                }
                
                // Fuzzy match
                foreach ($variations as $variation) {
                    if (levenshtein($locality, $variation) <= 2) {
                        return ucwords($standard);
                    }
                }
            }
        }

        return ucwords($locality);
    }

    /**
     * Find relevant vendors based on their hoardings in specified locations
     * Matches: city, locality, and hoarding type
     */
    private function findRelevantVendors(string $city, array $localities, array $hoardingTypes)
    {
        // Convert hoarding types to lowercase for matching
        $hoardingTypes = array_map('strtolower', $hoardingTypes);
        
        // Build query to find hoardings matching the criteria
        $hoardingQuery = DB::table('hoardings')
            ->select('vendor_id')
            ->where('status', 'active')
            ->whereNotNull('vendor_id');
        
        // Match city (with fuzzy tolerance)
        $hoardingQuery->where(function ($q) use ($city) {
            $q->where('city', 'like', "%{$city}%")
              ->orWhere('city', 'like', $this->getFuzzyPattern($city));
        });
        
        // Match locality if specified (excluding "To be discussed")
        if (!empty($localities) && $localities[0] !== 'To be discussed') {
            $hoardingQuery->where(function ($q) use ($localities) {
                foreach ($localities as $locality) {
                    $q->orWhere('locality', 'like', "%{$locality}%")
                      ->orWhere('address', 'like', "%{$locality}%")
                      ->orWhere('landmark', 'like', "%{$locality}%");
                }
            });
        }
        
        // Match hoarding type (OOH or DOOH)
        $hoardingQuery->where(function ($q) use ($hoardingTypes) {
            foreach ($hoardingTypes as $type) {
                $q->orWhere('hoarding_type', 'like', "%{$type}%");
            }
        });
        
        // Get unique vendor IDs
        $vendorIds = $hoardingQuery
            ->distinct()
            ->pluck('vendor_id')
            ->filter()
            ->unique()
            ->toArray();
        
        if (empty($vendorIds)) {
            Log::info('No hoardings found matching criteria', [
                'city' => $city,
                'localities' => $localities,
                'hoarding_types' => $hoardingTypes
            ]);
            
            // Fallback: Find vendors who have ANY hoarding in the city
            $vendorIds = DB::table('hoardings')
                ->select('vendor_id')
                ->where('status', 'active')
                ->where('city', 'like', "%{$city}%")
                ->whereNotNull('vendor_id')
                ->distinct()
                ->pluck('vendor_id')
                ->toArray();
        }
        
        // Get vendor user details
        $vendors = User::whereIn('id', $vendorIds)
            ->where('active_role', 'vendor')
            ->where('status', 'active')
            ->whereNotNull('email')
            ->with(['hoardings' => function ($query) use ($city) {
                $query->where('city', 'like', "%{$city}%")
                      ->where('status', 'active')
                      ->select('id', 'vendor_id', 'title', 'city', 'locality', 'hoarding_type');
            }])
            ->get();
        
        Log::info('Vendors matched for enquiry', [
            'city' => $city,
            'localities' => $localities,
            'total_hoardings_found' => count($vendorIds),
            'vendors_found' => $vendors->count(),
            'vendor_emails' => $vendors->pluck('email')->toArray()
        ]);
        
        return $vendors;
    }

    /**
     * Generate fuzzy pattern for SQL LIKE query
     */
    private function getFuzzyPattern(string $text): string
    {
        // Add % between each character for fuzzy matching
        // e.g., "lucknow" becomes "%l%u%c%k%n%o%w%"
        $pattern = '%' . implode('%', str_split(strtolower($text))) . '%';
        return $pattern;
    }
}