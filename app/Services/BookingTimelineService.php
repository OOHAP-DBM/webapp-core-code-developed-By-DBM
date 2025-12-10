<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingTimelineEvent;
use Carbon\Carbon;

class BookingTimelineService
{
    /**
     * Create a timeline event
     */
    public function createEvent(
        Booking $booking,
        string $eventType,
        string $title,
        array $options = []
    ): BookingTimelineEvent {
        $order = $options['order'] ?? $this->getNextOrder($booking);
        $category = $options['category'] ?? $this->detectCategory($eventType);

        $data = [
            'booking_id' => $booking->id,
            'event_type' => $eventType,
            'event_category' => $category,
            'title' => $title,
            'description' => $options['description'] ?? null,
            'status' => $options['status'] ?? 'pending',
            'reference_id' => $options['reference_id'] ?? null,
            'reference_type' => $options['reference_type'] ?? null,
            'version' => $options['version'] ?? null,
            'user_id' => $options['user_id'] ?? auth()->id(),
            'user_name' => $options['user_name'] ?? auth()->user()?->name,
            'metadata' => $options['metadata'] ?? [],
            'scheduled_at' => $options['scheduled_at'] ?? null,
            'started_at' => $options['started_at'] ?? null,
            'completed_at' => $options['completed_at'] ?? null,
            'order' => $order,
            'icon' => $options['icon'] ?? null,
            'color' => $options['color'] ?? null,
            'notify_customer' => $options['notify_customer'] ?? false,
            'notify_vendor' => $options['notify_vendor'] ?? false,
        ];

        return BookingTimelineEvent::create($data);
    }

