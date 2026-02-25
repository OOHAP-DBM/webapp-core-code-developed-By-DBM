<?php

namespace Modules\Enquiries\Controllers\Api;

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
use App\Models\Hoarding;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class DirectEnquiryApiController extends Controller

{
    // List all direct enquiries (admin)
    public function index(Request $request): JsonResponse
    {
        $query = DirectEnquiry::query();
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('location_city', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('hoarding_type')) {
            $query->where('hoarding_type', 'like', "%{$request->hoarding_type}%");
        }
        $enquiries = $query->with('assignedVendors')->latest()->paginate(15)->withQueryString();
        return response()->json($enquiries);
    }

    // Send OTP
    public function sendOtp(Request $request, GuestOtpService $otpService): JsonResponse
    {
        $request->validate([
            'identifier' => 'required|string'
        ]);
        $identifier = $request->identifier;
        if (!preg_match('/^[6-9][0-9]{9}$/', $identifier)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid phone number format. Must be 10 digits starting with 6-9.'
            ], 422);
        }
        try {
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

    // Verify OTP
    public function verifyOtp(Request $request, GuestOtpService $otpService): JsonResponse
    {
        $request->validate([
            'identifier' => 'required|string',
            'otp' => 'required|digits:4'
        ]);
        try {
            $verified = $otpService->verify($request->identifier, $request->otp, 'direct_enquiry');
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

    // Store new enquiry
    public function store(Request $request): JsonResponse
    {
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
            'captcha_id' => 'required|string',
            'captcha_answer' => 'required|numeric',
            'phone_verified' => 'required|in:1'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        // Stateless captcha validation (mobile-friendly)
        $captchaKey = 'captcha_' . $request->captcha_id;
        $expectedAnswer = \Cache::pull($captchaKey); // Remove after checking
        if (!$expectedAnswer || (int)$request->captcha_answer !== (int)$expectedAnswer) {
            return response()->json([
                'success' => false,
                'errors' => ['captcha' => ['Incorrect captcha answer']]
            ], 422);
        }
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
            $data = $validator->validated();
            $normalizedCity = $this->normalizeCityName($data['location_city']);
            $preferredLocations = !empty($data['preferred_locations']) 
                ? array_values(array_filter(
                    array_map('trim', $data['preferred_locations']), 
                    fn($loc) => !empty($loc)
                ))
                : ['To be discussed'];
            $normalizedLocalities = array_map(
                fn($loc) => $this->normalizeLocalityName($loc, $normalizedCity),
                $preferredLocations
            );
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
                'source' => 'api'
            ]);
            $vendors = $this->findRelevantVendors(
                $normalizedCity, 
                $normalizedLocalities,
                $data['hoarding_type']
            );
            if ($vendors->isNotEmpty()) {
                $enquiry->assignedVendors()->attach($vendors->pluck('id'));
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
            Mail::to($enquiry->email)->queue(
                new UserDirectEnquiryConfirmation($enquiry)
            );
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
            DB::table('guest_user_otps')
                ->where('identifier', $request->phone)
                ->where('purpose', 'direct_enquiry')
                ->delete();
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
        foreach ($cityMappings as $standard => $variations) {
            if (in_array($city, $variations)) {
                return ucwords($standard);
            }
        }
        foreach ($cityMappings as $standard => $variations) {
            foreach ($variations as $variation) {
                $distance = levenshtein($city, $variation);
                if ($distance <= 2) {
                    return ucwords($standard);
                }
            }
        }
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
        ];
        if (isset($localityMappings[$city])) {
            foreach ($localityMappings[$city] as $standard => $variations) {
                if (in_array($locality, $variations)) {
                    return ucwords($standard);
                }
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
        $hoardingTypes = array_map('strtolower', $hoardingTypes);
        $hoardingQuery = DB::table('hoardings')
            ->select('vendor_id')
            ->where('status', 'active')
            ->whereNotNull('vendor_id');
        $columns = ['city', 'state', 'locality'];
        $hoardingQuery->where(function ($q) use ($city, $columns) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', "%{$city}%")
                  ->orWhere($column, 'like', $this->getFuzzyPattern($city));
            }
        });
        if (!empty($localities) && $localities[0] !== 'To be discussed') {
            $hoardingQuery->where(function ($q) use ($localities) {
                foreach ($localities as $locality) {
                    $q->orWhere('locality', 'like', "%{$locality}%")
                      ->orWhere('address', 'like', "%{$locality}%")
                      ->orWhere('landmark', 'like', "%{$locality}%");
                }
            });
        }
        $hoardingQuery->where(function ($q) use ($hoardingTypes) {
            foreach ($hoardingTypes as $type) {
                $q->orWhere('hoarding_type', 'like', "%{$type}%");
            }
        });
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
            $vendorIds = DB::table('hoardings')
                ->select('vendor_id')
                ->where('status', 'active')
                ->where('city', 'like', "%{$city}%")
                ->whereNotNull('vendor_id')
                ->distinct()
                ->pluck('vendor_id')
                ->toArray();
        }
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
        $pattern = '%' . implode('%', str_split(strtolower($text))) . '%';
        return $pattern;
    }
}
