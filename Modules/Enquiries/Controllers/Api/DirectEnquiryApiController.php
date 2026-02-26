<?php

namespace Modules\Enquiries\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use App\Services\GuestOtpService;
use Modules\Enquiries\Models\DirectEnquiry;
use Modules\Enquiries\Mail\AdminDirectEnquiryMail;
use Modules\Enquiries\Mail\UserDirectEnquiryConfirmation;
use Modules\Enquiries\Mail\VendorDirectEnquiryMail;
use App\Notifications\AdminDirectEnquiryNotification;
use Modules\Enquiries\Notifications\VendorDirectEnquiryNotification;
use App\Models\User;

class DirectEnquiryApiController extends Controller
{
    // =========================================================================
    // POST /api/v1/enquiries/otp/send
    // Send OTP to phone number before submitting enquiry
    // Public endpoint — no auth required
    // =========================================================================
    public function sendOtp(Request $request, GuestOtpService $otpService): JsonResponse
    {
        $request->validate([
            'phone' => [
                'required',
                'string',
                'regex:/^[6-9][0-9]{9}$/',
            ],
        ], [
            'phone.regex' => 'Enter a valid 10-digit Indian mobile number.',
        ]);

        try {
            $otpService->generate($request->phone, 'direct_enquiry');

            $masked = $this->maskPhone($request->phone);

            Log::info('Enquiry OTP sent', [
                'phone_masked' => $masked,
                'ip'           => $request->ip(),
            ]);

            return $this->success('OTP sent successfully to ' . $masked);

        } catch (\Throwable $e) {
            Log::error('Enquiry OTP send failed', [
                'phone_masked' => $this->maskPhone($request->phone),
                'error'        => $e->getMessage(),
            ]);

            return $this->error('Failed to send OTP. Please try again.', 500);
        }
    }

