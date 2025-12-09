<?php

namespace App\Services;

use App\Models\User;
use App\Models\CustomerDashboardStat;
use App\Models\Booking;
use App\Models\Enquiry;
use Illuminate\Support\Facades\DB;

class CustomerDashboardService
{
    /**
     * Get or create dashboard stats for a customer
     */
    public function getStats(User $customer, bool $forceRecalculate = false): CustomerDashboardStat
    {
        $stats = CustomerDashboardStat::firstOrCreate(['user_id' => $customer->id]);

        if ($forceRecalculate || $stats->needsRecalculation()) {
            $this->calculateStats($customer, $stats);
        }

        return $stats->fresh();
    }

    /**
     * Calculate all statistics for a customer
     */
    public function calculateStats(User $customer, CustomerDashboardStat $stats): void
    {
        // Booking Stats
        $bookingStats = $this->calculateBookingStats($customer);
        
        // Payment Stats
        $paymentStats = $this->calculatePaymentStats($customer);
        
        // Enquiry Stats
        $enquiryStats = $this->calculateEnquiryStats($customer);
        
        // Offer Stats
        $offerStats = $this->calculateOfferStats($customer);
        
        // Quotation Stats
        $quotationStats = $this->calculateQuotationStats($customer);
        
        // Invoice Stats
        $invoiceStats = $this->calculateInvoiceStats($customer);
        
        // Thread Stats
        $threadStats = $this->calculateThreadStats($customer);

        // Update all stats
        $stats->update(array_merge(
            $bookingStats,
            $paymentStats,
            $enquiryStats,
            $offerStats,
            $quotationStats,
            $invoiceStats,
            $threadStats,
            ['last_calculated_at' => now()]
        ));
    }

    /**
     * Calculate booking statistics
     */
    protected function calculateBookingStats(User $customer): array
    {
        $bookings = DB::table('bookings')
            ->where('customer_id', $customer->id)
            ->selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN status IN ('confirmed', 'active', 'ongoing') THEN 1 END) as active,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled,
                COALESCE(SUM(total_amount), 0) as total_amount
            ")
            ->first();

