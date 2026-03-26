<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Hoarding;
use App\Models\Order;
use App\Models\Booking;
use App\Services\RazorpayService;
use Carbon\Carbon;
use DB;
use Modules\POS\Services\POSBookingService;

class PaymentController extends Controller
{
    protected POSBookingService $posBookingService;
    protected RazorpayService $razorpayService;

    public function __construct(POSBookingService $posBookingService, RazorpayService $razorpayService)
    {
        $this->posBookingService = $posBookingService;
        $this->razorpayService = $razorpayService;
    }
    /**
     * Show the billing details form
     */
    public function showBilling(Request $request)
    {
        $user = Auth::user();
        $hoardingId = $request->input('hoarding_id');
        $packageId = $request->input('package_id');
        $price = $request->input('price');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Create booking using POSBookingService
        $bookingData = [
            'hoarding_ids' => [$hoardingId],
            'customer_id' => $user->id,
            'payment_mode' => 'online',
            'start_date' => $startDate,
            'end_date' => $endDate,
            // Add other fields as needed
        ];
        $draft = $this->posBookingService->createBooking($bookingData);
        $draft->load('hoardings');

        // Create Razorpay order (optional, can be null at billing step)
        $amount = $price ?? $draft->total_amount;
        $order_id = null;
        $user_email = $user->email;
        $user_name = $user->name;
        $user_phone = $user->phone;
        $reviewSummary = [];

        return view('payment.billing', [
            'draft' => $draft,
            'hoarding' => $draft->hoardings->first(),
            'reviewSummary' => $reviewSummary,
            'amount' => $amount,
            'order_id' => $order_id,
            'user_email' => $user_email,
            'user_name' => $user_name,
            'user_phone' => $user_phone,
        ]);
    }

    /**
     * Save billing details and redirect to payment
     */
    public function saveBilling(Request $request)
    {
        try {
            $validated = $request->validate([
                'full_name' => 'required|string',
                'mobile' => 'required',
                'email' => 'required|email',
                'billing_address' => 'required',
                'pincode' => 'required',
                'city' => 'required',
                'state' => 'required',
            ]);
            session(['billing' => $validated]);
            return response()->json(['success' => true]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show payment page
     */
    public function showPayment(Request $request)
    {
        $user = Auth::user();
        $billing = session('billing');
        $draftId = $request->draft_id;
        if ($draftId) {
            // Load draft booking and hoarding info by draft_id (status = 'draft')
            $draft = \Modules\POS\Models\POSBooking::with('hoardings')->findOrFail($draftId);
            if ($draft->status !== 'draft') {
                abort(404, 'Booking is not in draft status');
            }
            $hoarding = $draft->hoardings->first();
            $startDate = $draft->start_date;
            $endDate = $draft->end_date;
            $duration = $draft->duration ?? (\Carbon\Carbon::parse($startDate)->diffInMonths(\Carbon\Carbon::parse($endDate)) ?: 1);
            $subtotal = $draft->base_price;
            $offerDiscount = $draft->discount_amount;
            $couponDiscount = $draft->coupon_discount ?? 0;
            $printingCharge = $draft->printing_charges ?? 0;
            $mountingCharge = $draft->mounting_charges ?? 0;
            $taxes = $draft->gst_amount ?? 0;
            $total = $draft->total_amount;
            return view('payment.payment', compact(
                'user',
                'hoarding',
                'billing',
                'startDate',
                'endDate',
                'duration',
                'subtotal',
                'offerDiscount',
                'couponDiscount',
                'printingCharge',
                'mountingCharge',
                'taxes',
                'total'
            ));
        } else {
            // Fallback to old logic if no draft_id
            $hoarding = Hoarding::findOrFail($request->hoarding_id);
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $duration = \Carbon\Carbon::parse($startDate)->diffInMonths(\Carbon\Carbon::parse($endDate)) ?: 1;
            $subtotal = $hoarding->monthly_price * $duration;
            $offerDiscount = round($subtotal * 0.3);
            $couponDiscount = 300;
            $printingCharge = 0;
            $mountingCharge = 0;
            $taxes = 1299;
            $total = $subtotal - $offerDiscount - $couponDiscount + $printingCharge + $mountingCharge + $taxes;
            return view('payment.payment', compact(
                'user',
                'hoarding',
                'billing',
                'startDate',
                'endDate',
                'duration',
                'subtotal',
                'offerDiscount',
                'couponDiscount',
                'printingCharge',
                'mountingCharge',
                'taxes',
                'total'
            ));
        }
    }

    /**
     * Create Razorpay order and return order details
     */
    public function createOrder(Request $request, RazorpayService $razorpay)
    {
        $user = Auth::user();
        $amount = $request->amount;
        $hoarding = null;
        $description = '';
        // If source is draft, use draft booking
        if ($request->source === 'draft' && $request->source_id) {
            $draft = \Modules\POS\Models\POSBooking::with('hoardings')->findOrFail($request->source_id);
            $hoarding = $draft->hoardings->first();
            $description = $hoarding ? $hoarding->title : 'Booking Payment';
            $amount = $draft->total_amount; // Ensure amount is set from draft
        } else {
            $hoarding = Hoarding::findOrFail($request->hoarding_id);
            $description = $hoarding->title;
        }

        $razorpayOrder = $razorpay->createOrder($amount, 'INR', uniqid('ORDER_'), 'manual');
        // Save order in DB
        $order = Order::create([
            'user_id' => $user->id,
            'hoarding_id' => $hoarding ? $hoarding->id : null,
            'amount' => $amount,
            'razorpay_order_id' => $razorpayOrder['id'],
            'status' => 'created',
            'source' => $request->source ?? null,
            'source_id' => $request->source_id ?? null,
        ]);

        return response()->json([
            'order_id' => $razorpayOrder['id'],
            'amount' => $amount,
            'key' => config('services.razorpay.key_id'),
            'name' => 'OOHAPP',
            'description' => $description,
        ]);
    }

    /**
     * Verify Razorpay payment and create booking
     */
    public function verifyPayment(Request $request, RazorpayService $razorpay)
    {
        $order = Order::where('razorpay_order_id', $request->razorpay_order_id)->firstOrFail();
        $isValid = $razorpay->verifySignature(
            $request->razorpay_order_id,
            $request->razorpay_payment_id,
            $request->razorpay_signature
        );

        if ($isValid) {
            DB::transaction(function () use ($order, $request) {
                $order->update(['status' => 'paid']);
                Booking::create([
                    'user_id' => $order->user_id,
                    'hoarding_id' => $order->hoarding_id,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'price_snapshot' => json_encode([
                        'amount' => $order->amount,
                        // ...other pricing details
                    ]),
                    'status' => 'confirmed',
                ]);
            });
            return response()->json(['status' => 'success']);
        } else {
            $order->update(['status' => 'failed']);
            return response()->json(['status' => 'failed', 'error' => 'Signature invalid'], 400);
        }
    }

    /**
     * (Optional) Apply coupon logic stub
     */
    public function applyCoupon(Request $request)
    {
        // TODO: Implement coupon application logic
        return response()->json(['status' => 'success', 'message' => 'Coupon applied (stub).']);
    }
}
