<?php

namespace Modules\POS\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;

class AdminPosController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function dashboard()
    {
        // Render the admin POS dashboard view
        return View::make('admin.pos.dashboard');
    }

    public function index()
    {
        // Render the admin POS bookings list view
        return View::make('admin.pos.list');
    }

    public function create()
    {
        // Render the admin POS create booking view
        return View::make('admin.pos.create');
    }

    // Extend: edit, view, etc. as needed
}
