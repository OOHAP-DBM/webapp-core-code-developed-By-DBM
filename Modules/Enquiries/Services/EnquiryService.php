<?php

namespace Modules\Enquiries\Services;

use Modules\Enquiries\Models\Enquiry;
use App\Models\Hoarding;
use Modules\Enquiries\Repositories\Contracts\EnquiryRepositoryInterface;
use Modules\Enquiries\Events\EnquiryCreated;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Enquiries\Repositories\EnquiryRepository;
use Modules\Enquiries\Repositories\EnquiryItemRepository;
use Modules\Enquiries\Services\EnquiryItemService;
use Modules\Enquiries\Services\EnquiryNotificationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;


class EnquiryService
{
    public function __construct(
        protected EnquiryRepositoryInterface $repository,
        protected EnquiryRepository $enquiryRepo,
        protected EnquiryItemRepository $itemRepo,
        protected EnquiryItemService $itemService,
        protected EnquiryNotificationService $notificationService,
        protected  ServiceBuilderService $serviceBuilder
    ) {
        $this->serviceBuilder = $serviceBuilder;
    }

    /**
     * Create a new enquiry with hoarding snapshot
     */
    public function createEnquiry(Request $request)
    {
        if (!\Illuminate\Support\Facades\Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please login to raise an enquiry.'
            ], 401);
        }

        try {
            $data = $this->validate($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Enquiry Validation Error', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            throw $e;
        }
        $hoardingIds = (array) $data['hoarding_id'];
        $enquiryType = count($hoardingIds) > 1 ? 'multiple' : 'single';

        return DB::transaction(function () use ($data, $hoardingIds, $enquiryType) {
            $enquiry = $this->enquiryRepo->createHeader($data, $enquiryType);
            $vendorGroups = $this->itemService->handle(
                $enquiry,
                $hoardingIds,
                $data
            );
            $this->notificationService->notifyAll($enquiry, $vendorGroups);

            // Remove hoardings from user's cart after successful enquiry
            \DB::table('carts')
                ->where('user_id', auth()->id())
                ->whereIn('hoarding_id', $hoardingIds)
                ->delete();

            return response()->json([
                'success' => true,
                'enquiry_id' => $enquiry->id,
                'message' => 'Enquiry submitted successfully'
            ]);
        });
    }

    private function validate(Request $request): array
    {
        $rules = [
            'hoarding_id' => 'required',
            'hoarding_id.*' => 'exists:hoardings,id',
            'package_id' => 'nullable',
            'package_id.*' => 'nullable', // Custom validation below
            'months' => 'nullable|array',
            'months.*' => 'nullable|integer|min:1',
            'package_label' => 'nullable',
            'amount' => 'nullable',  // CHANGED: Made optional (was required)
            'amount.*' => 'numeric|min:0',
            'duration_type' => 'required|string',
            'preferred_start_date' => 'required|date',
            'preferred_end_date' => 'nullable|date',
            'customer_name' => 'required|string',
            'customer_mobile' => 'nullable|string',
            'customer_email' => 'nullable|email',
            'message' => 'nullable|string',
            // DOOH
            'video_duration' => 'nullable|integer|in:15,30',
            'slots_count' => 'nullable|integer|min:1',
            'slot' => 'nullable|string',
            'duration_days' => 'nullable|integer|min:1',
        ];
        $validated = $request->validate($rules);

        // CRITICAL: Log what was validated to catch any issues
        \Log::info('[ENQUIRY SERVICE] Validation result for DOOH fields:', [
            'video_duration' => $validated['video_duration'] ?? null,
            'slots_count' => $validated['slots_count'] ?? null,
            'hoarding_type' => $validated['hoarding_id'] ?? 'unknown',
        ]);

        // Custom validation for package_id and months
        $hoardingIds = (array) ($validated['hoarding_id'] ?? []);
        $packageIds = (array) ($validated['package_id'] ?? []);
        $monthsArr = (array) ($validated['months'] ?? []);

        foreach ($hoardingIds as $index => $hoardingId) {
            $packageId = $packageIds[$index] ?? null;
            $months = $monthsArr[$index] ?? null;
            $hoarding = \App\Models\Hoarding::with('doohScreen')->find($hoardingId);
            if (!$hoarding) {
                // throw new \Illuminate\Validation\ValidationException('Invalid hoarding selected.');
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'hoarding_id' => ['Invalid hoarding selected.'],
                ]);
            }
            if ($packageId) {
                // Validate package belongs to hoarding
                if ($hoarding->hoarding_type === 'dooh') {
                    $package = \Modules\DOOH\Models\DOOHPackage::where('id', $packageId)
                        ->where('dooh_screen_id', $hoarding->doohScreen->id ?? null)
                        ->first();
                } else {
                    $package = \Modules\Hoardings\Models\HoardingPackage::where('id', $packageId)
                        ->where('hoarding_id', $hoarding->id)
                        ->first();
                }
                if (!$package) {
                    // If no package is available, allow null
                    continue;
                }
                // months must equal min_booking_duration
                if ($months !== null && $months != $package->min_booking_duration) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'months' => ['Months must match the package minimum booking duration.']
                    ]);
                }
            } else {
                // No package selected, months is required
                if (!$months || !is_numeric($months) || $months < 1) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'months' => ['Months is required when no package is selected.']
                    ]);
                }
            }
        }
        return $validated;
    }
    /**
     * Get enquiry by ID
     */
    public function find(int $id): ?Enquiry
    {
        return $this->repository->find($id);
    }

    /**
     * Get enquiries for current customer
     */
    public function getMyEnquiries(): Collection
    {
        return $this->repository->getByCustomer(Auth::id());
    }

    /**
     * Get enquiries for current vendor
     */
    public function getVendorEnquiries(): Collection
    {
        return $this->repository->getByVendor(Auth::id());
    }

    /**
     * Get enquiries by hoarding
     */
    public function getByHoarding(int $hoardingId): Collection
    {
        return $this->repository->getByHoarding($hoardingId);
    }

    /**
     * Update enquiry status
     */
    public function updateStatus(int $id, string $status): bool
    {
        // Validate status
        $validStatuses = [
            Enquiry::STATUS_PENDING,
            Enquiry::STATUS_ACCEPTED,
            Enquiry::STATUS_REJECTED,
            Enquiry::STATUS_CANCELLED,
        ];

        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid status: {$status}");
        }

        return $this->repository->updateStatus($id, $status);
    }

    /**
     * Accept enquiry
     */
    public function accept(int $id): bool
    {
        return $this->updateStatus($id, Enquiry::STATUS_ACCEPTED);
    }

    /**
     * Reject enquiry
     */
    public function reject(int $id): bool
    {
        return $this->updateStatus($id, Enquiry::STATUS_REJECTED);
    }

    /**
     * Cancel enquiry
     */
    public function cancel(int $id): bool
    {
        return $this->updateStatus($id, Enquiry::STATUS_CANCELLED);
    }

    /**
     * Get all enquiries with filters
     */
    public function getAll(array $filters = []): Collection
    {
        return $this->repository->getAll($filters);
    }

    /**
     * Check if user can view enquiry
     */
    public function canView(Enquiry $enquiry, $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            return false;
        }

        // Admin can view all
        if ($user->hasRole('admin')) {
            return true;
        }

        // Customer can view their own enquiries
        if ($enquiry->customer_id === $user->id) {
            return true;
        }

        // Vendor can view enquiries for their hoardings
        if ($user->hasRole('vendor') && $enquiry->hoarding->vendor_id === $user->id) {
            return true;
        }

        return false;
    }
}
