<?php

namespace Modules\Enquiries\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Threads\Services\ThreadService;
use Modules\Enquiries\Models\Enquiry;
use Modules\Hoardings\Models\Hoarding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EnquiryController extends Controller
{
    protected ThreadService $threadService;

    public function __construct(ThreadService $threadService)
    {
        $this->threadService = $threadService;
    }

    /**
     * Create new enquiry with thread
     */
    public function store(Request $request)
    {
        $request->validate([
            'hoarding_id' => 'required|exists:hoardings,id',
            'preferred_start_date' => 'required|date|after_or_equal:today',
            'preferred_end_date' => 'required|date|after:preferred_start_date',
            'duration_type' => 'nullable|in:days,weeks,months',
            'message' => 'nullable|string|max:2000',
        ]);

        DB::beginTransaction();
        try {
            $hoarding = Hoarding::with('vendor')->findOrFail($request->hoarding_id);
            $user = Auth::user();

            // Create snapshot
            $snapshot = $this->createSnapshot($hoarding, $request);

            // Create enquiry
            $enquiry = Enquiry::create([
                'customer_id' => $user->id,
                'hoarding_id' => $request->hoarding_id,
                'preferred_start_date' => $request->preferred_start_date,
                'preferred_end_date' => $request->preferred_end_date,
                'duration_type' => $request->duration_type ?? Enquiry::DURATION_MONTHS,
                'message' => $request->message,
                'status' => Enquiry::STATUS_PENDING,
                'snapshot' => $snapshot,
            ]);

            // Create thread
            $thread = $this->threadService->getOrCreateThread($enquiry->id);

            DB::commit();

            Log::info('Enquiry created', [
                'enquiry_id' => $enquiry->id,
                'customer_id' => $user->id,
                'hoarding_id' => $request->hoarding_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Enquiry created successfully',
                'data' => $enquiry->load(['hoarding', 'thread']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create enquiry', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create enquiry: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customer enquiries
     */
    public function getCustomerEnquiries(Request $request)
    {
        try {
            $user = Auth::user();
            
            $query = Enquiry::with(['hoarding', 'thread', 'offers'])
                ->where('customer_id', $user->id);

            if ($request->status) {
                $query->where('status', $request->status);
            }

            if ($request->hoarding_id) {
                $query->where('hoarding_id', $request->hoarding_id);
            }

            $enquiries = $query->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 15);

            return response()->json([
                'success' => true,
                'data' => $enquiries,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get customer enquiries', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load enquiries'
            ], 500);
        }
    }

    /**
     * Get vendor enquiries
     */
    public function getVendorEnquiries(Request $request)
    {
        try {
            $user = Auth::user();
            
            $query = Enquiry::with(['hoarding', 'customer', 'thread', 'offers'])
                ->whereHas('hoarding', function ($q) use ($user) {
                    $q->where('vendor_id', $user->id);
                });

            if ($request->status) {
                $query->where('status', $request->status);
            }

            $enquiries = $query->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 15);

            return response()->json([
                'success' => true,
                'data' => $enquiries,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get vendor enquiries', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load enquiries'
            ], 500);
        }
    }

    /**
     * Get single enquiry details
     */
    public function show(int $id)
    {
        try {
            $user = Auth::user();
            
            $enquiry = Enquiry::with([
                'hoarding',
                'customer',
                'thread',
                'offers.vendor',
                'offers.quotations',
            ])->findOrFail($id);

            // Authorization check
            if ($enquiry->customer_id !== $user->id && 
                $enquiry->hoarding->vendor_id !== $user->id && 
                $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $enquiry,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get enquiry details', [
                'enquiry_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load enquiry'
            ], 500);
        }
    }

    /**
     * Cancel enquiry
     */
    public function cancel(int $id)
    {
        try {
            $user = Auth::user();
            
            $enquiry = Enquiry::where('id', $id)
                ->where('customer_id', $user->id)
                ->firstOrFail();

            if ($enquiry->status !== Enquiry::STATUS_PENDING) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending enquiries can be cancelled'
                ], 400);
            }

            $enquiry->update(['status' => Enquiry::STATUS_CANCELLED]);

            Log::info('Enquiry cancelled', [
                'enquiry_id' => $id,
                'customer_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Enquiry cancelled successfully',
                'data' => $enquiry,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to cancel enquiry', [
                'enquiry_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel enquiry'
            ], 500);
        }
    }

    /**
     * Create enquiry snapshot
     */
    protected function createSnapshot(Hoarding $hoarding, Request $request): array
    {
        $startDate = Carbon::parse($request->preferred_start_date);
        $endDate = Carbon::parse($request->preferred_end_date);

        return [
            'hoarding' => [
                'id' => $hoarding->id,
                'name' => $hoarding->name,
                'location' => $hoarding->location,
                'city' => $hoarding->city,
                'state' => $hoarding->state,
                'type' => $hoarding->type,
                'size' => $hoarding->size,
                'price_per_month' => $hoarding->price_per_month,
                'price_per_day' => $hoarding->price_per_day ?? null,
            ],
            'vendor' => [
                'id' => $hoarding->vendor_id,
                'name' => $hoarding->vendor->name ?? 'Unknown',
                'email' => $hoarding->vendor->email ?? null,
                'phone' => $hoarding->vendor->phone ?? null,
            ],
            'enquiry_details' => [
                'preferred_start_date' => $startDate->format('Y-m-d'),
                'preferred_end_date' => $endDate->format('Y-m-d'),
                'duration_days' => $startDate->diffInDays($endDate),
                'duration_type' => $request->duration_type ?? Enquiry::DURATION_MONTHS,
            ],
            'captured_at' => now()->toDateTimeString(),
        ];
    }
}
