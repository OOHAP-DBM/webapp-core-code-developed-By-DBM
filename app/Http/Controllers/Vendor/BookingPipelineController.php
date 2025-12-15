<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Services\BookingPipelineService;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingPipelineController extends Controller
{
    protected BookingPipelineService $pipelineService;

    public function __construct(BookingPipelineService $pipelineService)
    {
        $this->middleware(['auth', 'role:vendor']);
        $this->pipelineService = $pipelineService;
    }

    /**
     * Display the Kanban pipeline board
     * GET /vendor/pipeline
     */
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->get('search'),
            'hoarding_id' => $request->get('hoarding_id'),
            'customer_id' => $request->get('customer_id'),
            'priority' => $request->get('priority'),
        ];

        $pipelineData = $this->pipelineService->getVendorPipeline(Auth::user(), $filters);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $pipelineData,
            ]);
        }

        return view('vendor.pipeline.index', [
            'stages' => $pipelineData['stages'],
            'summary' => $pipelineData['summary'],
            'filters' => $filters,
        ]);
    }

    /**
     * Get pipeline data (AJAX refresh)
     * GET /vendor/pipeline/data
     */
    public function getData(Request $request)
    {
        $filters = [
            'search' => $request->get('search'),
            'hoarding_id' => $request->get('hoarding_id'),
            'customer_id' => $request->get('customer_id'),
            'priority' => $request->get('priority'),
        ];

        $pipelineData = $this->pipelineService->getVendorPipeline(Auth::user(), $filters);

        return response()->json([
            'success' => true,
            'data' => $pipelineData,
        ]);
    }

    /**
     * Move booking to different stage (drag & drop)
     * POST /vendor/pipeline/move
     */
    public function moveBooking(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'from_stage' => 'required|string',
            'to_stage' => 'required|string',
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);

        // Verify vendor owns this booking
        if ($booking->vendor_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $result = $this->pipelineService->moveBooking(
            $booking,
            $validated['from_stage'],
            $validated['to_stage'],
            Auth::user()
        );

        return response()->json($result);
    }

    /**
     * Get booking details (for modal/sidebar)
     * GET /vendor/pipeline/booking/{id}
     */
    public function getBookingDetails($id)
    {
        $booking = Booking::with([
            'customer',
            'hoarding',
            'quotation.offer.enquiry',
            'timeline.events' => function($q) {
                $q->latest()->limit(10);
            },
            'payments',
        ])->findOrFail($id);

        // Verify vendor owns this booking
        if ($booking->vendor_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'booking' => [
                'id' => $booking->id,
                'booking_id' => $booking->booking_id,
                'status' => $booking->status,
                'customer' => [
                    'id' => $booking->customer->id,
                    'name' => $booking->customer->name,
                    'email' => $booking->customer->email,
                    'phone' => $booking->customer->phone,
                ],
                'hoarding' => [
                    'id' => $booking->hoarding->id,
                    'title' => $booking->hoarding->title,
                    'location' => $booking->hoarding->location,
                    'city' => $booking->hoarding->city,
                    'type' => $booking->hoarding->type,
                    'image_url' => $booking->hoarding->image_url,
                ],
                'dates' => [
                    'start' => $booking->start_date?->format('M d, Y'),
                    'end' => $booking->end_date?->format('M d, Y'),
                    'duration_days' => $booking->start_date && $booking->end_date 
                        ? $booking->start_date->diffInDays($booking->end_date) 
                        : null,
                ],
                'financials' => [
                    'total_amount' => $booking->total_amount,
                    'total_amount_formatted' => '₹' . number_format($booking->total_amount),
                    'payment_status' => $booking->payment_status ?? 'pending',
                ],
                'timeline' => $booking->timeline?->events?->map(function($event) {
                    return [
                        'title' => $event->title,
                        'description' => $event->description,
                        'status' => $event->status,
                        'created_at' => $event->created_at->format('M d, Y H:i'),
                        'created_at_human' => $event->created_at->diffForHumans(),
                    ];
                }),
                'created_at' => $booking->created_at->format('M d, Y H:i'),
            ],
        ]);
    }

    /**
     * Get stage statistics
     * GET /vendor/pipeline/stats
     */
    public function getStats(Request $request)
    {
        $filters = [
            'search' => $request->get('search'),
            'hoarding_id' => $request->get('hoarding_id'),
            'customer_id' => $request->get('customer_id'),
            'priority' => $request->get('priority'),
        ];

        $pipelineData = $this->pipelineService->getVendorPipeline(Auth::user(), $filters);

        return response()->json([
            'success' => true,
            'stats' => $pipelineData['summary'],
            'stage_counts' => collect($pipelineData['stages'])->mapWithKeys(function($stage) {
                return [$stage['key'] => $stage['count']];
            }),
        ]);
    }

    /**
     * Bulk update bookings stage
     * POST /vendor/pipeline/bulk-move
     */
    public function bulkMove(Request $request)
    {
        $validated = $request->validate([
            'booking_ids' => 'required|array',
            'booking_ids.*' => 'exists:bookings,id',
            'to_stage' => 'required|string',
        ]);

        $results = [];
        $successCount = 0;
        $failCount = 0;

        foreach ($validated['booking_ids'] as $bookingId) {
            $booking = Booking::find($bookingId);

            if ($booking && $booking->vendor_id === Auth::id()) {
                // Determine current stage (simplified)
                $fromStage = $this->detectCurrentStage($booking);
                
                $result = $this->pipelineService->moveBooking(
                    $booking,
                    $fromStage,
                    $validated['to_stage'],
                    Auth::user()
                );

                if ($result['success']) {
                    $successCount++;
                } else {
                    $failCount++;
                }

                $results[] = [
                    'booking_id' => $bookingId,
                    'success' => $result['success'],
                    'message' => $result['message'],
                ];
            } else {
                $failCount++;
                $results[] = [
                    'booking_id' => $bookingId,
                    'success' => false,
                    'message' => 'Booking not found or unauthorized',
                ];
            }
        }

        return response()->json([
            'success' => $successCount > 0,
            'message' => "{$successCount} bookings moved successfully, {$failCount} failed",
            'results' => $results,
        ]);
    }

    /**
     * Export pipeline view as PDF/CSV
     * GET /vendor/pipeline/export
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv'); // csv or pdf
        $filters = [
            'search' => $request->get('search'),
            'hoarding_id' => $request->get('hoarding_id'),
            'customer_id' => $request->get('customer_id'),
            'priority' => $request->get('priority'),
        ];

        $pipelineData = $this->pipelineService->getVendorPipeline(Auth::user(), $filters);

        if ($format === 'csv') {
            return $this->exportCSV($pipelineData);
        } else {
            return $this->exportPDF($pipelineData);
        }
    }

    /**
     * Export pipeline as CSV
     */
    protected function exportCSV(array $pipelineData)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="pipeline-' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($pipelineData) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Stage', 'Booking ID', 'Customer', 'Hoarding', 'Location', 'City',
                'Start Date', 'End Date', 'Amount', 'Status', 'Created At'
            ]);
            
            // Data
            foreach ($pipelineData['stages'] as $stage) {
                foreach ($stage['bookings'] as $booking) {
                    fputcsv($file, [
                        $stage['label'],
                        $booking['booking_id'],
                        $booking['customer_name'],
                        $booking['hoarding_title'],
                        $booking['hoarding_location'],
                        $booking['hoarding_city'],
                        $booking['start_date'] ?? 'N/A',
                        $booking['end_date'] ?? 'N/A',
                        $booking['total_amount'],
                        $booking['status_label'],
                        $booking['created_at'],
                    ]);
                }
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export pipeline as PDF
     */
    protected function exportPDF(array $pipelineData)
    {
        $pdf = \PDF::loadView('vendor.pipeline.export-pdf', [
            'stages' => $pipelineData['stages'],
            'summary' => $pipelineData['summary'],
            'generated_at' => now()->format('M d, Y H:i'),
        ]);

        return $pdf->download('pipeline-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Detect current stage of a booking (helper)
     */
    protected function detectCurrentStage(Booking $booking): string
    {
        // Simplified stage detection based on status and timeline
        if ($booking->status === 'draft' || $booking->status === 'pending') {
            if ($booking->quotation && $booking->quotation->status === 'sent') {
                return 'quotation_sent';
            }
            if ($booking->quotation && $booking->quotation->offer && $booking->quotation->offer->status === 'sent') {
                return 'offer_sent';
            }
            return 'new_enquiry';
        }

        if ($booking->status === 'payment_hold') {
            return 'in_payment';
        }

        if ($booking->status === 'payment_settled') {
            return 'booked';
        }

        if ($booking->status === 'confirmed') {
            // Check timeline for active stage
            if ($booking->start_date && $booking->end_date) {
                if (now()->between($booking->start_date, $booking->end_date)) {
                    return 'live';
                }
                if (now()->greaterThan($booking->end_date)) {
                    return 'completed';
                }
            }

            // Check for production stages
            $activeEvent = $booking->timeline?->events()
                ->whereIn('event_type', ['designing', 'printing', 'mounting'])
                ->whereIn('status', ['pending', 'in_progress'])
                ->latest()
                ->first();

            if ($activeEvent) {
                return $activeEvent->event_type;
            }

            return 'booked';
        }

        return 'new_enquiry';
    }
}
