<?php

namespace Modules\Threads\Services;

use Modules\Threads\Models\Thread;
use Modules\Threads\Models\ThreadMessage;
use Modules\Enquiries\Models\Enquiry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ThreadService
{
    /**
     * Get or create thread for enquiry
     */
    public function getOrCreateThread(int $enquiryId): Thread
    {
        $enquiry = Enquiry::with(['hoarding.vendor'])->findOrFail($enquiryId);
        
        $thread = Thread::where('enquiry_id', $enquiryId)->first();
        
        if (!$thread) {
            $isMultiVendor = $this->isMultiVendorEnquiry($enquiry);
            
            $thread = Thread::create([
                'enquiry_id' => $enquiryId,
                'customer_id' => $enquiry->customer_id,
                'vendor_id' => $isMultiVendor ? null : $enquiry->hoarding->vendor_id,
                'is_multi_vendor' => $isMultiVendor,
                'status' => Thread::STATUS_ACTIVE,
                'last_message_at' => now(),
            ]);

            Log::info('Thread created for enquiry', [
                'thread_id' => $thread->id,
                'enquiry_id' => $enquiryId,
                'is_multi_vendor' => $isMultiVendor,
            ]);
        }

        return $thread;
    }

    /**
     * Send text message in thread
     */
    public function sendMessage(int $threadId, array $data): ThreadMessage
    {
        DB::beginTransaction();
        try {
            $thread = Thread::findOrFail($threadId);
            
            // Handle file attachments if present
            $attachments = [];
            if (!empty($data['files'])) {
                $attachments = $this->uploadAttachments($threadId, $data['files']);
            }

            // Create message
            $message = ThreadMessage::create([
                'thread_id' => $threadId,
                'sender_id' => $data['sender_id'],
                'sender_type' => $data['sender_type'],
                'message_type' => ThreadMessage::TYPE_TEXT,
                'message' => $data['message'],
                'attachments' => $attachments,
                'is_read_customer' => $data['sender_type'] === ThreadMessage::SENDER_CUSTOMER,
                'is_read_vendor' => $data['sender_type'] === ThreadMessage::SENDER_VENDOR,
            ]);

            // Update thread
            $thread->update(['last_message_at' => now()]);
            
            // Increment unread count for recipient
            if ($data['sender_type'] === ThreadMessage::SENDER_CUSTOMER) {
                $thread->incrementUnread('vendor');
            } else {
                $thread->incrementUnread('customer');
            }

            DB::commit();

            Log::info('Message sent in thread', [
                'thread_id' => $threadId,
                'message_id' => $message->id,
                'sender_type' => $data['sender_type'],
            ]);

            return $message->load('sender');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to send message', [
                'thread_id' => $threadId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get messages for thread
     */
    public function getThreadMessages(int $threadId, array $filters = [])
    {
        $query = ThreadMessage::with(['sender', 'offer', 'quotation'])
            ->where('thread_id', $threadId);

        if (isset($filters['message_type'])) {
            $query->where('message_type', $filters['message_type']);
        }

        if (isset($filters['after_message_id'])) {
            $query->where('id', '>', $filters['after_message_id']);
        }

        return $query->orderBy('created_at', 'asc')->get();
    }

    /**
     * Get threads for customer
     */
    public function getCustomerThreads(int $customerId, array $filters = [])
    {
        $query = Thread::with(['enquiry.hoarding', 'vendor', 'latestMessage'])
            ->where('customer_id', $customerId);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['has_unread']) && $filters['has_unread']) {
            $query->where('unread_count_customer', '>', 0);
        }

        return $query->orderBy('last_message_at', 'desc')->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get threads for vendor
     */
    public function getVendorThreads(int $vendorId, array $filters = [])
    {
        $query = Thread::with(['enquiry.hoarding', 'customer', 'latestMessage'])
            ->where('vendor_id', $vendorId);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['has_unread']) && $filters['has_unread']) {
            $query->where('unread_count_vendor', '>', 0);
        }

        return $query->orderBy('last_message_at', 'desc')->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(int $threadId, int $userId, string $userType): void
    {
        DB::beginTransaction();
        try {
            $thread = Thread::findOrFail($threadId);
            
            // Verify authorization
            if ($userType === 'customer' && $thread->customer_id !== $userId) {
                throw new \Exception('Unauthorized: Not your thread');
            }
            if ($userType === 'vendor' && $thread->vendor_id !== $userId) {
                throw new \Exception('Unauthorized: Not your thread');
            }

            // Mark all unread messages as read
            $column = $userType === 'customer' ? 'is_read_customer' : 'is_read_vendor';
            ThreadMessage::where('thread_id', $threadId)
                ->where($column, false)
                ->update([
                    $column => true,
                    'read_at' => now(),
                ]);

            // Reset unread count
            $thread->resetUnread($userType);

            DB::commit();

            Log::info('Messages marked as read', [
                'thread_id' => $threadId,
                'user_id' => $userId,
                'user_type' => $userType,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to mark messages as read', [
                'thread_id' => $threadId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Close thread
     */
    public function closeThread(int $threadId, int $userId, string $userType): Thread
    {
        $thread = Thread::findOrFail($threadId);
        
        // Authorization check
        if ($userType === 'customer' && $thread->customer_id !== $userId) {
            throw new \Exception('Unauthorized');
        }
        if ($userType === 'vendor' && $thread->vendor_id !== $userId) {
            throw new \Exception('Unauthorized');
        }

        $thread->update(['status' => Thread::STATUS_CLOSED]);

        // Post system message
        ThreadMessage::create([
            'thread_id' => $threadId,
            'sender_id' => $userId,
            'sender_type' => $userType === 'customer' ? ThreadMessage::SENDER_CUSTOMER : ThreadMessage::SENDER_VENDOR,
            'message_type' => ThreadMessage::TYPE_SYSTEM,
            'message' => 'Thread closed',
            'is_read_customer' => true,
            'is_read_vendor' => true,
        ]);

        Log::info('Thread closed', [
            'thread_id' => $threadId,
            'closed_by' => $userId,
            'user_type' => $userType,
        ]);

        return $thread->fresh();
    }

    /**
     * Reopen thread
     */
    public function reopenThread(int $threadId): Thread
    {
        $thread = Thread::findOrFail($threadId);
        $thread->update(['status' => Thread::STATUS_ACTIVE]);

        ThreadMessage::create([
            'thread_id' => $threadId,
            'sender_id' => $thread->customer_id,
            'sender_type' => ThreadMessage::SENDER_CUSTOMER,
            'message_type' => ThreadMessage::TYPE_SYSTEM,
            'message' => 'Thread reopened',
            'is_read_customer' => true,
            'is_read_vendor' => false,
        ]);

        Log::info('Thread reopened', ['thread_id' => $threadId]);

        return $thread->fresh();
    }

    /**
     * Upload message attachments
     */
    protected function uploadAttachments(int $threadId, array $files): array
    {
        $attachments = [];
        
        foreach ($files as $file) {
            $path = $file->store("thread_attachments/{$threadId}", 'public');
            
            $attachments[] = [
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'uploaded_at' => now()->toDateTimeString(),
            ];
        }

        return $attachments;
    }

    /**
     * Check if multi-vendor enquiry
     */
    protected function isMultiVendorEnquiry(Enquiry $enquiry): bool
    {
        // For now, single vendor per hoarding
        // Can be extended for multi-vendor scenarios
        return false;
    }

    /**
     * Get thread statistics
     */
    public function getThreadStatistics(int $userId, string $userType): array
    {
        $column = $userType === 'customer' ? 'customer_id' : 'vendor_id';
        $unreadColumn = $userType === 'customer' ? 'unread_count_customer' : 'unread_count_vendor';

        return [
            'total' => Thread::where($column, $userId)->count(),
            'active' => Thread::where($column, $userId)->active()->count(),
            'unread' => Thread::where($column, $userId)->where($unreadColumn, '>', 0)->count(),
        ];
    }

    /**
     * Search threads
     */
    public function searchThreads(int $userId, string $userType, string $searchTerm)
    {
        $column = $userType === 'customer' ? 'customer_id' : 'vendor_id';
        
        return Thread::with(['enquiry.hoarding', 'customer', 'vendor', 'latestMessage'])
            ->where($column, $userId)
            ->whereHas('enquiry.hoarding', function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('location', 'like', "%{$searchTerm}%");
            })
            ->orderBy('last_message_at', 'desc')
            ->paginate(15);
    }
}
