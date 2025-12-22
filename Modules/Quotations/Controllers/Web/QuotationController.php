<?php

namespace Modules\Quotations\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    /**
     * Display a listing of quotations for the customer panel.
     */
    public function index(Request $request)
    {
        // Fetch quotations for the logged-in customer, optionally filter by status
        $query = Quotation::where('customer_id', auth()->id())
            ->with(['offer.enquiry', 'vendor']);

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $quotations = $query->orderByDesc('created_at')->paginate(10);
        return view('customer.quotations.index', compact('quotations'));
    }

    /**
     * Show a specific quotation.
     */
    public function show($id)
    {
        // TODO: Fetch and authorize the quotation
        // Example: $quotation = Quotation::findOrFail($id);
        $quotation = null;
        return view('customer.quotations.show', compact('quotation'));
    }
}
