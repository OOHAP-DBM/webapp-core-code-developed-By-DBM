<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * PROMPT 110: Campaign Dashboard Service
 * Aggregates campaign data for customer dashboard
 */
class CampaignDashboardService
{
    /**
     * Get customer's campaign overview
     */
    public function getCustomerOverview(User $customer): array
    {
        return [
            'stats' => $this->getStats($customer),
            'active_campaigns' => $this->getActiveCampaigns($customer),
            'upcoming_campaigns' => $this->getUpcomingCampaigns($customer),
            'recent_completed' => $this->getRecentCompletedCampaigns($customer),
            'pending_actions' => $this->getPendingActions($customer),
            'recent_invoices' => $this->getRecentInvoices($customer),
            'recent_updates' => $this->getRecentUpdates($customer),
        ];
    }

    /**
     * Get campaign statistics
     */
    public function getStats(User $customer): array
    {
        $now = Carbon::now();

        return [
            'total_campaigns' => Booking::where('customer_id', $customer->id)->count(),
            'active_campaigns' => Booking::where('customer_id', $customer->id)
                ->whereIn('status', ['confirmed', 'in_progress', 'mounted'])
                ->where('start_date', '<=', $now)
                ->where('end_date', '>=', $now)
                ->count(),
            'upcoming_campaigns' => Booking::where('customer_id', $customer->id)
                ->where('status', 'confirmed')
                ->where('start_date', '>', $now)
                ->count(),
            'completed_campaigns' => Booking::where('customer_id', $customer->id)
                ->where('status', 'completed')
                ->count(),
            'total_spend' => Booking::where('customer_id', $customer->id)
                ->whereIn('status', ['confirmed', 'in_progress', 'mounted', 'completed'])
                ->sum('total_amount'),
            // Use grand_total for pending payments (no 'amount' column in invoices)
            'pending_payments' => DB::table('invoices')
                ->where('customer_id', $customer->id)
                ->where('status', 'pending')
                ->sum('grand_total'),
            'active_hoardings' => Booking::where('customer_id', $customer->id)
                ->whereIn('status', ['confirmed', 'in_progress', 'mounted'])
                ->where('start_date', '<=', $now)
                ->where('end_date', '>=', $now)
                ->distinct('hoarding_id')
                ->count('hoarding_id'),
        ];
    }

    /**
     * Get active campaigns (currently running)
     */
    public function getActiveCampaigns(User $customer, int $limit = 10)
    {
        $now = Carbon::now();

        return Booking::with([
            'hoarding:id,title,location,city,state,type,image_url',
            'vendor:id,name,phone,email',
            'purchaseOrder:id,booking_id,po_number,status,grand_total,pdf_path',
            'timeline.events' => function ($query) {
                $query->where('event_type', 'stage_change')
                    ->latest()
                    ->limit(1);
            },
        ])
            ->where('customer_id', $customer->id)
            ->whereIn('status', ['confirmed', 'in_progress', 'mounted'])
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->orderBy('start_date', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($booking) {
                return $this->formatCampaignData($booking);
            });
    }

    /**
     * Get upcoming campaigns
     */
    public function getUpcomingCampaigns(User $customer, int $limit = 10)
    {
        $now = Carbon::now();

        return Booking::with([
            'hoarding:id,title,location,city,state,type,image_url',
            'vendor:id,name,phone,email',
            'purchaseOrder:id,booking_id,po_number,status,grand_total',
        ])
            ->where('customer_id', $customer->id)
            ->where('status', 'confirmed')
            ->where('start_date', '>', $now)
            ->orderBy('start_date', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($booking) {
                return $this->formatCampaignData($booking);
            });
    }

    /**
     * Get recently completed campaigns
     */
    public function getRecentCompletedCampaigns(User $customer, int $limit = 10)
    {
        return Booking::with([
            'hoarding:id,title,location,city,state,type,image_url',
            'vendor:id,name,phone,email',
            'purchaseOrder:id,booking_id,po_number,status,grand_total',
        ])
            ->where('customer_id', $customer->id)
            ->where('status', 'completed')
            ->orderBy('end_date', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($booking) {
                return $this->formatCampaignData($booking);
            });
    }

