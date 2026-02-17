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
use App\Services\GuestOtpService;
use Modules\Enquiries\Models\DirectEnquiry;
use Modules\Enquiries\Mail\AdminDirectEnquiryMail;
use Modules\Enquiries\Mail\UserDirectEnquiryConfirmation;
use Modules\Enquiries\Mail\VendorDirectEnquiryMail;
use App\Notifications\AdminDirectEnquiryNotification;
use Modules\Enquiries\Notifications\VendorDirectEnquiryNotification;
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

        public function sendOtp(Request $request, GuestOtpService $otpService)
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
            // Rate limiting check (60 seconds between OTPs)
            $recentOtp = DB::table('guest_user_otps')
                ->where('identifier', $identifier)
                ->where('purpose', 'direct_enquiry')
                ->latest('created_at')
                ->first();

            if ($recentOtp) {
                $createdAt = Carbon::parse($recentOtp->created_at);
                $waitTime = config('app.otp_wait_time', 1); // Default 60 seconds

                if (now()->diffInSeconds($createdAt) < $waitTime) {
                    $remaining = $waitTime - now()->diffInSeconds($createdAt);
                    return response()->json([
                        'success' => false,
                        'message' => "Please wait {$remaining} seconds before requesting another OTP",
                        'retry_after' => $remaining
                    ], 429);
                }
            }

            // Generate and send OTP (no user creation needed!)
            $otpService->generate($identifier, 'direct_enquiry');

            Log::info('OTP sent for direct enquiry', [
                'phone_masked' => substr($identifier, 0, 2) . 'XXXXXX' . substr($identifier, -2),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully to +91-' . substr($identifier, 0, 2) . 'XXXXXX' . substr($identifier, -2)
            ]);

        } catch (\Exception $e) {
            Log::error('OTP Send Failed', [
                'phone_masked' => substr($identifier, 0, 2) . 'XXXXXX' . substr($identifier, -2),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again later.'
            ], 500);
        }
    }

    /**
     * Verify OTP
     * NO USER LOOKUP NEEDED - works directly with phone number
     */
    public function verifyOtp(Request $request, GuestOtpService $otpService)
    {
        $request->validate([
            'identifier' => 'required|string',
            'otp' => 'required|digits:4'
        ]);

        try {
            // Verify OTP directly without user lookup
            $verified = $otpService->verify(
                $request->identifier, 
                $request->otp, 
                'direct_enquiry'
            );

            if (!$verified) {
                Log::warning('Invalid OTP attempt', [
                    'phone_masked' => substr($request->identifier, 0, 2) . 'XXXXXX' . substr($request->identifier, -2),
                    'ip' => $request->ip()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP. Please request a new one.'
                ], 422);
            }

            Log::info('OTP verified successfully', [
                'phone_masked' => substr($request->identifier, 0, 2) . 'XXXXXX' . substr($request->identifier, -2)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Phone number verified successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('OTP Verification Failed', [
                'phone_masked' => substr($request->identifier, 0, 2) . 'XXXXXX' . substr($request->identifier, -2),
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
        $phoneVerified = DB::table('guest_user_otps')
            ->where('identifier', $request->phone)
            ->where('purpose', 'direct_enquiry')
            ->whereNotNull('verified_at')
            ->where('created_at', '>', now()->subMinutes(20))
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
            
            // Normalize city name (handle spelling mistakes)
            $normalizedCity = $this->normalizeCityName($data['location_city']);
            
            // Filter and clean preferred locations
            $preferredLocations = !empty($data['preferred_locations']) 
                ? array_values(array_filter(
                    array_map('trim', $data['preferred_locations']), 
                    fn($loc) => !empty($loc)
                ))
                : ['To be discussed'];

            // Normalize locality names
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

            // Find relevant vendors based on hoardings
            $vendors = $this->findRelevantVendors(
                $normalizedCity, 
                $normalizedLocalities,
                $data['hoarding_type']
            );
            
            if ($vendors->isNotEmpty()) {
                // Attach vendors to enquiry
                $enquiry->assignedVendors()->attach($vendors->pluck('id'));
                
                // Send notifications to vendors (queued)
                foreach ($vendors as $vendor) {
                    Mail::to($vendor->email)->queue(
                        new VendorDirectEnquiryMail($enquiry, $vendor)
                    );
                    $vendor->notify(new VendorDirectEnquiryNotification($enquiry, $vendor));
                }
                
                Log::info('Enquiry assigned to vendors', [
                    'enquiry_id' => $enquiry->id,
                    'vendor_count' => $vendors->count(),
                    'city' => $normalizedCity
                ]);
            }

            // Send confirmation to customer
            Mail::to($enquiry->email)->queue(
                new UserDirectEnquiryConfirmation($enquiry)
            );

            // Notify admins
            $admins = User::whereIn('active_role', ['admin', 'superadmin'])
                ->where('status', 'active')
                ->get();

            foreach ($admins as $admin) {
                Mail::to($admin->email)->queue(
                    new AdminDirectEnquiryMail($enquiry, $vendors)
                );
                $admin->notify(new AdminDirectEnquiryNotification($enquiry));
            }

            DB::commit();

            // Clean up - delete OTP records after successful submission
            DB::table('guest_user_otps')
                ->where('identifier', $request->phone)
                ->where('purpose', 'direct_enquiry')
                ->delete();

            // Clear captcha
            session()->forget('captcha_answer');

            Log::info('Direct enquiry created successfully', [
                'enquiry_id' => $enquiry->id,
                'city' => $normalizedCity,
                'vendors_notified' => $vendors->count()
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
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit enquiry. Please try again.'
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
            'vendor_emails' => $vendors->pluck('email')->toAebsiterray()
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

    /**
     * Show a single direct enquiry for the vendor panel
     */
    public function vendorShow($enquiryId)
    {
        $vendor = auth()->user();
        $enquiry = DirectEnquiry::whereHas('assignedVendors', function($q) use ($vendor) {
                $q->where('vendor_id', $vendor->id);
            })
            ->where('id', $enquiryId)
            ->firstOrFail();

        return view('vendor.direct-enquiries.show', [
            'enquiry' => $enquiry,
            'vendor' => $vendor,
        ]);
    }

    /**
     * List direct enquiries assigned to the vendor (with optional type filter)
     */
   /**
     * Display vendor's assigned enquiries
     */
    public function vendorDirectIndex(Request $request)
    {
        $vendor = Auth::user();
        
        $query = $vendor->assignedEnquiries()
            ->with(['assignedVendors' => function ($q) use ($vendor) {
                $q->where('users.id', $vendor->id);
            }])
            ->latest('direct_web_enquiries.created_at');
        
        // Filter by status
        if ($request->filled('status')) {
            $query->wherePivot('response_status', $request->status);
        }
        
        // Filter by viewed
        if ($request->filled('viewed')) {
            $viewed = $request->boolean('viewed');
            $query->wherePivot('has_viewed', $viewed);
        }
        
        $enquiries = $query->paginate(15);
        
        // Get counts for dashboard
        $counts = [
            'total' => $vendor->assignedEnquiries()->count(),
            'new' => $vendor->newEnquiries()->count(),
            'pending' => $vendor->assignedEnquiries()->wherePivot('response_status', 'pending')->count(),
            'interested' => $vendor->assignedEnquiries()->wherePivot('response_status', 'interested')->count(),
            'quoted' => $vendor->assignedEnquiries()->wherePivot('response_status', 'quote_sent')->count(),
        ];
        
        return view('enquiries.vendor.direct-enquiry-index', compact('enquiries', 'counts'));
    }
    
    /**
     * Show single enquiry details
     */
    public function show(DirectEnquiry $enquiry)
    {
        $vendor = Auth::user();
        
        // Check if vendor is assigned to this enquiry
        if (!$enquiry->assignedVendors()->where('users.id', $vendor->id)->exists()) {
            abort(403, 'Unauthorized access to this enquiry');
        }
        
        // Mark as viewed
        $enquiry->markViewedBy($vendor->id);
        
        // Load relationships
        $enquiry->load(['assignedVendors' => function ($q) use ($vendor) {
            $q->where('users.id', $vendor->id);
        }]);
        
        // Get vendor's pivot data
        $vendorPivot = $enquiry->assignedVendors->first()->pivot;
        
        return view('vendor.enquiries.show', compact('enquiry', 'vendorPivot'));
    }
    
    /**
     * Update vendor's response to enquiry
     */
    public function respond(Request $request, DirectEnquiry $enquiry)
    {
        $vendor = Auth::user();
        
        // Validate
        $request->validate([
            'response_status' => 'required|in:interested,quote_sent,declined',
            'vendor_notes' => 'nullable|string|max:1000',
            'quoted_price' => 'nullable|numeric|min:0|max:99999999.99',
        ]);
        
        // Check if vendor is assigned
        if (!$enquiry->assignedVendors()->where('users.id', $vendor->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this enquiry'
            ], 403);
        }
        
        try {
            DB::beginTransaction();
            
            // Prepare update data
            $updateData = [
                'response_status' => $request->response_status,
                'responded_at' => now(),
            ];
            
            if ($request->filled('vendor_notes')) {
                $updateData['vendor_notes'] = $request->vendor_notes;
            }
            
            if ($request->filled('quoted_price')) {
                $updateData['quoted_price'] = $request->quoted_price;
            }
            
            if ($request->response_status === 'quote_sent') {
                $updateData['quote_sent_at'] = now();
            }
            
            // Update pivot table
            $enquiry->updateVendorResponse($vendor->id, $request->response_status, $updateData);
            
            // Send notification to customer if interested or quote sent
            if (in_array($request->response_status, ['interested', 'quote_sent'])) {
                Mail::to($enquiry->email)->queue(
                    new CustomerVendorResponseMail($enquiry, $vendor, $request->response_status, $request->quoted_price)
                );
            }
            
            // Notify admin
            $admins = User::whereIn('active_role', ['admin', 'superadmin'])->get();
            foreach ($admins as $admin) {
                $admin->notify(new VendorRespondedNotification($enquiry, $vendor, $request->response_status));
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Response submitted successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Vendor response failed', [
                'vendor_id' => $vendor->id,
                'enquiry_id' => $enquiry->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit response'
            ], 500);
        }
    }
    
    /**
     * Mark enquiry as viewed
     */
    public function markViewed(DirectEnquiry $enquiry)
    {
        $vendor = Auth::user();
        
        if (!$enquiry->assignedVendors()->where('users.id', $vendor->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        $enquiry->markViewedBy($vendor->id);
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Get vendor's enquiry statistics
     */
    public function statistics()
    {
        $vendor = Auth::user();
        
        $stats = [
            'total_enquiries' => $vendor->assignedEnquiries()->count(),
            'new_enquiries' => $vendor->newEnquiries()->count(),
            'viewed_enquiries' => $vendor->assignedEnquiries()->wherePivot('has_viewed', true)->count(),
            'responded' => $vendor->assignedEnquiries()->where('response_status', '!=', 'pending')->count(),
            'interested' => $vendor->assignedEnquiries()->wherePivot('response_status', 'interested')->count(),
            'quotes_sent' => $vendor->assignedEnquiries()->wherePivot('response_status', 'quote_sent')->count(),
            'declined' => $vendor->assignedEnquiries()->wherePivot('response_status', 'declined')->count(),
            'response_rate' => 0,
            'avg_response_time_hours' => 0,
        ];
        
        // Calculate response rate
        if ($stats['total_enquiries'] > 0) {
            $stats['response_rate'] = round(($stats['responded'] / $stats['total_enquiries']) * 100, 2);
        }
        
        // Calculate average response time
        $avgResponseTime = DB::table('enquiry_vendor')
            ->where('vendor_id', $vendor->id)
            ->whereNotNull('responded_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, responded_at)) as avg_hours')
            ->value('avg_hours');
        
        $stats['avg_response_time_hours'] = round($avgResponseTime ?? 0, 1);
        
        return response()->json($stats);
    }
    
    /**
     * Update vendor notes
     */
    public function updateNotes(Request $request, DirectEnquiry $enquiry)
    {
        $vendor = Auth::user();
        
        $request->validate([
            'notes' => 'required|string|max:1000'
        ]);
        
        if (!$enquiry->assignedVendors()->where('users.id', $vendor->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        $enquiry->assignedVendors()->updateExistingPivot($vendor->id, [
            'vendor_notes' => $request->notes
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Notes updated successfully'
        ]);
    }
}