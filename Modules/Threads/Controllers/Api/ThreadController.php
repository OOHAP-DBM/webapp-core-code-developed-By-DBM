<?php

namespace Modules\Threads\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Threads\Services\ThreadService;
use Modules\Offers\Services\OfferWorkflowService;
use Modules\Quotations\Services\QuotationWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ThreadController extends Controller
{
    protected ThreadService $threadService;
    protected OfferWorkflowService $offerService;
    protected QuotationWorkflowService $quotationService;

    public function __construct(
        ThreadService $threadService,
        OfferWorkflowService $offerService,
        QuotationWorkflowService $quotationService
    ) {
        $this->threadService = $threadService;
        $this->offerService = $offerService;
        $this->quotationService = $quotationService;
    }

    /**
     * Get thread with messages
     */
    public function show(Request $request, int $id)
    {
        try {
            $thread = \Modules\Threads\Models\Thread::with([
                'enquiry.hoarding',
                'customer',
                'vendor',
            ])->findOrFail($id);

            $user = Auth::user();
            
            // Authorization check
            if ($thread->customer_id !== $user->id && $thread->vendor_id !== $user->id && $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to thread'
                ], 403);
            }

            // Get messages
            $messages = $this->threadService->getThreadMessages($id);

            // Mark as read
            $userType = $thread->customer_id === $user->id ? 'customer' : 'vendor';
            $this->threadService->markAsRead($id, $user->id, $userType);

            return response()->json([
                'success' => true,
                'data' => [
                    'thread' => $thread,
                    'messages' => $messages,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get thread', [
                'thread_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load thread: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send text message
     */
    public function sendMessage(Request $request, int $id)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
            'files.*' => 'file|max:10240', // 10MB per file
        ]);

        try {
            $thread = \Modules\Threads\Models\Thread::findOrFail($id);
            $user = Auth::user();

            // Authorization check
            if ($thread->customer_id !== $user->id && $thread->vendor_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Determine sender type
            $senderType = $thread->customer_id === $user->id 
                ? \Modules\Threads\Models\ThreadMessage::SENDER_CUSTOMER 
                : \Modules\Threads\Models\ThreadMessage::SENDER_VENDOR;

            $message = $this->threadService->sendMessage($id, [
                'sender_id' => $user->id,
                'sender_type' => $senderType,
                'message' => $request->message,
                'files' => $request->file('files') ?? [],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => $message,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send message', [
                'thread_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create and send offer inline in thread (vendor action)
     */
    public function createOfferInline(Request $request, int $threadId)
    {
        $request->validate([
            'price' => 'required|numeric|min:0',
            'price_type' => 'required|in:total,monthly,weekly,daily',
            'description' => 'nullable|string|max:2000',
            'valid_days' => 'nullable|integer|min:1|max:90',
            'message' => 'nullable|string|max:1000',
        ]);

        try {
            $thread = \Modules\Threads\Models\Thread::with('enquiry')->findOrFail($threadId);
            $user = Auth::user();

            // Authorization: Only vendor can create offer
            if ($thread->vendor_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only the vendor can create offers'
                ], 403);
            }

            // Create offer
            $offer = $this->offerService->createOfferVersion([
                'enquiry_id' => $thread->enquiry_id,
                'vendor_id' => $user->id,
                'price' => $request->price,
                'price_type' => $request->price_type,
                'description' => $request->description,
                'valid_days' => $request->valid_days,
                'status' => \Modules\Offers\Models\Offer::STATUS_DRAFT,
            ]);

            // Send offer via thread
            $this->offerService->sendOfferViaThread($offer->id, $request->message);

            return response()->json([
                'success' => true,
                'message' => 'Offer created and sent successfully',
                'data' => $offer->load('enquiry'),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create offer inline', [
                'thread_id' => $threadId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create offer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Accept offer inline in thread (customer action)
     */
    public function acceptOfferInline(Request $request, int $threadId, int $offerId)
    {
        try {
            $thread = \Modules\Threads\Models\Thread::findOrFail($threadId);
            $user = Auth::user();

            // Authorization: Only customer can accept
            if ($thread->customer_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only the customer can accept offers'
                ], 403);
            }

            $offer = $this->offerService->acceptOfferAndFreeze($offerId, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Offer accepted successfully',
                'data' => $offer->load('enquiry'),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to accept offer inline', [
                'thread_id' => $threadId,
                'offer_id' => $offerId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to accept offer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject offer inline in thread (customer action)
     */
    public function rejectOfferInline(Request $request, int $threadId, int $offerId)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $thread = \Modules\Threads\Models\Thread::findOrFail($threadId);
            $user = Auth::user();

            // Authorization: Only customer can reject
            if ($thread->customer_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only the customer can reject offers'
                ], 403);
            }

            $offer = $this->offerService->rejectOfferViaThread($offerId, $user->id, $request->reason);

            return response()->json([
                'success' => true,
                'message' => 'Offer rejected',
                'data' => $offer,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to reject offer inline', [
                'thread_id' => $threadId,
                'offer_id' => $offerId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject offer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create and send quotation inline in thread (vendor action)
     */
    public function createQuotationInline(Request $request, int $threadId)
    {
        $request->validate([
            'offer_id' => 'required|exists:offers,id',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.rate' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
            'message' => 'nullable|string|max:1000',
        ]);

        try {
            $thread = \Modules\Threads\Models\Thread::findOrFail($threadId);
            $user = Auth::user();

            // Authorization: Only vendor can create quotation
            if ($thread->vendor_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only the vendor can create quotations'
                ], 403);
            }

            // Create quotation
            $quotation = $this->quotationService->createQuotationVersion([
                'offer_id' => $request->offer_id,
                'vendor_id' => $user->id,
                'items' => $request->items,
                'tax_rate' => $request->tax_rate ?? 0,
                'discount' => $request->discount ?? 0,
                'notes' => $request->notes,
                'status' => \Modules\Quotations\Models\Quotation::STATUS_DRAFT,
            ]);

            // Send quotation via thread
            $this->quotationService->sendQuotationViaThread($quotation->id, $request->message);

            return response()->json([
                'success' => true,
                'message' => 'Quotation created and sent successfully',
                'data' => $quotation->load(['offer', 'customer', 'vendor']),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create quotation inline', [
                'thread_id' => $threadId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create quotation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve quotation inline in thread (customer action)
     */
    public function approveQuotationInline(Request $request, int $threadId, int $quotationId)
    {
        try {
            $thread = \Modules\Threads\Models\Thread::findOrFail($threadId);
            $user = Auth::user();

            // Authorization: Only customer can approve
            if ($thread->customer_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only the customer can approve quotations'
                ], 403);
            }

            $quotation = $this->quotationService->approveQuotationAndFreeze($quotationId, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Quotation approved successfully! You can now proceed to booking.',
                'data' => $quotation->load(['offer', 'customer', 'vendor']),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to approve quotation inline', [
                'thread_id' => $threadId,
                'quotation_id' => $quotationId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve quotation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject quotation inline in thread (customer action)
     */
    public function rejectQuotationInline(Request $request, int $threadId, int $quotationId)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $thread = \Modules\Threads\Models\Thread::findOrFail($threadId);
            $user = Auth::user();

            // Authorization: Only customer can reject
            if ($thread->customer_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only the customer can reject quotations'
                ], 403);
            }

            $quotation = $this->quotationService->rejectQuotationViaThread($quotationId, $user->id, $request->reason);

            return response()->json([
                'success' => true,
                'message' => 'Quotation rejected',
                'data' => $quotation,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to reject quotation inline', [
                'thread_id' => $threadId,
                'quotation_id' => $quotationId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject quotation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customer threads
     */
    public function getCustomerThreads(Request $request)
    {
        try {
            $user = Auth::user();
            
            $filters = [
                'status' => $request->status,
                'has_unread' => $request->boolean('has_unread'),
                'per_page' => $request->per_page ?? 15,
            ];

            $threads = $this->threadService->getCustomerThreads($user->id, $filters);

            return response()->json([
                'success' => true,
                'data' => $threads,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get customer threads', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load threads'
            ], 500);
        }
    }

    /**
     * Get vendor threads
     */
    public function getVendorThreads(Request $request)
    {
        try {
            $user = Auth::user();
            
            $filters = [
                'status' => $request->status,
                'has_unread' => $request->boolean('has_unread'),
                'per_page' => $request->per_page ?? 15,
            ];

            $threads = $this->threadService->getVendorThreads($user->id, $filters);

            return response()->json([
                'success' => true,
                'data' => $threads,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get vendor threads', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load threads'
            ], 500);
        }
    }
}
