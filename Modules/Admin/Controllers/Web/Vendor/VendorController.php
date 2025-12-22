<?php

namespace Modules\Admin\Controllers\Web\Vendor;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $vendors = User::role('vendor')->orderByDesc('created_at')->paginate(30);
        return view('admin.vendors.index', compact('vendors'));
    }

    public function show($id)
    {
        $vendor = User::role('vendor')->findOrFail($id);
        return view('admin.vendors.show', compact('vendor'));
    }

    public function approve($id)
    {
        $vendor = User::role('vendor')->findOrFail($id);
        $vendor->status = 'active';
        $vendor->save();
        return redirect()->back()->with('success', 'Vendor approved.');
    }

    public function suspend($id)
    {
        $vendor = User::role('vendor')->findOrFail($id);
        $vendor->status = 'suspended';
        $vendor->save();
        return redirect()->back()->with('success', 'Vendor suspended.');
    }
}