    /**
     * Generate full timeline for a booking
     */
    public function generateFullTimeline(Booking $booking): void
    {
        $order = 0;

        // 1. Enquiry
        if ($booking->quotation && $booking->quotation->offer && $booking->quotation->offer->enquiry) {
            $enquiry = $booking->quotation->offer->enquiry;
            $this->createEvent($booking, BookingTimelineEvent::TYPE_ENQUIRY, 'Enquiry Received', [
                'description' => "Customer enquiry for {$booking->hoarding->title}",
                'status' => 'completed',
                'reference_id' => $enquiry->id,
                'reference_type' => 'App\\Models\\Enquiry',
                'completed_at' => $enquiry->created_at,
                'order' => $order++,
                'category' => BookingTimelineEvent::CATEGORY_BOOKING,
            ]);
        }

        // 2. Offer versions
        if ($booking->quotation && $booking->quotation->offer) {
            $offer = $booking->quotation->offer;
            $this->createEvent($booking, BookingTimelineEvent::TYPE_OFFER, 'Offer Created', [
                'description' => "Vendor submitted offer - ₹{$offer->total_price}",
                'status' => 'completed',
                'reference_id' => $offer->id,
                'reference_type' => 'App\\Models\\Offer',
                'version' => 1,
                'completed_at' => $offer->created_at,
                'order' => $order++,
                'category' => BookingTimelineEvent::CATEGORY_BOOKING,
            ]);
        }

        // 3. Quotation versions
        if ($booking->quotation) {
            $quotation = $booking->quotation;
            $this->createEvent($booking, BookingTimelineEvent::TYPE_QUOTATION, 'Quotation Generated', [
                'description' => "Admin generated quotation - ₹{$quotation->total_amount}",
                'status' => 'completed',
                'reference_id' => $quotation->id,
                'reference_type' => 'App\\Models\\Quotation',
                'version' => 1,
                'completed_at' => $quotation->created_at,
                'order' => $order++,
                'category' => BookingTimelineEvent::CATEGORY_BOOKING,
                'notify_customer' => true,
                'notify_vendor' => true,
            ]);
        }

        // 4. Purchase Order (PO) - PROMPT 47
        $this->createEvent($booking, BookingTimelineEvent::TYPE_PO, 'Purchase Order', [
            'description' => "PO generated for booking #{$booking->id}",
            'status' => $booking->confirmed_at ? 'completed' : 'pending',
            'completed_at' => $booking->confirmed_at,
            'scheduled_at' => $booking->created_at,
            'order' => $order++,
            'category' => BookingTimelineEvent::CATEGORY_BOOKING,
            'notify_customer' => true,
            'notify_vendor' => true,
            'notify_admin' => true,
        ]);

        // 5. Payment Hold
        if ($booking->payment_status === 'held' || $booking->payment_status === 'captured') {
            $this->createEvent($booking, BookingTimelineEvent::TYPE_PAYMENT_HOLD, 'Payment Hold', [
                'description' => "Payment of ₹{$booking->total_amount} held",
                'status' => 'completed',
                'completed_at' => $booking->hold_expiry_at ? now() : null,
                'order' => $order++,
                'category' => BookingTimelineEvent::CATEGORY_PAYMENT,
                'notify_customer' => true,
                'notify_admin' => true,
            ]);
        }

        // 6. Payment Settled
        if ($booking->payment_status === 'captured') {
            $this->createEvent($booking, BookingTimelineEvent::TYPE_PAYMENT_SETTLED, 'Payment Settled', [
                'description' => "Payment of ₹{$booking->total_amount} captured",
                'status' => 'completed',
                'completed_at' => now(),
                'order' => $order++,
                'category' => BookingTimelineEvent::CATEGORY_PAYMENT,
                'notify_customer' => true,
                'notify_vendor' => true,
                'notify_admin' => true,
            ]);
        }

        // 7. Designing - PROMPT 47
        $this->createEvent($booking, BookingTimelineEvent::TYPE_DESIGNING, 'Designing', [
            'description' => 'Creative concept and design work',
            'status' => $this->getProductionStatus($booking, 'designing'),
            'scheduled_at' => $booking->start_date->copy()->subDays(12),
            'order' => $order++,
            'category' => BookingTimelineEvent::CATEGORY_PRODUCTION,
            'notify_vendor' => true,
        ]);

        // 8. Graphics Design
        $this->createEvent($booking, BookingTimelineEvent::TYPE_GRAPHICS, 'Graphics Design', [
            'description' => 'Final graphics and artwork preparation',
            'status' => $this->getProductionStatus($booking, 'graphics'),
            'scheduled_at' => $booking->start_date->copy()->subDays(10),
            'order' => $order++,
            'category' => BookingTimelineEvent::CATEGORY_PRODUCTION,
            'notify_vendor' => true,
        ]);

        // 9. Printing
        $this->createEvent($booking, BookingTimelineEvent::TYPE_PRINTING, 'Printing', [
            'description' => 'Print production of campaign materials',
            'status' => $this->getProductionStatus($booking, 'printing'),
            'scheduled_at' => $booking->start_date->copy()->subDays(7),
            'order' => $order++,
            'category' => BookingTimelineEvent::CATEGORY_PRODUCTION,
            'notify_vendor' => true,
        ]);

        // 10. Mounting
        $this->createEvent($booking, BookingTimelineEvent::TYPE_MOUNTING, 'Mounting', [
            'description' => 'Installation of campaign on hoarding',
            'status' => $this->getProductionStatus($booking, 'mounting'),
            'scheduled_at' => $booking->start_date->copy()->subDays(2),
            'order' => $order++,
            'category' => BookingTimelineEvent::CATEGORY_PRODUCTION,
            'notify_vendor' => true,
            'notify_customer' => true,
        ]);

        // 11. Campaign Start
        $this->createEvent($booking, BookingTimelineEvent::TYPE_CAMPAIGN_START, 'Campaign Started', [
            'description' => "Campaign goes live on {$booking->start_date->format('M d, Y')}",
            'status' => now()->gte($booking->start_date) ? 'completed' : 'pending',
            'completed_at' => now()->gte($booking->start_date) ? $booking->start_date : null,
            'scheduled_at' => $booking->start_date,
            'order' => $order++,
            'category' => BookingTimelineEvent::CATEGORY_CAMPAIGN,
            'notify_customer' => true,
            'notify_vendor' => true,
            'notify_admin' => true,
        ]);

        // 12. Survey (Optional) - PROMPT 47
        $this->createEvent($booking, BookingTimelineEvent::TYPE_SURVEY, 'Survey', [
            'description' => 'Optional campaign survey (if requested)',
            'status' => 'pending',
            'scheduled_at' => $booking->start_date->copy()->addDays(ceil($booking->duration_days / 2)),
            'order' => $order++,
            'category' => BookingTimelineEvent::CATEGORY_PRODUCTION,
            'metadata' => ['optional' => true],
            'notify_vendor' => true,
        ]);

        // 13. Proof of Display
        $this->createEvent($booking, BookingTimelineEvent::TYPE_PROOF, 'Proof of Display', [
            'description' => 'Photo verification of campaign display',
            'status' => $booking->pod_approved_at ? 'completed' : 'pending',
            'completed_at' => $booking->pod_approved_at,
            'scheduled_at' => $booking->start_date->copy()->addDay(),
            'order' => $order++,
            'category' => BookingTimelineEvent::CATEGORY_PRODUCTION,
            'notify_customer' => true,
            'notify_admin' => true,
        ]);

        // 14. Campaign Running
        if (now()->gte($booking->start_date) && now()->lte($booking->end_date)) {
            $this->createEvent($booking, BookingTimelineEvent::TYPE_CAMPAIGN_RUNNING, 'Campaign Running', [
                'description' => "Campaign is currently live ({$booking->duration_days} days)",
                'status' => 'in_progress',
                'started_at' => $booking->start_date,
                'order' => $order++,
                'category' => BookingTimelineEvent::CATEGORY_CAMPAIGN,
                'notify_admin' => true,
            ]);
        }

        // 15. Campaign Completed
        $this->createEvent($booking, BookingTimelineEvent::TYPE_CAMPAIGN_COMPLETED, 'Campaign Completed', [
            'description' => "Campaign ends on {$booking->end_date->format('M d, Y')}",
            'status' => now()->gte($booking->end_date) ? 'completed' : 'pending',
            'completed_at' => now()->gte($booking->end_date) ? $booking->end_date : null,
            'scheduled_at' => $booking->end_date,
            'order' => $order++,
            'category' => BookingTimelineEvent::CATEGORY_CAMPAIGN,
            'notify_customer' => true,
            'notify_vendor' => true,
            'notify_admin' => true,
        ]);
    }

