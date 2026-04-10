@extends('layouts.admin')

@section('content')
<div class="bg-white shadow-sm rounded w-full max-w-2xl mx-auto px-6 py-8">
	<h1 class="text-2xl font-semibold mb-6">Create Mail Layout</h1>
	<form action="{{ route('admin.mail.layouts.store') }}" method="POST" enctype="multipart/form-data">
		@csrf
		<div class="mb-4">
			<label class="block mb-1 font-medium">Logo Image</label>
			<input type="file" name="logo_url" class="form-input w-full">
		</div>
		<div class="mb-4">
			<label class="block mb-1 font-medium">Header HTML</label>
			<textarea name="header_html" class="form-textarea w-full" rows="3">{{ old('header_html') }}</textarea>
		</div>
		<div class="mb-4">
			<label class="block mb-1 font-medium">Footer HTML</label>
			<textarea name="footer_html" class="form-textarea w-full" rows="3">{{ old('footer_html') }}</textarea>
		</div>
		<div class="mb-4">
			<label class="block mb-1 font-medium">Primary Color</label>
			<input type="text" name="primary_color" class="form-input w-full" value="{{ old('primary_color') }}" placeholder="#00995c">
		</div>
		<div class="mb-4">
			<label class="block mb-1 font-medium">Font Family</label>
			<input type="text" name="font_family" class="form-input w-full" value="{{ old('font_family') }}" placeholder="Arial, sans-serif">
		</div>
		<div class="mb-4 flex items-center gap-2">
			<input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}>
			<label for="is_active" class="font-medium">Active</label>
		</div>
		<div class="flex gap-3 mt-6">
			<button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700">Create</button>
			<a href="{{ route('admin.mail.layouts.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Cancel</a>
		</div>
	</form>
</div>
@endsection
