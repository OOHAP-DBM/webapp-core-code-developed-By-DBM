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
use Modules\Enquiries\Notifications\CustomerDirectEnquiryNotification;
use App\Models\User;

class DirectEnquiryApiController extends Controller
{
    // =========================================================================
    // POST /api/v1/enquiries/otp/send
    // Send OTP to phone number before submitting enquiry
    // Public endpoint — no auth required
    // =========================================================================
    /**
     * @OA\Post(
     *     path="/enquiries/direct/otp-send",
     *     summary="Send OTP to phone for direct enquiry (public)",
     *     description="Sends a 4-digit OTP to the provided Indian mobile number for direct enquiry submission. No authentication required.",
     *     tags={"Direct Enquiries"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone"},
     *             @OA\Property(property="phone", type="string", example="9876543210", description="10-digit Indian mobile number")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OTP sent successfully to +91-98XXXXXX10")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Enter a valid 10-digit Indian mobile number.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to send OTP",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to send OTP. Please try again.")
     *         )
     *     )
     * )
     */
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
    /**
     * @OA\Post(
     *     path="/enquiries/direct/otp-verify",
     *     summary="Verify OTP for direct enquiry (public)",
     *     description="Verifies the 4-digit OTP sent to the provided phone number for direct enquiry submission. No authentication required.",
     *     tags={"Direct Enquiries"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone", "otp"},
     *             @OA\Property(property="phone", type="string", example="9876543210"),
     *             @OA\Property(property="otp", type="string", example="1234")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Phone verified successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Phone verified successfully."),
     *             @OA\Property(property="phone", type="string", example="9876543210"),
     *             @OA\Property(property="verified", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid or expired OTP",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid or expired OTP. Please request a new one.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Verification failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Verification failed. Please try again.")
     *         )
     *     )
     * )
     */
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
    /**
     * @OA\Post(
     *     path="/enquiries/direct",
     *     summary="Submit a direct enquiry (public, OTP verified)",
     *     description="Creates a direct enquiry for OOH/DOOH hoardings. Phone must be OTP-verified within last 20 minutes.",
     *     tags={"Direct Enquiries"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "phone", "hoarding_type", "location_city", "remarks", "phone_verified"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="phone", type="string", example="9876543210"),
     *             @OA\Property(property="hoarding_type", type="array", @OA\Items(type="string", enum={"OOH","DOOH"}), example={"OOH"}),
     *             @OA\Property(property="location_city", type="string", example="Lucknow"),
     *             @OA\Property(property="preferred_locations", type="array", @OA\Items(type="string"), example={"Hazratganj", "Aminabad"}),
     *             @OA\Property(property="remarks", type="string", example="Looking for prime locations for a 2-week campaign."),
     *             @OA\Property(property="preferred_modes", type="array", @OA\Items(type="string", enum={"Call","WhatsApp","Email"}), example={"Call","Email"}),
     *             @OA\Property(property="phone_verified", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Enquiry submitted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Enquiry submitted successfully! You will receive quotes within 24-48 hours."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="enquiry_id", type="integer", example=123)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or phone not verified",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Phone verification expired or not completed. Please verify your phone again."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Submission failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to submit enquiry. Please try again.")
     *         )
     *     )
     * )
     */
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

