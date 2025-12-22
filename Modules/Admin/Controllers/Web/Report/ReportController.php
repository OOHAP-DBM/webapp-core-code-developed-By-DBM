<?php

namespace Modules\Admin\Controllers\Web\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        // Add logic to fetch report data
        return view('admin.report.index');
    }
}
