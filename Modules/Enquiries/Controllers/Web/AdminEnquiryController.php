<?php

namespace Modules\Enquiries\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Modules\Enquiries\Models\Enquiry;   

class AdminEnquiryController extends Controller
{
    /**
     * All enquiries listing
     */
    public function index(Request $request)
    {
        $query = DB::table('enquiries as e')
            ->join('users as u', 'u.id', '=', 'e.customer_id')
            ->join('enquiry_items as ei', 'ei.enquiry_id', '=', 'e.id')
            ->select(
                'e.*',
                'u.name as customer_name',
                'u.email as customer_email',
                DB::raw('COUNT(ei.id) as hoardings_count'),
                DB::raw('COUNT(DISTINCT ei.hoarding_id) as locations_count'),
                DB::raw('MAX(ei.preferred_end_date) as offer_valid_till')
            )
            ->groupBy(
                'e.id',
                'e.status',
                'e.created_at',
                'e.updated_at',
                'e.customer_id',
                'e.source',
                'e.customer_note',
                'e.contact_number',
                'u.name',
                'u.email'
            )
            ->orderByDesc('e.id');

        // SEARCH by enquiry ID (auto search)
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            if (is_numeric($search)) {
                $query->where('e.id', $search);
            }
        }

        // FILTER by date
        if ($request->filled('date_filter')) {
            $filter = $request->input('date_filter');
            if ($filter === 'last_week') {
                $query->where('e.created_at', '>=', now()->subWeek());
            } elseif ($filter === 'last_month') {
                $query->where('e.created_at', '>=', now()->subMonth());
            } elseif ($filter === 'last_year') {
                $query->where('e.created_at', '>=', now()->subYear());
            } elseif ($filter === 'custom') {
                $from = $request->input('from_date');
                $to = $request->input('to_date');
                if ($from) {
                    $query->whereDate('e.created_at', '>=', $from);
                }
                if ($to) {
                    $query->whereDate('e.created_at', '<=', $to);
                }
            }
        }

        $enquiries = $query->paginate(10)->appends($request->except('page'));
        $enquiries->getCollection()->transform(function ($row) {
            $model = new \Modules\Enquiries\Models\Enquiry();
            foreach ((array) $row as $key => $value) {
                $model->{$key} = $value;
            }
            $model->exists = true;
            return $model;
        });
        return view('admin.enquiries.enquiry-index', compact('enquiries'));
    }


  
    public function show($id)
    {
        $enquiry = \Modules\Enquiries\Models\Enquiry::with([
            'customer',
            'offers.vendor',
            'offers.items',
            'items.hoarding.vendor',
            'items.hoarding.doohScreen'
        ])->findOrFail($id);
        $enquiry->getEnquiryDetails();
        $items = $enquiry->items;
        return view('admin.enquiries.enquiry-show', compact('enquiry','items'));
    }

}
