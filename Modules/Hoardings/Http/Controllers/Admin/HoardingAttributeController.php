<?php

namespace Modules\Hoardings\Http\Controllers\Admin;

use Modules\Hoarding\Models\HoardingAttribute;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class HoardingAttributeController extends Controller
{
    public function index()
    {
        $attributes = HoardingAttribute::groupedByType();
        return view('hoardings.admin.attributes.index', compact('attributes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'label' => 'required|string',
            'value' => 'required|string',
        ]);
        // Always store type as lowercase
        $validated['type'] = strtolower($validated['type']);
        HoardingAttribute::create($validated + ['is_active' => true]);
        return redirect()->back()->with('success', 'Attribute added successfully.');
    }

    public function destroy($id)
    {
        $attribute = HoardingAttribute::findOrFail($id);
        $attribute->delete();
        return redirect()->back()->with('success', 'Attribute deleted.');
    }
}
