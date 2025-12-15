<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingPipelineService
{
    /**
     * Pipeline stages configuration
     */
    const STAGES = [
        'new_enquiry' => [
            'label' => 'New Enquiry',
            'icon' => 'fa-envelope',
            'color' => 'info',
            'order' => 1,
        ],
        'offer_sent' => [
            'label' => 'Offer Sent',
            'icon' => 'fa-paper-plane',
            'color' => 'primary',
            'order' => 2,
        ],
        'quotation_sent' => [
            'label' => 'Quotation Sent',
            'icon' => 'fa-file-invoice',
            'color' => 'warning',
            'order' => 3,
        ],
        'in_payment' => [
            'label' => 'In Payment',
            'icon' => 'fa-credit-card',
            'color' => 'purple',
            'order' => 4,
        ],
        'booked' => [
            'label' => 'Booked',
            'icon' => 'fa-check-circle',
            'color' => 'success',
            'order' => 5,
        ],
        'designing' => [
            'label' => 'Designing',
            'icon' => 'fa-pencil-ruler',
            'color' => 'teal',
            'order' => 6,
        ],
        'printing' => [
            'label' => 'Printing',
            'icon' => 'fa-print',
            'color' => 'cyan',
            'order' => 7,
        ],
        'mounting' => [
            'label' => 'Mounting',
            'icon' => 'fa-tools',
            'color' => 'orange',
            'order' => 8,
        ],
        'live' => [
            'label' => 'Live',
            'icon' => 'fa-broadcast-tower',
            'color' => 'success',
            'order' => 9,
        ],
        'survey' => [
            'label' => 'Survey',
            'icon' => 'fa-clipboard-check',
            'color' => 'indigo',
            'order' => 10,
            'optional' => true,
        ],
        'completed' => [
            'label' => 'Completed',
            'icon' => 'fa-flag-checkered',
            'color' => 'secondary',
            'order' => 11,
        ],
    ];

    /**
     * Get all pipeline data for vendor
     */
    public function getVendorPipeline(User $vendor, array $filters = []): array
    {
        $stages = [];
        
        foreach (self::STAGES as $stageKey => $stageConfig) {
            $bookings = $this->getBookingsForStage($vendor, $stageKey, $filters);
            
            $stages[$stageKey] = [
                'key' => $stageKey,
                'label' => $stageConfig['label'],
                'icon' => $stageConfig['icon'],
                'color' => $stageConfig['color'],
                'order' => $stageConfig['order'],
                'optional' => $stageConfig['optional'] ?? false,
                'count' => $bookings->count(),
                'total_value' => $bookings->sum('total_amount'),
                'bookings' => $bookings->map(fn($b) => $this->formatBookingCard($b)),
            ];
        }
        
        return [
            'stages' => $stages,
            'summary' => $this->getPipelineSummary($vendor, $filters),
        ];
    }

    /**
     * Get bookings for specific stage
     */
    protected function getBookingsForStage(User $vendor, string $stage, array $filters = [])
    {
        $query = Booking::where('vendor_id', $vendor->id)
            ->with(['customer', 'hoarding', 'timeline.events' => function($q) {
                $q->latest()->limit(1);
            }]);

        // Apply stage-specific filters
        switch ($stage) {
            case 'new_enquiry':
                // Has enquiry, no offer yet
                $query->whereHas('quotation.offer.enquiry')
                    ->whereDoesntHave('quotation.offer', function($q) {
                        $q->where('status', 'sent');
                    })
                    ->whereIn('status', ['draft', 'pending']);
                break;

            case 'offer_sent':
                // Offer sent, quotation not created
                $query->whereHas('quotation.offer', function($q) {
                    $q->where('status', 'sent');
                })
                ->whereDoesntHave('quotation', function($q) {
                    $q->where('status', 'sent');
                })
                ->where('status', 'pending');
                break;

            case 'quotation_sent':
                // Quotation sent, payment not initiated
                $query->whereHas('quotation', function($q) {
                    $q->where('status', 'sent');
                })
                ->whereNotIn('status', ['payment_hold', 'payment_settled', 'confirmed']);
                break;

            case 'in_payment':
                // Payment initiated but not settled
                $query->where('status', 'payment_hold');
                break;

            case 'booked':
                // Payment settled, designing not started
                $query->where('status', 'payment_settled')
                    ->whereDoesntHave('timeline.events', function($q) {
                        $q->where('event_type', 'designing')
                          ->where('status', 'in_progress');
                    });
                break;

            case 'designing':
                // Designing stage active
                $query->whereHas('timeline.events', function($q) {
                    $q->where('event_type', 'designing')
                      ->whereIn('status', ['pending', 'in_progress']);
                });
                break;

            case 'printing':
                // Printing stage active
                $query->whereHas('timeline.events', function($q) {
                    $q->where('event_type', 'printing')
                      ->whereIn('status', ['pending', 'in_progress']);
                });
                break;

            case 'mounting':
                // Mounting stage active
                $query->whereHas('timeline.events', function($q) {
                    $q->where('event_type', 'mounting')
                      ->whereIn('status', ['pending', 'in_progress']);
                });
                break;

            case 'live':
                // Campaign is live (between start and end date)
                $query->where('status', 'confirmed')
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->whereDoesntHave('timeline.events', function($q) {
                        $q->where('event_type', 'survey');
                    });
                break;

            case 'survey':
                // Survey in progress
                $query->whereHas('timeline.events', function($q) {
                    $q->where('event_type', 'survey')
                      ->whereIn('status', ['pending', 'in_progress']);
                });
                break;

            case 'completed':
                // Campaign completed
                $query->where('status', 'confirmed')
                    ->where('end_date', '<', now())
                    ->whereHas('timeline.events', function($q) {
                        $q->where('event_type', 'campaign_completed')
                          ->where('status', 'completed');
                    });
                break;
        }

        // Apply common filters
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('booking_id', 'like', "%{$filters['search']}%")
                  ->orWhereHas('customer', function($q2) use ($filters) {
                      $q2->where('name', 'like', "%{$filters['search']}%");
                  })
                  ->orWhereHas('hoarding', function($q2) use ($filters) {
                      $q2->where('title', 'like', "%{$filters['search']}%");
                  });
            });
        }

        if (!empty($filters['hoarding_id'])) {
            $query->where('hoarding_id', $filters['hoarding_id']);
        }

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (!empty($filters['priority'])) {
            // High priority: starting soon or high value
            if ($filters['priority'] === 'high') {
                $query->where(function($q) {
                    $q->where('start_date', '<=', now()->addDays(7))
                      ->orWhere('total_amount', '>=', 100000);
                });
            }
        }

        return $query->orderBy('created_at', 'desc')
            ->limit(50) // Limit per stage for performance
            ->get();
    }

    /**
     * Format booking for Kanban card display
     */
    protected function formatBookingCard(Booking $booking): array
    {
        $daysUntilStart = $booking->start_date ? now()->diffInDays($booking->start_date, false) : null;
        $daysUntilEnd = $booking->end_date ? now()->diffInDays($booking->end_date, false) : null;
        
        return [
            'id' => $booking->id,
            'booking_id' => $booking->booking_id,
            'customer_name' => $booking->customer->name ?? 'N/A',
            'customer_avatar' => $booking->customer->avatar_url ?? null,
            'hoarding_title' => $booking->hoarding->title ?? 'N/A',
            'hoarding_location' => $booking->hoarding->location ?? 'N/A',
            'hoarding_city' => $booking->hoarding->city ?? 'N/A',
            'hoarding_image' => $booking->hoarding->image_url ?? null,
            'total_amount' => $booking->total_amount,
            'total_amount_formatted' => '₹' . number_format($booking->total_amount),
            'start_date' => $booking->start_date?->format('M d, Y'),
            'end_date' => $booking->end_date?->format('M d, Y'),
            'duration_days' => $booking->start_date && $booking->end_date 
                ? $booking->start_date->diffInDays($booking->end_date) 
                : null,
            'days_until_start' => $daysUntilStart,
            'days_until_end' => $daysUntilEnd,
            'is_urgent' => $daysUntilStart !== null && $daysUntilStart >= 0 && $daysUntilStart <= 7,
            'is_high_value' => $booking->total_amount >= 100000,
            'status' => $booking->status,
            'status_label' => $this->getStatusLabel($booking->status),
            'latest_update' => $booking->timeline?->events?->first()?->created_at?->diffForHumans(),
            'latest_update_text' => $booking->timeline?->events?->first()?->title ?? null,
            'payment_status' => $booking->payment_status ?? 'pending',
            'created_at' => $booking->created_at->format('M d, Y'),
        ];
    }

    /**
     * Get pipeline summary statistics
     */
    protected function getPipelineSummary(User $vendor, array $filters = []): array
    {
        $query = Booking::where('vendor_id', $vendor->id);

        // Apply filters to summary as well
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('booking_id', 'like', "%{$filters['search']}%")
                  ->orWhereHas('customer', function($q2) use ($filters) {
                      $q2->where('name', 'like', "%{$filters['search']}%");
                  });
            });
        }

        $totalBookings = (clone $query)->count();
        $totalValue = (clone $query)->sum('total_amount');
        $activeBookings = (clone $query)->whereIn('status', ['payment_hold', 'payment_settled', 'confirmed'])
            ->where('end_date', '>=', now())
            ->count();
        $urgentBookings = (clone $query)->where('start_date', '<=', now()->addDays(7))
            ->where('start_date', '>=', now())
            ->count();

        return [
            'total_bookings' => $totalBookings,
            'total_value' => $totalValue,
            'total_value_formatted' => '₹' . number_format($totalValue),
            'active_bookings' => $activeBookings,
            'urgent_bookings' => $urgentBookings,
            'conversion_rate' => $this->getConversionRate($vendor),
        ];
    }

    /**
     * Move booking to different stage
     */
    public function moveBooking(Booking $booking, string $fromStage, string $toStage, User $user): array
    {
        DB::beginTransaction();
        try {
            // Validate stage transition
            if (!$this->isValidTransition($fromStage, $toStage)) {
                throw new \Exception("Invalid stage transition from {$fromStage} to {$toStage}");
            }

            // Update booking based on target stage
            $this->updateBookingForStage($booking, $toStage, $user);

            // Create timeline event for stage change
            if ($booking->timeline) {
                $booking->timeline->events()->create([
                    'event_type' => 'stage_change',
                    'title' => "Moved to " . self::STAGES[$toStage]['label'],
                    'description' => "Booking moved from {$fromStage} to {$toStage} stage",
                    'status' => 'completed',
                    'category' => 'booking',
                    'triggered_by' => $user->id,
                    'completed_at' => now(),
                ]);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Booking moved successfully',
                'booking' => $this->formatBookingCard($booking->fresh()),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validate if stage transition is allowed
     */
    protected function isValidTransition(string $from, string $to): bool
    {
        $fromOrder = self::STAGES[$from]['order'] ?? 0;
        $toOrder = self::STAGES[$to]['order'] ?? 0;

        // Can't skip more than 2 stages forward
        if ($toOrder > $fromOrder + 2) {
            return false;
        }

        // Can always move backward
        if ($toOrder < $fromOrder) {
            return true;
        }

        // Special cases
        if ($to === 'survey' && !in_array($from, ['live', 'mounting'])) {
            return false; // Survey only after mounting or during live
        }

        return true;
    }

    /**
     * Update booking status/data for new stage
     */
    protected function updateBookingForStage(Booking $booking, string $stage, User $user): void
    {
        switch ($stage) {
            case 'in_payment':
                $booking->update(['status' => 'payment_hold']);
                break;

            case 'booked':
                $booking->update(['status' => 'payment_settled']);
                break;

            case 'designing':
            case 'printing':
            case 'mounting':
                $booking->update(['status' => 'confirmed']);
                // Update timeline event
                if ($booking->timeline) {
                    $event = $booking->timeline->events()
                        ->where('event_type', $stage)
                        ->first();
                    
                    if ($event) {
                        $event->update([
                            'status' => 'in_progress',
                            'started_at' => now(),
                        ]);
                    }
                }
                break;

            case 'live':
                $booking->update(['status' => 'confirmed']);
                if ($booking->timeline) {
                    $booking->timeline->events()
                        ->where('event_type', 'campaign_start')
                        ->update(['status' => 'completed', 'completed_at' => now()]);
                }
                break;

            case 'completed':
                $booking->update(['status' => 'confirmed']);
                if ($booking->timeline) {
                    $booking->timeline->events()
                        ->where('event_type', 'campaign_completed')
                        ->update(['status' => 'completed', 'completed_at' => now()]);
                }
                break;
        }
    }

    /**
     * Get status label
     */
    protected function getStatusLabel(string $status): string
    {
        $labels = [
            'draft' => 'Draft',
            'pending' => 'Pending',
            'payment_hold' => 'Payment Hold',
            'payment_settled' => 'Payment Settled',
            'confirmed' => 'Confirmed',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
        ];

        return $labels[$status] ?? ucfirst($status);
    }

    /**
     * Calculate conversion rate
     */
    protected function getConversionRate(User $vendor): float
    {
        $totalEnquiries = Booking::where('vendor_id', $vendor->id)
            ->whereHas('quotation.offer.enquiry')
            ->count();

        $converted = Booking::where('vendor_id', $vendor->id)
            ->whereIn('status', ['payment_settled', 'confirmed'])
            ->count();

        return $totalEnquiries > 0 ? round(($converted / $totalEnquiries) * 100, 1) : 0;
    }

    /**
     * Get stage configuration
     */
    public static function getStageConfig(string $stage): ?array
    {
        return self::STAGES[$stage] ?? null;
    }

    /**
     * Get all stages
     */
    public static function getAllStages(): array
    {
        return self::STAGES;
    }
}