    /**
     * Add enquiry event
     */
    public function addEnquiryEvent(Booking $booking, $enquiry): BookingTimelineEvent
    {
        return $this->createEvent($booking, BookingTimelineEvent::TYPE_ENQUIRY, 'Enquiry Received', [
            'description' => "Customer enquiry received",
            'status' => 'completed',
            'reference_id' => $enquiry->id,
            'reference_type' => get_class($enquiry),
            'completed_at' => $enquiry->created_at,
        ]);
    }

    /**
     * Add offer event
     */
    public function addOfferEvent(Booking $booking, $offer, int $version = 1): BookingTimelineEvent
    {
        return $this->createEvent($booking, BookingTimelineEvent::TYPE_OFFER, "Offer v{$version}", [
            'description' => "Vendor offer submitted - ₹{$offer->total_price}",
            'status' => 'completed',
            'reference_id' => $offer->id,
            'reference_type' => get_class($offer),
            'version' => $version,
            'completed_at' => $offer->created_at,
        ]);
    }

    /**
     * Add quotation event
     */
    public function addQuotationEvent(Booking $booking, $quotation, int $version = 1): BookingTimelineEvent
    {
        return $this->createEvent($booking, BookingTimelineEvent::TYPE_QUOTATION, "Quotation v{$version}", [
            'description' => "Quotation generated - ₹{$quotation->total_amount}",
            'status' => 'completed',
            'reference_id' => $quotation->id,
            'reference_type' => get_class($quotation),
            'version' => $version,
            'completed_at' => $quotation->created_at,
        ]);
    }

