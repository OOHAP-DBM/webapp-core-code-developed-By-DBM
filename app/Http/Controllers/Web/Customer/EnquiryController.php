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
use Modules\Cart\Services\CartService;
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
        logger('📩 ENQUIRY REQUEST RAW', $request->all());

        $validated = $request->validate([
            'hoarding_id' => 'required',
            'hoarding_id.*' => 'exists:hoardings,id',

            'package_id' => 'nullable',
            'package_id.*' => 'nullable|exists:hoarding_packages,id',

            'package_label' => 'nullable',
            'amount' => 'required',
            'amount.*' => 'numeric|min:0',

            'duration_type' => 'required|string',
            'preferred_start_date' => 'required|date',
            'preferred_end_date' => 'nullable|date',

            'customer_name' => 'required|string',
            'customer_mobile' => 'nullable|string',
            'customer_email' => 'nullable|email',
            'message' => 'nullable|string',
        ]);

        /* =====================================================
        | 🔥 NORMALIZE EVERYTHING TO ARRAY (CORE FIX)
        ===================================================== */
        $hoardingIds = is_array($validated['hoarding_id'])
            ? array_values($validated['hoarding_id'])
            : [$validated['hoarding_id']];

        $packageIds = is_array($validated['package_id'] ?? null)
            ? array_values($validated['package_id'])
            : [$validated['package_id'] ?? null];

        $packageLabels = is_array($validated['package_label'] ?? null)
            ? array_values($validated['package_label'])
            : [$validated['package_label'] ?? 'Base Price'];

        $amounts = is_array($validated['amount'])
            ? array_values($validated['amount'])
            : [$validated['amount']];

        logger('🧩 NORMALIZED DATA', compact(
            'hoardingIds', 'packageIds', 'packageLabels', 'amounts'
        ));

        return DB::transaction(function () use (
            $validated,
            $hoardingIds,
            $packageIds,
            $packageLabels,
            $amounts
        ) {

            logger('🟢 TRANSACTION START');

            $user = auth()->user();
            $customerEmail = $validated['customer_email']
                ?? auth()->user()->email
                ?? null;

            /* ================= ENQUIRY ================= */
            $enquiry = Enquiry::create([
                'customer_id'    => $user->id,
                'source'         => $user->role ?? 'user',
                'status'         => Enquiry::STATUS_SUBMITTED,
                'customer_note'  => $validated['message'] ?? null,
                'contact_number' => $validated['customer_mobile'],
                'customer_email' => $customerEmail,
            ]);

            logger('🧾 ENQUIRY CREATED', ['id' => $enquiry->id]);

            /* ================= LOOP ITEMS ================= */
            foreach ($hoardingIds as $index => $hoardingId) {

                $packageId    = $packageIds[$index] ?? null;
                $packageLabel = $packageLabels[$index] ?? 'Base Price';
                $amount       = $amounts[$index] ?? 0;

                logger('➡️ PROCESS ITEM', compact(
                    'hoardingId','packageId','packageLabel','amount'
                ));

                $hoarding = Hoarding::where('id', $hoardingId)
                    ->where('status', 'active')
                    ->firstOrFail();

                $startDate = Carbon::parse($validated['preferred_start_date']);

                $services = [];
                $endDate = null;
                $expectedDuration = null;
                $packageType = 'base';

                /* ================= PACKAGE ================= */
                if (!empty($packageId)) {

                    $package = HoardingPackage::findOrFail($packageId);

                    $endDate = (clone $startDate)
                        ->addMonths($package->min_booking_duration);

                    $expectedDuration =
                        $startDate->diffInDays($endDate) . ' days';

                    $serviceNames = is_string($package->services_included)
                        ? json_decode($package->services_included, true)
                        : ($package->services_included ?? []);

                    $priceMap = is_string($hoarding->service_prices)
                        ? json_decode($hoarding->service_prices, true)
                        : ($hoarding->service_prices ?? []);

                    $services = $this->buildServicesWithPrice(
                        $serviceNames,
                        $priceMap
                    );

                    $packageType = 'package';

                    logger('📦 PACKAGE APPLIED', ['package_id' => $packageId]);

                } else {

                    $endDate = !empty($validated['preferred_end_date'])
                        ? Carbon::parse($validated['preferred_end_date'])
                        : (clone $startDate)->addMonth();

                    $expectedDuration =
                        $startDate->diffInDays($endDate) . ' days';

                    $services = $this->buildBaseOOHServices($hoarding);

                    logger('🏷 BASE BOOKING');
                }

                /* ================= ENQUIRY ITEM ================= */
                EnquiryItem::create([
                    'enquiry_id'           => $enquiry->id,
                    'hoarding_id'          => $hoarding->id,
                    'hoarding_type'        => $hoarding->hoarding_type,

                    'package_id'           => $packageId,
                    'package_type'         => $packageType,

                    'preferred_start_date' => $startDate,
                    'preferred_end_date'   => $endDate,
                    'expected_duration'    => $expectedDuration,

                    'services' => $services,

                    'meta' => [
                        'package_label' => $packageLabel,
                        'amount'        => $amount,
                        'duration_type' => $validated['duration_type'],
                        'customer_name' => $validated['customer_name'],
                        'customer_email' => $customerEmail,
                        'customer_mobile'=> $validated['customer_mobile'],
                    ],

                    'status' => EnquiryItem::STATUS_NEW,
                ]);

                logger('✅ ENQUIRY ITEM SAVED', ['hoarding_id' => $hoardingId]);
            }

            logger('🎉 TRANSACTION COMMIT');

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
    public function shortlisted(CartService $cartService)
    {
        return response()->json(
            $cartService->getCartForUI()
        );
    }
    public function packages($id)
    {
        return DB::table('hoarding_packages')
            ->where('hoarding_id', $id)
            ->where('is_active', 1)
            ->select(
                'id',
                'package_name',
                'price',
                'duration',
                'duration_unit'
            )
            ->get();
    }
}
