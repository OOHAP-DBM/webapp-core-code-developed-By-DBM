<?php
namespace Modules\Enquiries\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Enquiries\Models\Enquiry;
use Modules\Enquiries\Http\Resources\Api\EnquiryResource;
use Modules\Enquiries\Services\EnquiryService;
use Modules\Enquiries\Http\Resources\Api\EnquiryItemResource;
class VendorEnquiryController extends Controller
{
    protected EnquiryService $service;
    protected GracePeriodService $gracePeriodService;

    public function __construct(EnquiryService $service)
    {
        $this->service = $service;
       
    }
    /**
     * List all enquiries for hoardings owned by the current vendor/agency/staff
     * GET /api/v1/vendor/enquiries
     */
    public function index(Request $request)
    {
        $user = Auth::user();

    if (! $user->hasRole('vendor')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $enquiries = $this->service->getVendorEnquiries($user->id);
        return EnquiryResource::collection($enquiries);
    }

    public function show(int $id)
    {
        $user = Auth::user();

        // Must be authenticated
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // First check: enquiry exists AND has at least one item for this vendor
        $enquiry = Enquiry::where('id', $id)
            ->whereHas('items.hoarding', function ($q) use ($user) {
                $q->where('vendor_id', $user->id);
            })
            ->first();

        if (! $enquiry) {
            return response()->json([
                'success' => false,
                'message' => 'Enquiry not found or access denied'
            ], Response::HTTP_NOT_FOUND);
        }

        // Load ONLY vendor-specific data
        $enquiry->load([
            'customer',
            'items' => function ($q) use ($user) {
                $q->whereHas('hoarding', function ($h) use ($user) {
                    $h->where('vendor_id', $user->id);
                });
            },
            'items.hoarding.vendor',
            'items.hoarding.ooh',
            'items.hoarding.doohScreen',
            'items.hoarding.vendor.vendorProfile',
            'items.package',
        ]);

        return response()->json([
            'success' => true,
            'data' => new EnquiryItemResource($enquiry),
        ]);
    }
}
