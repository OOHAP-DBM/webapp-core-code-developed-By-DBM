<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of the customer's payments.
     */
    public function index(Request $request)
    {
        // TODO: Implement payment listing logic
        return view('customer.payments.index');
    }

    /**
     * Display a specific payment.
     */
    public function show($id)
    {
        // TODO: Implement payment detail logic
        return view('customer.payments.show', compact('id'));
    }
}