        return [
            'total_bookings' => $bookings->total ?? 0,
            'active_bookings' => $bookings->active ?? 0,
            'completed_bookings' => $bookings->completed ?? 0,
            'cancelled_bookings' => $bookings->cancelled ?? 0,
            'total_booking_amount' => $bookings->total_amount ?? 0,
        ];
    }

    /**
     * Calculate payment statistics
     */
    protected function calculatePaymentStats(User $customer): array
    {
        $payments = DB::table('booking_payments')
            ->join('bookings', 'booking_payments.booking_id', '=', 'bookings.id')
            ->where('bookings.customer_id', $customer->id)
            ->selectRaw("
                COUNT(*) as total,
                COALESCE(SUM(CASE WHEN booking_payments.status = 'completed' THEN booking_payments.amount END), 0) as paid,
                COALESCE(SUM(CASE WHEN booking_payments.status = 'pending' THEN booking_payments.amount END), 0) as pending,
                COALESCE(SUM(CASE WHEN booking_payments.status = 'refunded' THEN booking_payments.amount END), 0) as refunded
            ")
            ->first();

        return [
            'total_payments' => $payments->total ?? 0,
            'total_paid' => $payments->paid ?? 0,
            'total_pending' => $payments->pending ?? 0,
            'total_refunded' => $payments->refunded ?? 0,
        ];
    }

    /**
     * Calculate enquiry statistics
     */
    protected function calculateEnquiryStats(User $customer): array
    {
        $enquiries = DB::table('enquiries')
            ->where('customer_id', $customer->id)
            ->selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                COUNT(CASE WHEN status IN ('responded', 'converted') THEN 1 END) as responded
            ")
            ->first();

        return [
            'total_enquiries' => $enquiries->total ?? 0,
            'pending_enquiries' => $enquiries->pending ?? 0,
            'responded_enquiries' => $enquiries->responded ?? 0,
        ];
    }

    /**
     * Calculate offer statistics
     */
    protected function calculateOfferStats(User $customer): array
    {
        $offers = DB::table('offers')
            ->where('customer_id', $customer->id)
            ->selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
                COUNT(CASE WHEN status = 'accepted' THEN 1 END) as accepted
            ")
            ->first();

        return [
            'total_offers' => $offers->total ?? 0,
            'active_offers' => $offers->active ?? 0,
            'accepted_offers' => $offers->accepted ?? 0,
        ];
    }

    /**
     * Calculate quotation statistics
     */
    protected function calculateQuotationStats(User $customer): array
    {
        $quotations = DB::table('quotations')
            ->where('customer_id', $customer->id)
            ->selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved
            ")
            ->first();

        return [
            'total_quotations' => $quotations->total ?? 0,
            'pending_quotations' => $quotations->pending ?? 0,
            'approved_quotations' => $quotations->approved ?? 0,
        ];
    }

    /**
     * Calculate invoice statistics
     */
    protected function calculateInvoiceStats(User $customer): array
    {
        // Assuming invoices are generated from bookings
        $invoices = DB::table('bookings')
            ->where('customer_id', $customer->id)
            ->whereNotNull('invoice_number')
            ->selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN payment_status = 'paid' THEN 1 END) as paid,
                COUNT(CASE WHEN payment_status IN ('pending', 'partial') THEN 1 END) as unpaid,
                COALESCE(SUM(total_amount), 0) as total_amount
            ")
            ->first();

        return [
            'total_invoices' => $invoices->total ?? 0,
            'paid_invoices' => $invoices->paid ?? 0,
            'unpaid_invoices' => $invoices->unpaid ?? 0,
            'total_invoice_amount' => $invoices->total_amount ?? 0,
        ];
    }

    /**
     * Calculate thread statistics
     */
    protected function calculateThreadStats(User $customer): array
    {
        $threads = DB::table('threads')
            ->where('customer_id', $customer->id)
            ->selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN has_unread_messages = 1 THEN 1 END) as unread
            ")
            ->first();

        return [
            'total_threads' => $threads->total ?? 0,
            'unread_threads' => $threads->unread ?? 0,
        ];
    }

    /**
     * Get recent activities for dashboard
     */
    public function getRecentActivities(User $customer, int $limit = 10): array
    {
        $activities = [];

        // Recent bookings
        $recentBookings = Booking::where('customer_id', $customer->id)
            ->latest()
            ->limit(5)
            ->get(['id', 'booking_number', 'status', 'created_at']);

        foreach ($recentBookings as $booking) {
            $activities[] = [
                'type' => 'booking',
                'title' => "Booking #{$booking->booking_number}",
                'status' => $booking->status,
                'date' => $booking->created_at,
                'url' => route('customer.bookings.show', $booking->id),
            ];
        }

        // Recent enquiries
        $recentEnquiries = Enquiry::where('customer_id', $customer->id)
            ->latest()
            ->limit(5)
            ->get(['id', 'status', 'created_at']);

        foreach ($recentEnquiries as $enquiry) {
            $activities[] = [
                'type' => 'enquiry',
                'title' => "Enquiry #{$enquiry->id}",
                'status' => $enquiry->status,
                'date' => $enquiry->created_at,
                'url' => route('customer.enquiries.show', $enquiry->id),
            ];
        }

        // Sort by date and limit
        usort($activities, function($a, $b) {
            return $b['date'] <=> $a['date'];
        });

        return array_slice($activities, 0, $limit);
    }

    /**
     * Get upcoming bookings
     */
    public function getUpcomingBookings(User $customer, int $limit = 5)
    {
        return Booking::where('customer_id', $customer->id)
            ->whereIn('status', ['confirmed', 'active'])
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->limit($limit)
            ->get();
    }

    /**
     * Get pending payments
     */
    public function getPendingPayments(User $customer)
    {
        return DB::table('booking_payments')
            ->join('bookings', 'booking_payments.booking_id', '=', 'bookings.id')
            ->where('bookings.customer_id', $customer->id)
            ->where('booking_payments.status', 'pending')
            ->select('booking_payments.*', 'bookings.booking_number')
            ->get();
    }

    /**
     * Get chart data for bookings over time
     */
    public function getBookingChartData(User $customer, string $period = 'monthly'): array
    {
        $groupBy = $period === 'daily' ? 'DATE(created_at)' : 'DATE_FORMAT(created_at, "%Y-%m")';
        
        $data = DB::table('bookings')
            ->where('customer_id', $customer->id)
            ->selectRaw("
                {$groupBy} as period,
                COUNT(*) as count,
                SUM(total_amount) as amount
            ")
            ->groupBy('period')
            ->orderBy('period')
            ->limit(12)
            ->get();

        return [
            'labels' => $data->pluck('period')->toArray(),
            'bookings' => $data->pluck('count')->toArray(),
            'amounts' => $data->pluck('amount')->toArray(),
        ];
    }

    /**
     * Get spending summary by category
     */
    public function getSpendingSummary(User $customer): array
    {
        $summary = DB::table('bookings')
            ->join('hoardings', 'bookings.hoarding_id', '=', 'hoardings.id')
            ->where('bookings.customer_id', $customer->id)
            ->where('bookings.payment_status', 'paid')
            ->selectRaw("
                hoardings.type as category,
                COUNT(*) as booking_count,
                SUM(bookings.total_amount) as total_spent
            ")
            ->groupBy('hoardings.type')
            ->get();

        return [
            'categories' => $summary->pluck('category')->toArray(),
            'spending' => $summary->pluck('total_spent')->toArray(),
            'counts' => $summary->pluck('booking_count')->toArray(),
        ];
    }
}