    /**
     * Add payment hold event
     */
    public function addPaymentHoldEvent(Booking $booking): BookingTimelineEvent
    {
        return $this->createEvent($booking, BookingTimelineEvent::TYPE_PAYMENT_HOLD, 'Payment Hold', [
            'description' => "Payment of ₹{$booking->total_amount} held",
            'status' => 'completed',
            'completed_at' => now(),
            'category' => BookingTimelineEvent::CATEGORY_PAYMENT,
        ]);
    }

    /**
     * Add payment settled event
     */
    public function addPaymentSettledEvent(Booking $booking): BookingTimelineEvent
    {
        return $this->createEvent($booking, BookingTimelineEvent::TYPE_PAYMENT_SETTLED, 'Payment Settled', [
            'description' => "Payment of ₹{$booking->total_amount} captured",
            'status' => 'completed',
            'completed_at' => now(),
            'category' => BookingTimelineEvent::CATEGORY_PAYMENT,
        ]);
    }

    /**
     * Start production event
     */
    public function startProductionEvent(Booking $booking, string $stage): BookingTimelineEvent
    {
        $event = $booking->timelineEvents()->ofType($stage)->first();
        
        if ($event) {
            $event->markAsStarted();
            return $event;
        }

        return $this->createEvent($booking, $stage, ucfirst($stage) . ' Started', [
            'status' => 'in_progress',
            'started_at' => now(),
            'category' => BookingTimelineEvent::CATEGORY_PRODUCTION,
        ]);
    }

    /**
     * Complete production event
     */
    public function completeProductionEvent(Booking $booking, string $stage): BookingTimelineEvent
    {
        $event = $booking->timelineEvents()->ofType($stage)->first();
        
        if ($event) {
            $event->markAsCompleted();
            return $event;
        }

        return $this->createEvent($booking, $stage, ucfirst($stage) . ' Completed', [
            'status' => 'completed',
            'completed_at' => now(),
            'category' => BookingTimelineEvent::CATEGORY_PRODUCTION,
        ]);
    }

    /**
     * Get timeline for booking
     */
    public function getTimeline(Booking $booking)
    {
        return $booking->timelineEvents()->timeline()->get();
    }

    /**
     * Get timeline progress percentage
     */
    public function getProgress(Booking $booking): float
    {
        $events = $booking->timelineEvents;
        
        if ($events->isEmpty()) {
            return 0;
        }

        $completed = $events->where('status', 'completed')->count();
        $total = $events->count();

        return round(($completed / $total) * 100, 2);
    }

    /**
     * Get current stage
     */
    public function getCurrentStage(Booking $booking): ?BookingTimelineEvent
    {
        return $booking->timelineEvents()
                      ->where('status', 'in_progress')
                      ->orWhere(function($q) {
                          $q->where('status', 'pending')
                            ->whereNotNull('scheduled_at')
                            ->where('scheduled_at', '<=', now());
                      })
                      ->timeline()
                      ->first();
    }

    /**
     * Get next upcoming event
     */
    public function getNextEvent(Booking $booking): ?BookingTimelineEvent
    {
        return $booking->timelineEvents()
                      ->where('status', 'pending')
                      ->whereNotNull('scheduled_at')
                      ->where('scheduled_at', '>', now())
                      ->orderBy('scheduled_at')
                      ->first();
    }

