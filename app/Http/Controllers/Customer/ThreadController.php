<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Thread;
use App\Models\ThreadMessage;
use App\Models\Enquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ThreadController extends Controller
{
    /**
     * Display inbox with all threads
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $threads = Thread::where('customer_id', $user->id)
            ->with(['enquiry.hoarding', 'vendor', 'latestMessage.sender'])
            ->orderBy('last_message_at', 'desc')
            ->paginate(20);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'threads' => $threads->map(function($thread) use ($user) {
                    return [
                        'id' => $thread->id,
                        'title' => $thread->title,
                        'enquiry_id' => $thread->enquiry_id,
                        'vendor' => $thread->vendor ? [
                            'id' => $thread->vendor->id,
                            'name' => $thread->vendor->name,
                            'company_name' => $thread->vendor->company_name,
                        ] : null,
                        'status' => $thread->status,
                        'last_message' => $thread->latestMessage ? [
                            'message' => $thread->latestMessage->message,
                            'sender' => $thread->latestMessage->sender->name,
                            'created_at' => $thread->latestMessage->created_at->diffForHumans(),
                        ] : null,
                        'unread_count' => $thread->unread_count_customer,
                        'last_message_at' => $thread->last_message_at?->diffForHumans(),
                    ];
                }),
                'pagination' => [
                    'current_page' => $threads->currentPage(),
                    'last_page' => $threads->lastPage(),
                    'total' => $threads->total(),
                ]
            ]);
        }

        return view('customer.threads.index', compact('threads'));
    }

    /**
     * Show specific thread with messages
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        $thread = Thread::where('customer_id', $user->id)
            ->with(['enquiry.hoarding', 'vendor', 'messages.sender'])
            ->findOrFail($id);

        // Mark messages as read
        $thread->markAsReadFor($user->id);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'thread' => [
                    'id' => $thread->id,
                    'title' => $thread->title,
                    'enquiry_id' => $thread->enquiry_id,
                    'status' => $thread->status,
                    'vendor' => $thread->vendor ? [
                        'id' => $thread->vendor->id,
                        'name' => $thread->vendor->name,
                        'company_name' => $thread->vendor->company_name,
                        'phone' => $thread->vendor->phone,
                    ] : null,
                    'enquiry' => $thread->enquiry ? [
                        'id' => $thread->enquiry->id,
                        'hoarding' => $thread->enquiry->hoarding ? [
                            'title' => $thread->enquiry->hoarding->title,
                            'location' => $thread->enquiry->hoarding->location,
                        ] : null,
                    ] : null,
                ],
                'messages' => $thread->messages->map(function($msg) use ($user) {
                    return [
                        'id' => $msg->id,
                        'message' => $msg->message,
                        'message_type' => $msg->message_type,
                        'sender' => [
                            'id' => $msg->sender->id,
                            'name' => $msg->sender->name,
                            'is_me' => $msg->sender_id === $user->id,
                        ],
                        'attachments' => $msg->getAttachmentUrls(),
                        'offer_id' => $msg->offer_id,
                        'quotation_id' => $msg->quotation_id,
                        'is_read' => $msg->isReadBy($user->id),
                        'created_at' => $msg->created_at->format('M d, Y h:i A'),
                        'formatted_time' => $msg->formatted_time,
                    ];
                })
            ]);
        }

        return view('customer.threads.show', compact('thread'));
    }

    /**
     * Send a message in the thread
     */
    public function sendMessage(Request $request, $id)
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'message' => 'required_without:attachments|string|max:5000',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240', // 10MB max per file
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $thread = Thread::where('customer_id', $user->id)->findOrFail($id);

        // Handle file attachments
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('threads/' . $thread->id, 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ];
            }
        }

        $message = ThreadMessage::create([
            'thread_id' => $thread->id,
            'sender_id' => $user->id,
            'sender_type' => 'customer',
            'message_type' => 'text',
            'message' => $request->message,
            'attachments' => !empty($attachments) ? $attachments : null,
        ]);

        // TODO: Send notification to vendor
        // event(new NewThreadMessage($thread, $message));

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => [
                'id' => $message->id,
                'message' => $message->message,
                'sender' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ],
                'attachments' => $message->getAttachmentUrls(),
                'created_at' => $message->created_at->format('M d, Y h:i A'),
                'formatted_time' => $message->formatted_time,
            ]
        ]);
    }

    /**
     * Mark thread as read
     */
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();
        $thread = Thread::where('customer_id', $user->id)->findOrFail($id);
        
        $thread->markAsReadFor($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Thread marked as read'
        ]);
    }

    /**
     * Archive thread
     */
    public function archive(Request $request, $id)
    {
        $user = $request->user();
        $thread = Thread::where('customer_id', $user->id)->findOrFail($id);
        
        $thread->update(['status' => 'archived']);

        return response()->json([
            'success' => true,
            'message' => 'Thread archived successfully'
        ]);
    }

    /**
     * Get unread count for user
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();
        
        $count = Thread::where('customer_id', $user->id)
            ->where('unread_count_customer', '>', 0)
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $count
        ]);
    }
}
