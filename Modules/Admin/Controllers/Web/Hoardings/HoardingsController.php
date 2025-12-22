<?php

namespace Modules\Admin\Controllers\Web\Hoardings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HoardingsController extends Controller
{
    public function index(): View
    {
        // Add logic to fetch hoardings data
        return view('admin.hoardings.index');
    }
}
