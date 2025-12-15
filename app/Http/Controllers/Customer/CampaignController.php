<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\CampaignDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * PROMPT 110: Customer Campaign Dashboard Controller
 */
class CampaignController extends Controller
{
    protected CampaignDashboardService $campaignService;

    public function __construct(CampaignDashboardService $campaignService)
    {
        $this->middleware('auth');
        $this->middleware('role:customer');
        $this->campaignService = $campaignService;
    }

    /**
     * Display campaign dashboard
     */
    public function dashboard()
    {
        $customer = Auth::user();
        $overview = $this->campaignService->getCustomerOverview($customer);

        return view('customer.campaigns.dashboard', $overview);
    }

    /**
     * List all campaigns with filters
     */
    public function index(Request $request)
    {
        $customer = Auth::user();

        $filters = $request->only([
            'status',
            'city',
            'type',
            'start_date',
            'end_date',
            'search',
            'sort_by',
            'sort_order',
            'per_page',
        ]);

        $campaigns = $this->campaignService->getAllCampaigns($customer, $filters);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'campaigns' => $campaigns,
            ]);
        }

        return view('customer.campaigns.index', [
            'campaigns' => $campaigns,
            'filters' => $filters,
        ]);
    }

    /**
     * Show campaign details
     */
    public function show(int $id)
    {
        $customer = Auth::user();
        $campaign = $this->campaignService->getCampaignDetails($customer, $id);

        if (!$campaign) {
            abort(404, 'Campaign not found');
        }

        return view('customer.campaigns.show', $campaign);
    }

    /**
     * Get active campaigns (AJAX)
     */
    public function active()
    {
        $customer = Auth::user();
        $campaigns = $this->campaignService->getActiveCampaigns($customer);

        return response()->json([
            'success' => true,
            'campaigns' => $campaigns,
        ]);
    }

    /**
     * Get upcoming campaigns (AJAX)
     */
    public function upcoming()
    {
        $customer = Auth::user();
        $campaigns = $this->campaignService->getUpcomingCampaigns($customer);

        return response()->json([
            'success' => true,
            'campaigns' => $campaigns,
        ]);
    }

    /**
     * Get completed campaigns (AJAX)
     */
    public function completed()
    {
        $customer = Auth::user();
        $campaigns = $this->campaignService->getRecentCompletedCampaigns($customer);

        return response()->json([
            'success' => true,
            'campaigns' => $campaigns,
        ]);
    }

    /**
     * Get campaign statistics (AJAX)
     */
    public function stats()
    {
        $customer = Auth::user();
        $stats = $this->campaignService->getStats($customer);

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * Get pending actions (AJAX)
     */
    public function pendingActions()
    {
        $customer = Auth::user();
        $actions = $this->campaignService->getPendingActions($customer);

        return response()->json([
            'success' => true,
            'actions' => $actions,
        ]);
    }

    /**
     * Download campaign report
     */
    public function downloadReport(int $id)
    {
        $customer = Auth::user();
        $campaign = $this->campaignService->getCampaignDetails($customer, $id);

        if (!$campaign) {
            abort(404, 'Campaign not found');
        }

        // Generate PDF report
        $pdf = \PDF::loadView('customer.campaigns.report', $campaign);
        
        return $pdf->download("campaign-{$campaign['booking']['booking_id']}-report.pdf");
    }

    /**
     * Export campaigns to CSV
     */
    public function export(Request $request)
    {
        $customer = Auth::user();
        
        $filters = $request->only([
            'status',
            'city',
            'type',
            'start_date',
            'end_date',
            'search',
        ]);

        $campaigns = $this->campaignService->getAllCampaigns($customer, array_merge($filters, ['per_page' => 1000]));

        $filename = "campaigns-" . date('Y-m-d') . ".csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($campaigns) {
            $file = fopen('php://output', 'w');

            // Headers
            fputcsv($file, [
                'Booking ID',
                'Status',
                'Hoarding',
                'Location',
                'City',
                'Type',
                'Start Date',
                'End Date',
                'Duration (Days)',
                'Total Amount',
                'Payment Status',
                'PO Number',
            ]);

            // Data rows
            foreach ($campaigns as $campaign) {
                fputcsv($file, [
                    $campaign['booking_id'],
                    $campaign['status_label'],
                    $campaign['hoarding']['title'],
                    $campaign['hoarding']['location'],
                    $campaign['hoarding']['city'],
                    $campaign['hoarding']['type'],
                    $campaign['dates']['start'],
                    $campaign['dates']['end'],
                    $campaign['dates']['duration_days'],
                    $campaign['financials']['total_amount'],
                    $campaign['financials']['payment_status'],
                    $campaign['purchase_order']['po_number'] ?? 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
