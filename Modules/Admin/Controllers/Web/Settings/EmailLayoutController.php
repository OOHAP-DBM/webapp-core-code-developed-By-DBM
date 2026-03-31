<?php

namespace Modules\Admin\Controllers\Web\Settings;

use App\Models\EmailLayout;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;

class EmailLayoutController extends Controller
{
	public function index()
	{
		$layouts = EmailLayout::all();
		return view('admin.mail.layouts.index', compact('layouts'));
	}

	public function create()
	{
		return view('admin.mail.layouts.create');
	}

	public function store(Request $request)
	{
		$validated = $request->validate([
			'logo_url' => 'nullable|string|max:255',
			'header_html' => 'nullable|string',
			'footer_html' => 'nullable|string',
			'primary_color' => 'nullable|string|max:32',
			'font_family' => 'nullable|string|max:64',
			'is_active' => 'boolean',
		]);
		$layout = EmailLayout::create($validated);
		return redirect()->route('admin.mail.layouts.index')->with('success', 'Layout created successfully.');
	}

	public function edit(EmailLayout $layout)
	{
		return view('admin.mail.layouts.edit', compact('layout'));
	}

	public function update(Request $request, EmailLayout $layout)
    {
        $validated = $request->validate([
            'logo_url' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
            'header_html' => 'nullable|string',
            'footer_html' => 'nullable|string',
            'primary_color' => 'nullable|string|max:32',
            'font_family' => 'nullable|string|max:64',
            'is_active' => 'nullable|boolean',
        ]);

        // ✅ Handle logo upload
        if ($request->hasFile('logo_url')) {

            // Delete old image (optional but best)
            if ($layout->logo_url && File::exists(public_path($layout->logo_url))) {
                File::delete(public_path($layout->logo_url));
            }

            // Folder path
            $destinationPath = public_path('assets/images/emails/logo');

            // Create folder if not exists
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            // New filename
            $file = $request->file('logo_url');
            $filename = time() . '_' . $file->getClientOriginalName();

            // Move file
            $file->move($destinationPath, $filename);

            // Save path in DB
            $validated['logo_url'] = 'assets/images/emails/logo/' . $filename;
        }

        // Checkbox fix
        $validated['is_active'] = $request->has('is_active');

        // Update
        $layout->update($validated);

        return redirect()
            ->route('admin.mail.layouts.index')
            ->with('success', 'Layout updated successfully.');
    }

	public function destroy(EmailLayout $layout)
	{
		$layout->delete();
		return redirect()->route('admin.mail.layouts.index')->with('success', 'Layout deleted successfully.');
	}
}
