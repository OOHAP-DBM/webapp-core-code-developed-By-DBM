<?php

namespace Modules\Admin\Controllers\Web\ActivityLogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogsController extends Controller
{
    public function index(): View
    {
        // Add logic to fetch activity logs data
        return view('admin.activitylogs.index');
    }
}
