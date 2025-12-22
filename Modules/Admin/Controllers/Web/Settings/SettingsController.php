<?php

namespace Modules\Admin\Controllers\Web\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        // Add logic to fetch settings data
        return view('admin.settings.index');
    }
}