    // =========================================================================
    // POST /api/v1/enquiries/otp/verify
    // Verify OTP — client must pass otp_token in subsequent submit call
    // Public endpoint — no auth required
    // =========================================================================
    public function verifyOtp(Request $request, GuestOtpService $otpService): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'regex:/^[6-9][0-9]{9}$/'],
            'otp'   => ['required', 'digits:4'],
        ]);

        try {
            $verified = $otpService->verify(
                $request->phone,
                $request->otp,
                'direct_enquiry'
            );

            if (!$verified) {
                Log::warning('Invalid OTP attempt', [
                    'phone_masked' => $this->maskPhone($request->phone),
                    'ip'           => $request->ip(),
                ]);

                return $this->error('Invalid or expired OTP. Please request a new one.', 422);
            }

            Log::info('Enquiry OTP verified', [
                'phone_masked' => $this->maskPhone($request->phone),
            ]);

            return $this->success('Phone verified successfully.', [
                'phone'    => $request->phone,
                'verified' => true,
            ]);

        } catch (\Throwable $e) {
            Log::error('Enquiry OTP verify failed', [
                'phone_masked' => $this->maskPhone($request->phone),
                'error'        => $e->getMessage(),
            ]);

            return $this->error('Verification failed. Please try again.', 500);
        }
    }

    // =========================================================================
    // POST /api/v1/enquiries
    // Submit a direct enquiry
    // Public endpoint — phone must be OTP-verified within last 20 minutes
    // =========================================================================
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'                   => 'required|string|min:3|max:255',
            'email'                  => 'required|email|max:255',
            'phone'                  => ['required', 'digits:10', 'regex:/^[6-9][0-9]{9}$/'],
            'hoarding_type'          => 'required|array|min:1',
            'hoarding_type.*'        => 'in:DOOH,OOH',
            'location_city'          => 'required|string|max:255',
            'preferred_locations'    => 'nullable|array',
            'preferred_locations.*'  => 'nullable|string|max:255',
            'remarks'                => 'required|string|min:10|max:2000',
            'preferred_modes'        => 'nullable|array',
            'preferred_modes.*'      => 'in:Call,WhatsApp,Email',

            // Mobile sends this flag after calling verifyOtp successfully
            'phone_verified'         => 'required|accepted',
        ], [
            'phone_verified.accepted' => 'Phone number must be verified via OTP before submitting.',
        ]);

        // Confirm OTP was actually verified in DB (not just a flag from client)
        $phoneVerified = DB::table('guest_user_otps')
            ->where('identifier', $request->phone)
            ->where('purpose', 'direct_enquiry')
            ->whereNotNull('verified_at')
            ->where('created_at', '>', now()->subMinutes(20))
            ->exists();

        if (!$phoneVerified) {
            return $this->error(
                'Phone verification expired or not completed. Please verify your phone again.',
                422,
                ['phone' => ['Phone verification is required.']]
            );
        }

        try {
            DB::beginTransaction();

            $normalizedCity       = $this->normalizeCityName($request->location_city);
            $preferredLocations   = $this->cleanLocations($request->preferred_locations ?? []);
            $normalizedLocalities = array_map(
                fn($loc) => $this->normalizeLocalityName($loc, $normalizedCity),
                $preferredLocations
            );

            $enquiry = DirectEnquiry::create([
                'name'                => $request->name,
                'email'               => $request->email,
                'phone'               => $request->phone,
                'hoarding_type'       => implode(',', $request->hoarding_type),
                'location_city'       => $normalizedCity,
                'preferred_locations' => $normalizedLocalities,
                'remarks'             => $request->remarks,
                'preferred_modes'     => $request->preferred_modes ?? ['Call', 'Email'],
                'is_phone_verified'   => true,
                'status'              => 'new',
                'source'              => 'mobile_app',
            ]);

            // Find matching vendors and notify them
            $vendors = $this->findRelevantVendors(
                $normalizedCity,
                $normalizedLocalities,
                $request->hoarding_type
            );

            if ($vendors->isNotEmpty()) {
                $enquiry->assignedVendors()->attach($vendors->pluck('id'));

                foreach ($vendors as $vendor) {
                    Mail::to($vendor->email)->queue(new VendorDirectEnquiryMail($enquiry, $vendor));
                    $vendor->notify(new VendorDirectEnquiryNotification($enquiry, $vendor));
                }
            }

            // Confirm to customer
            Mail::to($enquiry->email)->queue(new UserDirectEnquiryConfirmation($enquiry));

            // Notify admins
            $admins = User::whereIn('active_role', ['admin', 'superadmin'])
                ->where('status', 'active')
                ->get();

            foreach ($admins as $admin) {
                Mail::to($admin->email)->queue(new AdminDirectEnquiryMail($enquiry, $vendors));
                $admin->notify(new AdminDirectEnquiryNotification($enquiry));
            }

            DB::commit();

            // Cleanup OTP records
            DB::table('guest_user_otps')
                ->where('identifier', $request->phone)
                ->where('purpose', 'direct_enquiry')
                ->delete();

            Log::info('Direct enquiry submitted via mobile', [
                'enquiry_id'       => $enquiry->id,
                'city'             => $normalizedCity,
                'vendors_notified' => $vendors->count(),
            ]);

            return $this->success(
                'Enquiry submitted successfully! You will receive quotes within 24-48 hours.',
                ['enquiry_id' => $enquiry->id],
                201
            );

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Mobile enquiry submission failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->error('Failed to submit enquiry. Please try again.', 500);
        }
    }

    // =========================================================================
    // GET /api/v1/vendor/enquiries
    // Vendor: list all assigned enquiries with optional filters
    // Auth: vendor token required
    // =========================================================================
    public function vendorIndex(Request $request): JsonResponse
    {
        $vendor = Auth::user();

        $query = $vendor->assignedEnquiries()
            ->with(['assignedVendors' => fn($q) => $q->where('users.id', $vendor->id)])
            ->latest('direct_web_enquiries.created_at');

        // Filter by response status: pending | interested | quote_sent | declined
        if ($request->filled('status')) {
            $request->validate(['status' => 'in:pending,interested,quote_sent,declined']);
            $query->wherePivot('response_status', $request->status);
        }

        // Filter by viewed: true | false
        if ($request->has('viewed')) {
            $query->wherePivot('has_viewed', $request->boolean('viewed'));
        }

        $perPage   = min((int) $request->input('per_page', 15), 50);
        $enquiries = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $enquiries->map(fn($e) => $this->formatEnquiryDetail($e, $e->assignedVendors->first()?->pivot)),
            'meta'    => [
                'current_page' => $enquiries->currentPage(),
                'last_page'    => $enquiries->lastPage(),
                'per_page'     => $enquiries->perPage(),
                'total'        => $enquiries->total(),
            ],
        ]);
    }

    // =========================================================================
    // GET /api/v1/vendor/enquiries/{id}
    // Vendor: get single enquiry detail + mark as viewed
    // Auth: vendor token required
    // =========================================================================
    public function vendorShow(int $id): JsonResponse
    {
        $vendor  = Auth::user();
        $enquiry = $this->findVendorEnquiry($id, $vendor->id);

        if (!$enquiry) {
            return $this->error('Enquiry not found.', 404);
        }

        // Auto-mark as viewed
        $enquiry->markViewedBy($vendor->id);

        $enquiry->load(['assignedVendors' => fn($q) => $q->where('users.id', $vendor->id)]);
        $pivot = $enquiry->assignedVendors->first()?->pivot;

        return response()->json([
            'success' => true,
            'data'    => $this->formatEnquiryDetail($enquiry, $pivot),
        ]);
    }

    // =========================================================================
    // POST /api/v1/vendor/enquiries/{id}/respond
    // Vendor: submit response (interested / quote_sent / declined)
    // Auth: vendor token required
    // =========================================================================
    public function respond(Request $request, int $id): JsonResponse
    {
        $vendor  = Auth::user();
        $enquiry = $this->findVendorEnquiry($id, $vendor->id);

        if (!$enquiry) {
            return $this->error('Enquiry not found.', 404);
        }

        $request->validate([
            'response_status' => 'required|in:interested,quote_sent,declined',
            'vendor_notes'    => 'nullable|string|max:1000',
            'quoted_price'    => 'nullable|numeric|min:0|max:99999999.99',
        ]);

        try {
            DB::beginTransaction();

            $updateData = [
                'response_status' => $request->response_status,
                'responded_at'    => now(),
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

            $enquiry->updateVendorResponse($vendor->id, $request->response_status, $updateData);

            // Notify admin
            $admins = User::whereIn('active_role', ['admin', 'superadmin'])->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\VendorRespondedNotification($enquiry, $vendor, $request->response_status));
            }

            DB::commit();

            return $this->success('Response submitted successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Vendor respond failed (mobile)', [
                'vendor_id'  => $vendor->id,
                'enquiry_id' => $id,
                'error'      => $e->getMessage(),
            ]);

            return $this->error('Failed to submit response. Please try again.', 500);
        }
    }

    // =========================================================================
    // PATCH /api/v1/vendor/enquiries/{id}/notes
    // Vendor: update private notes on an enquiry
    // Auth: vendor token required
    // =========================================================================
    public function updateNotes(Request $request, int $id): JsonResponse
    {
        $vendor  = Auth::user();
        $enquiry = $this->findVendorEnquiry($id, $vendor->id);

        if (!$enquiry) {
            return $this->error('Enquiry not found.', 404);
        }

        $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $enquiry->assignedVendors()->updateExistingPivot($vendor->id, [
            'vendor_notes' => $request->notes,
        ]);

        return $this->success('Notes updated successfully.');
    }

    // =========================================================================
    // PATCH /api/v1/vendor/enquiries/{id}/mark-viewed
    // Vendor: explicitly mark an enquiry as viewed
    // Auth: vendor token required
    // =========================================================================
    public function markViewed(int $id): JsonResponse
    {
        $vendor  = Auth::user();
        $enquiry = $this->findVendorEnquiry($id, $vendor->id);

        if (!$enquiry) {
            return $this->error('Enquiry not found.', 404);
        }

        $enquiry->markViewedBy($vendor->id);

        return $this->success('Marked as viewed.');
    }

    // =========================================================================
    // GET /api/v1/vendor/enquiries/statistics
    // Vendor: enquiry stats for dashboard
    // Auth: vendor token required
    // =========================================================================
    public function statistics(): JsonResponse
    {
        $vendor = Auth::user();

        $total     = $vendor->assignedEnquiries()->count();
        $responded = $vendor->assignedEnquiries()
            ->wherePivot('response_status', '!=', 'pending')
            ->count();

        $avgResponseHours = DB::table('enquiry_vendor')
            ->where('vendor_id', $vendor->id)
            ->whereNotNull('responded_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, responded_at)) as avg_hours')
            ->value('avg_hours');

        return response()->json([
            'success' => true,
            'data'    => [
                'total_enquiries'        => $total,
                'new'                    => $vendor->newEnquiries()->count(),
                'viewed'                 => $vendor->assignedEnquiries()->wherePivot('has_viewed', true)->count(),
                'pending'                => $vendor->assignedEnquiries()->wherePivot('response_status', 'pending')->count(),
                'interested'             => $vendor->assignedEnquiries()->wherePivot('response_status', 'interested')->count(),
                'quotes_sent'            => $vendor->assignedEnquiries()->wherePivot('response_status', 'quote_sent')->count(),
                'declined'               => $vendor->assignedEnquiries()->wherePivot('response_status', 'declined')->count(),
                'response_rate_percent'  => $total > 0 ? round(($responded / $total) * 100, 2) : 0,
                'avg_response_time_hours'=> round($avgResponseHours ?? 0, 1),
            ],
        ]);
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    private function findVendorEnquiry(int $enquiryId, int $vendorId): ?DirectEnquiry
    {
        return DirectEnquiry::whereHas(
            'assignedVendors',
            fn($q) => $q->where('users.id', $vendorId)
        )->find($enquiryId);
    }

    private function formatEnquiryListItem(DirectEnquiry $enquiry, User $vendor): array
    {
        $pivot = $enquiry->assignedVendors->first()?->pivot;

        return [
            'id'              => $enquiry->id,
            'name'            => $enquiry->name,
            'city'            => $enquiry->location_city,
            'hoarding_type'   => $enquiry->hoarding_type,
            'status'          => $enquiry->status,
            'response_status' => $pivot?->response_status ?? 'pending',
            'has_viewed'      => (bool) ($pivot?->has_viewed ?? false),
            'created_at'      => $enquiry->created_at?->toISOString(),
        ];
    }

    private function formatEnquiryDetail(DirectEnquiry $enquiry, $pivot): array
    {
        return [
            'id'                  => $enquiry->id,
            'name'                => $enquiry->name,
            'email'               => $enquiry->email,
            'phone'               => $enquiry->phone,
            'hoarding_type'       => $enquiry->hoarding_type,
            'location_city'       => $enquiry->location_city,
            'preferred_locations' => $enquiry->preferred_locations,
            'remarks'             => $enquiry->remarks,
            'preferred_modes'     => $enquiry->preferred_modes,
            'status'              => $enquiry->status,
            'source'              => $enquiry->source,
            'created_at'          => $enquiry->created_at?->toISOString(),

            // Vendor-specific pivot data
            'vendor_response' => [
                'status'       => $pivot?->response_status ?? 'pending',
                'notes'        => $pivot?->vendor_notes,
                'quoted_price' => $pivot?->quoted_price,
                'has_viewed'   => (bool) ($pivot?->has_viewed ?? false),
                'responded_at' => $pivot?->responded_at,
                'quote_sent_at'=> $pivot?->quote_sent_at,
            ],
        ];
    }

    private function cleanLocations(array $locations): array
    {
        $cleaned = array_values(array_filter(
            array_map('trim', $locations),
            fn($loc) => !empty($loc)
        ));

        return empty($cleaned) ? ['To be discussed'] : $cleaned;
    }

    private function maskPhone(string $phone): string
    {
        return '+91-' . substr($phone, 0, 2) . 'XXXXXX' . substr($phone, -2);
    }

    private function success(string $message, array $data = [], int $status = 200): JsonResponse
    {
        $payload = ['success' => true, 'message' => $message];

        if (!empty($data)) {
            $payload['data'] = $data;
        }

        return response()->json($payload, $status);
    }

    private function error(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        $payload = ['success' => false, 'message' => $message];

        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    // ── City / locality normalization (same logic as web controller) ──────────

    private function normalizeCityName(string $city): string
    {
        $city = trim(strtolower($city));

        $cityMappings = [
            'lucknow'    => ['lucknow', 'lko', 'lakhnau', 'lakhnaow', 'lucknaw', 'lukhnow'],
            'kanpur'     => ['kanpur', 'cawnpore', 'kanpoor', 'kanpore'],
            'varanasi'   => ['varanasi', 'banaras', 'benares', 'kashi', 'varnasi'],
            'agra'       => ['agra', 'agrah', 'aagra'],
            'prayagraj'  => ['prayagraj', 'allahabad', 'ilahabad', 'prayag'],
            'ballia'     => ['ballia', 'balliya', 'balia', 'balya'],
            'mohali'     => ['mohali', 'sahibzada ajit singh nagar', 'sas nagar', 'mohalli'],
            'chandigarh' => ['chandigarh', 'chandigrah', 'chandigar'],
            'mumbai'     => ['mumbai', 'bombay', 'mumbay', 'mumby', 'mombai'],
            'delhi'      => ['delhi', 'dilli', 'dehli', 'new delhi', 'newdelhi'],
            'bangalore'  => ['bangalore', 'bengaluru', 'bangaluru', 'banglore', 'bengaloor'],
            'kolkata'    => ['kolkata', 'calcutta', 'kolkatta', 'kalkatta', 'kolkota'],
            'hyderabad'  => ['hyderabad', 'hydrabad', 'haidarabad', 'hyderabadh'],
            'chennai'    => ['chennai', 'madras', 'chenai', 'chenna'],
            'pune'       => ['pune', 'poona', 'puna'],
            'ahmedabad'  => ['ahmedabad', 'amdavad', 'ahmadabad', 'ahmdabad'],
            'jaipur'     => ['jaipur', 'jaypur', 'jeypore', 'jeypur'],
            'surat'      => ['surat', 'surath', 'suratt'],
            'indore'     => ['indore', 'indor', 'indaur'],
            'bhopal'     => ['bhopal', 'bhopl', 'bhopaal'],
        ];

        foreach ($cityMappings as $standard => $variations) {
            if (in_array($city, $variations)) {
                return ucwords($standard);
            }
        }

        foreach ($cityMappings as $standard => $variations) {
            foreach ($variations as $variation) {
                if (levenshtein($city, $variation) <= 2) {
                    return ucwords($standard);
                }
            }
        }

        return ucwords($city);
    }

    private function normalizeLocalityName(string $locality, string $city): string
    {
        if ($locality === 'To be discussed') {
            return $locality;
        }

        $locality = trim(strtolower($locality));
        $city     = strtolower($city);

        $localityMappings = [
            'lucknow' => [
                'hazratganj'   => ['hazratganj', 'hazrat ganj', 'ganj'],
                'gomti nagar'  => ['gomti nagar', 'gomtinagar', 'gomti', 'gomati nagar'],
                'indira nagar' => ['indira nagar', 'indiranagar', 'indra nagar'],
                'aminabad'     => ['aminabad', 'amina bad', 'aminaabad'],
                'alambagh'     => ['alambagh', 'alam bagh', 'alambag'],
            ],
            'ballia' => [
                'rasra'    => ['rasra', 'raasra', 'rasara'],
                'kharuwan' => ['kharuwan', 'kharwan'],
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

    private function findRelevantVendors(string $city, array $localities, array $hoardingTypes)
    {
        $hoardingTypes = array_map('strtolower', $hoardingTypes);

        $query = DB::table('hoardings')
            ->select('vendor_id')
            ->where('status', 'active')
            ->whereNotNull('vendor_id')
            ->where(function ($q) use ($city) {
                $q->where('city', 'like', "%{$city}%")
                  ->orWhere('state', 'like', "%{$city}%")
                  ->orWhere('locality', 'like', "%{$city}%");
            })
            ->where(function ($q) use ($hoardingTypes) {
                foreach ($hoardingTypes as $type) {
                    $q->orWhere('hoarding_type', 'like', "%{$type}%");
                }
            });

        if (!empty($localities) && $localities[0] !== 'To be discussed') {
            $query->where(function ($q) use ($localities) {
                foreach ($localities as $locality) {
                    $q->orWhere('locality', 'like', "%{$locality}%")
                      ->orWhere('address', 'like', "%{$locality}%")
                      ->orWhere('landmark', 'like', "%{$locality}%");
                }
            });
        }

        $vendorIds = $query->distinct()->pluck('vendor_id')->filter()->unique()->toArray();

        // Fallback: any vendor with a hoarding in that city
        if (empty($vendorIds)) {
            $vendorIds = DB::table('hoardings')
                ->select('vendor_id')
                ->where('status', 'active')
                ->where('city', 'like', "%{$city}%")
                ->whereNotNull('vendor_id')
                ->distinct()
                ->pluck('vendor_id')
                ->toArray();
        }

        return User::whereIn('id', $vendorIds)
            ->where('active_role', 'vendor')
            ->where('status', 'active')
            ->whereNotNull('email')
            ->with(['hoardings' => fn($q) => $q
                ->where('city', 'like', "%{$city}%")
                ->where('status', 'active')
                ->select('id', 'vendor_id', 'title', 'city', 'locality', 'hoarding_type')
            ])
            ->get();
    }
}