                // Notify vendors with push and in-app notifications
                foreach ($vendors as $vendor) {
                    Mail::to($vendor->email)->queue(new VendorDirectEnquiryMail($enquiry, $vendor));

                    // In-app notification
                    $vendor->notify(new VendorDirectEnquiryNotification($enquiry));

                    // Push notification with hoarding details
                    $hoardingTypes = implode(', ', array_map('strtoupper', explode(',', $request->hoarding_type[0] ?? 'OOH')));
                    send(
                        $vendor,
                        'New Hoarding Enquiry Received',
                        "New {$hoardingTypes} enquiry from {$enquiry->name} in {$normalizedCity}",
                        [
                            'type'           => 'vendor_direct_enquiry',
                            'enquiry_id'     => $enquiry->id,
                            'customer_name'  => $enquiry->name,
                            'hoarding_type'  => implode(',', $request->hoarding_type),
                            'city'           => $normalizedCity,
                            'source'         => 'mobile_app'
                        ]
                    );
                }
            }

            // Customer confirmation email
            Mail::to($enquiry->email)->queue(new UserDirectEnquiryConfirmation($enquiry));

            // Send in-app notification to customer
            // Note: Customer is not yet a registered user, so we'll store this for when they login
            // or for guest notification display via email link

            // Attempt to notify if customer exists in system by email
            $existingCustomer = User::where('email', $enquiry->email)
                ->where('active_role', 'customer')
                ->first();

            if ($existingCustomer) {
                // In-app notification for registered customer
                $existingCustomer->notify(new \Modules\Enquiries\Notifications\CustomerDirectEnquiryNotification($enquiry));

                // Push notification to customer
                $hoardingTypes = implode(', ', array_map('strtoupper', explode(',', $request->hoarding_type[0] ?? 'OOH')));
                send(
                    $existingCustomer,
                    'Enquiry Submitted Successfully',
                    "Your {$hoardingTypes} hoarding enquiry for {$normalizedCity} has been submitted.",
                    [
                        'type'           => 'customer_direct_enquiry',
                        'enquiry_id'     => $enquiry->id,
                        'hoarding_type'  => implode(',', $request->hoarding_type),
                        'city'           => $normalizedCity,
                        'status'         => 'submitted'
                    ]
                );
            }

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
    /**
     * @OA\Get(
     *     path="/vendor/enquiries",
     *     summary="List all assigned direct enquiries for vendor",
     *     description="Returns paginated list of direct enquiries assigned to the authenticated vendor. Supports filtering by status and viewed flag.",
     *     tags={"Direct Enquiries"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="status", in="query", description="Filter by response status", required=false, @OA\Schema(type="string", enum={"pending","interested","quote_sent","declined"})),
     *     @OA\Parameter(name="viewed", in="query", description="Filter by viewed status (true/false)", required=false, @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="per_page", in="query", description="Results per page (default: 15, max: 50)", required=false, @OA\Schema(type="integer", example=15)),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of direct enquiries",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=2),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=20)
     *             )
     *         )
     *     )
     * )
     */
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
    /**
     * @OA\Get(
     *     path="/vendor/enquiries/{id}",
     *     summary="Get single direct enquiry detail for vendor",
     *     description="Returns detail of a direct enquiry assigned to the authenticated vendor. Marks as viewed.",
     *     tags={"Direct Enquiries"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", description="Direct enquiry ID", required=true, @OA\Schema(type="integer", example=123)),
     *     @OA\Response(
     *         response=200,
     *         description="Direct enquiry detail",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Enquiry not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Enquiry not found.")
     *         )
     *     )
     * )
     */
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
    /**
     * @OA\Post(
     *     path="/vendor/enquiries/{id}/respond",
     *     summary="Vendor respond to direct enquiry",
     *     description="Submit a response to a direct enquiry (interested, quote_sent, declined) as a vendor.",
     *     tags={"Direct Enquiries"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", description="Direct enquiry ID", required=true, @OA\Schema(type="integer", example=123)),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"response_status"},
     *             @OA\Property(property="response_status", type="string", enum={"interested","quote_sent","declined"}, example="quote_sent"),
     *             @OA\Property(property="vendor_notes", type="string", example="We can offer a special rate."),
     *             @OA\Property(property="quoted_price", type="number", format="float", example=15000.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response submitted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Response submitted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Enquiry not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Enquiry not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to submit response",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to submit response. Please try again.")
     *         )
     *     )
     * )
     */
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
    /**
     * @OA\Patch(
     *     path="/vendor/enquiries/{id}/notes",
     *     summary="Vendor update private notes on direct enquiry",
     *     description="Update private notes for a direct enquiry as a vendor.",
     *     tags={"Direct Enquiries"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", description="Direct enquiry ID", required=true, @OA\Schema(type="integer", example=123)),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"notes"},
     *             @OA\Property(property="notes", type="string", example="Follow up next week.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notes updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notes updated successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Enquiry not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Enquiry not found.")
     *         )
     *     )
     * )
     */
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
    /**
     * @OA\Patch(
     *     path="/vendor/enquiries/{id}/mark-viewed",
     *     summary="Vendor mark direct enquiry as viewed",
     *     description="Explicitly mark a direct enquiry as viewed by the vendor.",
     *     tags={"Direct Enquiries"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", description="Direct enquiry ID", required=true, @OA\Schema(type="integer", example=123)),
     *     @OA\Response(
     *         response=200,
     *         description="Marked as viewed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Marked as viewed.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Enquiry not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Enquiry not found.")
     *         )
     *     )
     * )
     */
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
    /**
     * @OA\Get(
     *     path="/vendor/enquiries/statistics",
     *     summary="Vendor direct enquiry statistics",
     *     description="Returns statistics for direct enquiries assigned to the authenticated vendor.",
     *     tags={"Direct Enquiries"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Statistics data",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_enquiries", type="integer", example=20),
     *                 @OA\Property(property="new", type="integer", example=5),
     *                 @OA\Property(property="viewed", type="integer", example=10),
     *                 @OA\Property(property="pending", type="integer", example=3),
     *                 @OA\Property(property="interested", type="integer", example=2),
     *                 @OA\Property(property="quotes_sent", type="integer", example=1),
     *                 @OA\Property(property="declined", type="integer", example=1),
     *                 @OA\Property(property="response_rate_percent", type="number", format="float", example=75.0),
     *                 @OA\Property(property="avg_response_time_hours", type="number", format="float", example=12.5)
     *             )
     *         )
     *     )
     * )
     */
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
                'avg_response_time_hours' => round($avgResponseHours ?? 0, 1),
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
                'quote_sent_at' => $pivot?->quote_sent_at,
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
            ->with([
                'hoardings' => fn($q) => $q
                    ->where('city', 'like', "%{$city}%")
                    ->where('status', 'active')
                    ->select('id', 'vendor_id', 'title', 'city', 'locality', 'hoarding_type')
            ])
            ->get();
    }
}