    /**
     * Detect category from event type
     */
    protected function detectCategory(string $eventType): string
    {
        return match($eventType) {
            BookingTimelineEvent::TYPE_ENQUIRY,
            BookingTimelineEvent::TYPE_OFFER,
            BookingTimelineEvent::TYPE_QUOTATION,
            BookingTimelineEvent::TYPE_PO => BookingTimelineEvent::CATEGORY_BOOKING,
            
            BookingTimelineEvent::TYPE_PAYMENT_HOLD,
            BookingTimelineEvent::TYPE_PAYMENT_SETTLED => BookingTimelineEvent::CATEGORY_PAYMENT,
            
            BookingTimelineEvent::TYPE_DESIGNING,
            BookingTimelineEvent::TYPE_GRAPHICS,
            BookingTimelineEvent::TYPE_PRINTING,
            BookingTimelineEvent::TYPE_MOUNTING,
            BookingTimelineEvent::TYPE_SURVEY,
            BookingTimelineEvent::TYPE_PROOF => BookingTimelineEvent::CATEGORY_PRODUCTION,
            
            BookingTimelineEvent::TYPE_CAMPAIGN_START,
            BookingTimelineEvent::TYPE_CAMPAIGN_RUNNING,
            BookingTimelineEvent::TYPE_CAMPAIGN_COMPLETED => BookingTimelineEvent::CATEGORY_CAMPAIGN,
            
            default => BookingTimelineEvent::CATEGORY_BOOKING,
        };
    }

    /**
     * Get next order number
     */
    protected function getNextOrder(Booking $booking): int
    {
        $maxOrder = $booking->timelineEvents()->max('order') ?? -1;
        return $maxOrder + 1;
    }

    /**
     * Get production status
     */
    protected function getProductionStatus(Booking $booking, string $stage): string
    {
        // Check if booking has reached this stage
        $now = now();
        $startDate = $booking->start_date;

        return match($stage) {
            'designing' => $now->gte($startDate->copy()->subDays(12)) ? 'in_progress' : 'pending',
            'graphics' => $now->gte($startDate->copy()->subDays(10)) ? 'in_progress' : 'pending',
            'printing' => $now->gte($startDate->copy()->subDays(7)) ? 'in_progress' : 'pending',
            'mounting' => $now->gte($startDate->copy()->subDays(2)) ? 'in_progress' : 'pending',
            default => 'pending',
        };
    }

    /**
     * Update event with user and note (PROMPT 47)
     */
    public function updateEventWithNote(BookingTimelineEvent $event, string $note, $user = null): BookingTimelineEvent
    {
        $user = $user ?? auth()->user();
        
        $metadata = $event->metadata ?? [];
        $metadata['notes'] = $metadata['notes'] ?? [];
        $metadata['notes'][] = [
            'note' => $note,
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'System',
            'user_role' => $user?->role ?? 'system',
            'timestamp' => now()->toIso8601String(),
        ];

        $event->update([
            'metadata' => $metadata,
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'System',
        ]);

        return $event;
    }

    /**
     * Complete stage with user and note (PROMPT 47)
     */
    public function completeStageWithNote(
        Booking $booking, 
        string $eventType, 
        string $note = null, 
        $user = null
    ): BookingTimelineEvent {
        $event = $booking->timelineEvents()->ofType($eventType)->first();
        
        if (!$event) {
            throw new \Exception("Timeline event '{$eventType}' not found for booking #{$booking->id}");
        }

        $event->markAsCompleted();
        
        if ($note) {
            $this->updateEventWithNote($event, $note, $user);
        }

        return $event;
    }

    /**
     * Start stage with user and note (PROMPT 47)
     */
    public function startStageWithNote(
        Booking $booking, 
        string $eventType, 
        string $note = null, 
        $user = null
    ): BookingTimelineEvent {
        $event = $booking->timelineEvents()->ofType($eventType)->first();
        
        if (!$event) {
            throw new \Exception("Timeline event '{$eventType}' not found for booking #{$booking->id}");
        }

        $event->markAsStarted();
        
        if ($note) {
            $this->updateEventWithNote($event, $note, $user);
        }

        return $event;
    }

    /**
     * Rebuild timeline
     */
    public function rebuildTimeline(Booking $booking): void
    {
        // Delete existing events
        $booking->timelineEvents()->delete();
        
        // Generate new timeline
        $this->generateFullTimeline($booking);
    }
}
