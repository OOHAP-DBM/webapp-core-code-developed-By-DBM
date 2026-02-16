@extends('layouts.admin')

@section('title', 'All Hoardings')
@section('page_title', 'Hoarding Attributes')

@section('breadcrumb')
<x-breadcrumb :items="[
    ['label' => 'Home', 'route' => route('admin.dashboard')],
    ['label' => 'All Hoardings', 'route' => route('admin.my-hoardings')],
    ['label' => 'Category']
]" />
@endsection
@section('content')
@section('content')
<div class="max-w-4xl mx-auto py-10">
    <h2 class="text-2xl font-bold mb-8">Hoarding Attributes Settings</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        @foreach($attributes as $type => $items)
        <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
            <h3 class="text-lg font-semibold mb-4 capitalize">{{ $type }}</h3>
            <ul class="mb-4 divide-y divide-gray-100">
                @foreach($items as $attr)
                <li class="flex items-center justify-between py-2">
                    <span>{{ $attr->label }} <span class="text-xs text-gray-400">({{ $attr->value }})</span></span>
                    <form action="{{ route('admin.hoarding-attributes.destroy', $attr->id) }}" method="POST" onsubmit="return confirm('Delete this attribute?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:underline text-xs">Delete</button>
                    </form>
                </li>
                @endforeach
            </ul>
            <form action="{{ route('admin.hoarding-attributes.store') }}" method="POST" class="space-y-2">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="text" name="label" placeholder="Label (e.g. Unipole)" class="w-full border rounded px-3 py-2 text-sm" required>
                <input type="text" name="value" placeholder="Value (e.g. unipole)" class="w-full border rounded px-3 py-2 text-sm" required>
                <button type="submit" class="w-full bg-[#009A5C] text-white rounded py-2 font-bold">Add New</button>
            </form>
        </div>
        @endforeach
    </div>
    <div class="mt-10">
        <h4 class="text-lg font-semibold mb-2">Add New Attribute Type</h4>
        <form action="{{ route('admin.hoarding-attributes.store') }}" method="POST" class="flex gap-2">
            @csrf
            <input type="text" name="type" placeholder="Type (e.g. category)" class="border rounded px-3 py-2 text-sm" required>
            <input type="text" name="label" placeholder="Label (e.g. Billboard)" class="border rounded px-3 py-2 text-sm" required>
            <input type="text" name="value" placeholder="Value (e.g. billboard)" class="border rounded px-3 py-2 text-sm" required>
            <button type="submit" class="bg-[#009A5C] text-white rounded px-4 py-2 font-bold">Add</button>
        </form>
    </div>
</div>
@endsection
