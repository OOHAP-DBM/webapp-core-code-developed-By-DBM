<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use App\Models\Hoarding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Enquiries\Models\Enquiry;
use Modules\Enquiries\Models\EnquiryItem;
use Carbon\Carbon;
use Modules\DOOH\Models\DOOHPackage;
use Modules\Hoardings\Models\HoardingPackage;

class EnquiryController extends Controller
{
    /* =====================================================
     | INDEX
     ===================================================== */
    public function index(Request $request)
    {
        $query = Enquiry::where('customer_id', Auth::id())
            ->with(['hoarding', 'quotation'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $enquiries = $query->paginate(10);

        return view('customer.enquiries.index', compact('enquiries'));
    }

    /* =====================================================
     | CREATE
     ===================================================== */
    public function create(Request $request)
    {
        $hoarding = null;

        if ($request->filled('hoarding_id')) {
            $hoarding = Hoarding::where('status', 'approved')
                ->findOrFail($request->hoarding_id);
        }

        return view('customer.enquiry-create', compact('hoarding'));
    }

    /* =====================================================
     | STORE
     ===================================================== */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'hoarding_id'          => 'required|exists:hoardings,id',

            'package_id'           => 'nullable|integer',
            'package_label'        => 'nullable|string|max:255',

            'amount'               => 'required|numeric|min:0',
            'duration_type'        => 'required|string',

            'preferred_start_date' => 'required|date',
            'preferred_end_date'   => 'nullable|date',

            'customer_name'        => 'required|string|max:255',
            'customer_email'       => 'nullable|email|max:255',
            'customer_mobile'      => 'required|string|max:20',

            'message'              => 'nullable|string|max:1000',
        ]);

        return DB::transaction(function () use ($validated) {

            /* ================= USER ================= */
            $user = auth()->user();

            /* ================= ENQUIRY ================= */
            $enquiry = Enquiry::create([
                'customer_id'    => $user->id,
                'source'         => $user->role ?? 'user',
                'status'         => Enquiry::STATUS_SUBMITTED,
                'customer_note'  => $validated['message'] ?? null,
                'contact_number' => $validated['customer_mobile'],
            ]);

            /* ================= HOARDING ================= */
            $hoarding = Hoarding::where('id', $validated['hoarding_id'])
                ->where('status', 'active')
                ->firstOrFail();

            $isOOH  = $hoarding->hoarding_type === EnquiryItem::TYPE_OOH;
            $isDOOH = $hoarding->hoarding_type === EnquiryItem::TYPE_DOOH;

            /* ================= COMMON ================= */
            $startDate = Carbon::parse($validated['preferred_start_date']);
            $endDate   = null;
            $services  = [];
            $expectedDuration = null;

            /* =====================================================
             | PACKAGE SELECTED
             ===================================================== */
            if (!empty($validated['package_id'])) {

                if ($isOOH) {
                    $package = HoardingPackage::findOrFail($validated['package_id']);
                } else {
                    $package = DOOHPackage::findOrFail($validated['package_id']);
                }

                $endDate = (clone $startDate)->addMonths($package->min_booking_duration);
                $expectedDuration = $startDate->diffInDays($endDate) . ' days';

                $serviceNames = $package->services_included ?? [];
                $priceMap    = $hoarding->service_prices ?? [];

                $services = $this->buildServicesWithPrice($serviceNames, $priceMap);

            } else {

                /* =================================================
                 | BASE PRICE
                 ================================================= */

                if ($isOOH) {

                    if (empty($validated['preferred_end_date'])) {
                        throw new \Exception('End date required for OOH base booking');
                    }

                    $endDate = Carbon::parse($validated['preferred_end_date']);
                    $expectedDuration = $startDate->diffInDays($endDate) . ' days';

                    $serviceNames = $hoarding->services ?? [];
                    $priceMap    = $hoarding->service_prices ?? [];

                    $services = $this->buildBaseOOHServices($hoarding);


                } else {
                    // DOOH BASE
                    $endDate = Carbon::parse(
                        $validated['preferred_end_date'] ?? $validated['preferred_start_date']
                    );

                    $expectedDuration = '10 seconds';
                    $services = [];
                }
            }

            /* ================= ENQUIRY ITEM ================= */
            EnquiryItem::create([
                'enquiry_id'           => $enquiry->id,
                'hoarding_id'          => $hoarding->id,
                'hoarding_type'        => $hoarding->hoarding_type,

                'package_id'           => $validated['package_id'] ?? null,
                'package_type'         => $validated['package_id'] ? 'package' : 'base',

                'preferred_start_date' => $startDate,
                'preferred_end_date'   => $endDate,
                'expected_duration'    => $expectedDuration,

                'services'             => $services,

                'meta' => [
                    'package_label'   => $validated['package_label'] ?? 'Base Price',
                    'amount'          => $validated['amount'],
                    'duration_type'   => $validated['duration_type'],
                    'customer_name'   => $validated['customer_name'],
                    'customer_email'  => $validated['customer_email'],
                    'customer_mobile' => $validated['customer_mobile'],
                    'duration_unit'   => $isDOOH && empty($validated['package_id'])
                        ? 'seconds'
                        : 'days',
                ],

                'status' => EnquiryItem::STATUS_NEW,
            ]);

            return response()->json([
                'success'    => true,
                'enquiry_id' => $enquiry->id,
                'message'    => 'Enquiry submitted successfully',
            ]);
        });
    }

    /* =====================================================
     | SHOW
     ===================================================== */
    public function show(int $id)
    {
        $enquiry = Enquiry::where('customer_id', Auth::id())
            ->with(['hoarding.vendor', 'quotation'])
            ->findOrFail($id);

        return view('customer.enquiries.show', compact('enquiry'));
    }

    /* =====================================================
     | CANCEL
     ===================================================== */
    public function cancel(int $id)
    {
        $enquiry = Enquiry::where('customer_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($id);

        $enquiry->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Enquiry cancelled successfully',
        ]);
    }

    /* =====================================================
     | SERVICE PRICE BUILDER (CORE)
     ===================================================== */
    private function buildServicesWithPrice(array $serviceNames, array $priceMap = []): array
    {
        $services = [];

        foreach ($serviceNames as $service) {
            $price = (int) ($priceMap[$service] ?? 0);

            $services[] = [
                'name'  => $service,
                'price' => $price,
                'type'  => $price > 0 ? 'paid' : 'free',
            ];
        }

        return $services;
    }
    private function buildBaseOOHServices(Hoarding $hoarding): array
    {
        $services = [];

        // Graphics
        if ((int) $hoarding->graphics_included === 1) {
            $services[] = [
                'name'  => 'graphics',
                'price' => 0,
                'type'  => 'free',
            ];
        } elseif (!empty($hoarding->graphics_charge) && $hoarding->graphics_charge > 0) {
            $services[] = [
                'name'  => 'graphics',
                'price' => (int) $hoarding->graphics_charge,
                'type'  => 'paid',
            ];
        }

        // Survey
        if (!empty($hoarding->survey_charge) && $hoarding->survey_charge > 0) {
            $services[] = [
                'name'  => 'survey',
                'price' => (int) $hoarding->survey_charge,
                'type'  => 'paid',
            ];
        }

        return $services;
    }

}
