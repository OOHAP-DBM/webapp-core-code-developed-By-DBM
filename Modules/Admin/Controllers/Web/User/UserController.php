<?php

namespace Modules\Admin\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::orderByDesc('created_at')->paginate(30);
        return view('admin.users.index', compact('users'));
    }
}
