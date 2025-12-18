<?php

namespace Modules\Admin\Controllers\Web\Payments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentsController extends Controller
{
    public function index(): View
    {
        // Add logic to fetch payments data
        return view('admin.payments.index');
    }
}