    /**
     * Get all campaigns with filtering
     */
    public function getAllCampaigns(User $customer, array $filters = [])
    {
        $query = Booking::with([
            'hoarding:id,title,location,city,state,type,image_url',
            'vendor:id,name,phone,email',
            'purchaseOrder:id,booking_id,po_number,status,grand_total',
        ])
            ->where('customer_id', $customer->id);

        // Apply filters
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $now = Carbon::now();
                $query->whereIn('status', ['confirmed', 'in_progress', 'mounted'])
                    ->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now);
            } elseif ($filters['status'] === 'upcoming') {
                $query->where('status', 'confirmed')
                    ->where('start_date', '>', Carbon::now());
            } else {
                $query->where('status', $filters['status']);
            }
        }

        if (!empty($filters['city'])) {
            $query->whereHas('hoarding', function ($q) use ($filters) {
                $q->where('city', $filters['city']);
            });
        }

        if (!empty($filters['type'])) {
            $query->whereHas('hoarding', function ($q) use ($filters) {
                $q->where('type', $filters['type']);
            });
        }

        if (!empty($filters['start_date'])) {
            $query->where('start_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('end_date', '<=', $filters['end_date']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('booking_id', 'like', "%{$search}%")
                    ->orWhereHas('hoarding', function ($hq) use ($search) {
                        $hq->where('title', 'like', "%{$search}%")
                            ->orWhere('location', 'like', "%{$search}%");
                    });
            });
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'start_date';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($filters['per_page'] ?? 15)
            ->through(function ($booking) {
                return $this->formatCampaignData($booking);
            });
    }

    /**
     * Get campaign details
     */
    public function getCampaignDetails(User $customer, int $bookingId): ?array
    {
        $booking = Booking::with([
            'hoarding:id,title,location,city,state,type,image_url,latitude,longitude,width,height,price_per_month',
            'vendor:id,name,phone,email,company_name',
            'purchaseOrder' => function ($query) {
                $query->with(['milestones']);
            },
            'timeline.events' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
        ])
            ->where('customer_id', $customer->id)
            ->where('id', $bookingId)
            ->first();

        if (!$booking) {
            return null;
        }

        return [
            'booking' => $this->formatCampaignData($booking),
            'timeline' => $this->formatTimeline($booking),
            'invoices' => $this->getBookingInvoices($booking),
            'creatives' => $this->getBookingCreatives($booking),
            'proofs' => $this->getBookingProofs($booking),
            'purchase_order' => $this->formatPurchaseOrder($booking->purchaseOrder),
            'communication' => $this->getRecentCommunication($booking),
        ];
    }

    /**
     * Get pending actions for customer
     */
    public function getPendingActions(User $customer): array
    {
        $actions = [];

        // Pending payments
        $pendingInvoices = DB::table('invoices')
            ->where('customer_id', $customer->id)
            ->where('status', 'pending')
            ->where('due_date', '<', Carbon::now()->addDays(7))
            ->count();

        if ($pendingInvoices > 0) {
            $actions[] = [
                'type' => 'payment',
                'title' => 'Pending Payments',
                'count' => $pendingInvoices,
                'message' => "{$pendingInvoices} invoice(s) due within 7 days",
                'action_url' => route('customer.invoices.index'),
                'priority' => 'high',
            ];
        }

        // Pending creative approvals
        $pendingCreatives = DB::table('booking_creatives')
            ->join('bookings', 'booking_creatives.booking_id', '=', 'bookings.id')
            ->where('bookings.customer_id', $customer->id)
            ->where('booking_creatives.status', 'pending_approval')
            ->count();

        if ($pendingCreatives > 0) {
            $actions[] = [
                'type' => 'approval',
                'title' => 'Creative Approvals',
                'count' => $pendingCreatives,
                'message' => "{$pendingCreatives} creative(s) awaiting your approval",
                'action_url' => route('customer.campaigns.index', ['filter' => 'pending_approval']),
                'priority' => 'medium',
            ];
        }

        // Campaigns starting soon
        $startingSoon = Booking::where('customer_id', $customer->id)
            ->where('status', 'confirmed')
            ->whereBetween('start_date', [Carbon::now(), Carbon::now()->addDays(7)])
            ->count();

        if ($startingSoon > 0) {
            $actions[] = [
                'type' => 'reminder',
                'title' => 'Campaigns Starting Soon',
                'count' => $startingSoon,
                'message' => "{$startingSoon} campaign(s) starting within 7 days",
                'action_url' => route('customer.campaigns.index', ['filter' => 'upcoming']),
                'priority' => 'low',
            ];
        }

        return $actions;
    }

    /**
     * Get recent invoices
     */
    protected function getRecentInvoices(User $customer, int $limit = 5): array
    {
        return DB::table('invoices')
            ->where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'amount' => $invoice->amount,
                    'status' => $invoice->status,
                    'due_date' => $invoice->due_date,
                    'created_at' => $invoice->created_at,
                ];
            })
            ->toArray();
    }

    /**
     * Get recent updates across all campaigns
     */
    protected function getRecentUpdates(User $customer, int $limit = 10): array
    {
        return DB::table('booking_timeline_events')
            ->join('bookings', 'booking_timeline_events.booking_id', '=', 'bookings.id')
            ->where('bookings.customer_id', $customer->id)
            ->select(
                'booking_timeline_events.*',
                'bookings.id as booking_id',
                'bookings.id as booking_number' // Use id as booking_number
            )
            ->orderBy('booking_timeline_events.created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Format campaign data for display
     */
    protected function formatCampaignData(Booking $booking): array
    {
        $now = Carbon::now();
        $daysRemaining = $booking->end_date ? Carbon::parse($booking->end_date)->diffInDays($now, false) : null;
        $daysUntilStart = $booking->start_date ? Carbon::parse($booking->start_date)->diffInDays($now, false) : null;

        return [
            'id' => $booking->id,
            'booking_id' => $booking->booking_id,
            'status' => $booking->status,
            'status_label' => $this->getStatusLabel($booking->status),
            'status_color' => $this->getStatusColor($booking->status),
            'hoarding' => [
                'id' => $booking->hoarding->id ?? null,
                'title' => $booking->hoarding->title ?? 'N/A',
                'location' => $booking->hoarding->location ?? 'N/A',
                'city' => $booking->hoarding->city ?? 'N/A',
                'state' => $booking->hoarding->state ?? 'N/A',
                'type' => $booking->hoarding->type ?? 'N/A',
                'image_url' => $booking->hoarding->image_url ?? null,
            ],
            'vendor' => [
                'id' => $booking->vendor->id ?? null,
                'name' => $booking->vendor->name ?? 'N/A',
                'phone' => $booking->vendor->phone ?? null,
            ],
            'dates' => [
                'start' => $booking->start_date,
                'end' => $booking->end_date,
                'duration_days' => $booking->start_date && $booking->end_date
                    ? Carbon::parse($booking->start_date)->diffInDays($booking->end_date)
                    : null,
                'days_remaining' => $daysRemaining,
                'days_until_start' => $daysUntilStart,
                'is_active' => $booking->start_date && $booking->end_date
                    ? Carbon::parse($booking->start_date)->lte($now) && Carbon::parse($booking->end_date)->gte($now)
                    : false,
            ],
            'financials' => [
                'total_amount' => $booking->total_amount,
                'payment_status' => $booking->payment_status ?? 'pending',
            ],
            'purchase_order' => $booking->purchaseOrder ? [
                'po_number' => $booking->purchaseOrder->po_number,
                'status' => $booking->purchaseOrder->status,
                'grand_total' => $booking->purchaseOrder->grand_total,
                'pdf_url' => $booking->purchaseOrder->pdf_path 
                    ? asset('storage/' . $booking->purchaseOrder->pdf_path)
                    : null,
            ] : null,
            'current_stage' => $booking->timeline->events->first()->stage ?? 'pending',
            'created_at' => $booking->created_at,
            'updated_at' => $booking->updated_at,
        ];
    }

    /**
     * Format timeline data
     */
    protected function formatTimeline(Booking $booking): array
    {
        if (!$booking->timeline) {
            return [];
        }

        return $booking->timeline->events->map(function ($event) {
            return [
                'id' => $event->id,
                'stage' => $event->stage,
                'event_type' => $event->event_type,
                'title' => $event->title,
                'description' => $event->description,
                'created_at' => $event->created_at,
                'metadata' => $event->metadata,
            ];
        })->toArray();
    }

    /**
     * Get booking invoices
     */
    protected function getBookingInvoices(Booking $booking): array
    {
        return DB::table('invoices')
            ->where('booking_id', $booking->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get booking creatives
     */
    protected function getBookingCreatives(Booking $booking): array
    {
        return DB::table('booking_creatives')
            ->where('booking_id', $booking->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get booking proofs (mounting, printing)
     */
    protected function getBookingProofs(Booking $booking): array
    {
        return DB::table('booking_proofs')
            ->where('booking_id', $booking->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Format purchase order data
     */
    protected function formatPurchaseOrder($po): ?array
    {
        if (!$po) {
            return null;
        }

        return [
            'id' => $po->id,
            'po_number' => $po->po_number,
            'status' => $po->status,
            'grand_total' => $po->grand_total,
            'pdf_url' => $po->pdf_path ? asset('storage/' . $po->pdf_path) : null,
            'milestones' => $po->milestones ?? [],
            'created_at' => $po->created_at,
        ];
    }

    /**
     * Get recent communication
     */
    protected function getRecentCommunication(Booking $booking, int $limit = 10): array
    {
        if (!$booking->thread_id) {
            return [];
        }

        return DB::table('thread_messages')
            ->where('thread_id', $booking->thread_id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get status label
     */
    protected function getStatusLabel(string $status): string
    {
        $labels = [
            'draft' => 'Draft',
            'pending' => 'Pending Approval',
            'confirmed' => 'Confirmed',
            'in_progress' => 'In Progress',
            'mounted' => 'Mounted',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];

        return $labels[$status] ?? ucfirst($status);
    }

    /**
     * Get status color
     */
    protected function getStatusColor(string $status): string
    {
        $colors = [
            'draft' => 'secondary',
            'pending' => 'warning',
            'confirmed' => 'info',
            'in_progress' => 'primary',
            'mounted' => 'success',
            'completed' => 'success',
            'cancelled' => 'danger',
        ];

        return $colors[$status] ?? 'secondary';
    }
}
