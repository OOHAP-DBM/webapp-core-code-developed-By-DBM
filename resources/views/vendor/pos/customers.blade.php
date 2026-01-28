@extends('layouts.vendor')
@section('title', 'POS Customers')
@section('content')
<div class="px-6 py-6">
    <div class="bg-white rounded-xl shadow border">
        <div class="px-6 py-4 bg-primary text-white rounded-t-xl">
            <h4 class="text-lg font-semibold flex items-center gap-2">
                👥 POS Customers
            </h4>
        </div>
        <div class="p-6">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Phone</th>
                        <th class="px-4 py-2">Email</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $customer)
                    <tr>
                        <td class="px-4 py-2">{{ $customer->name }}</td>
                        <td class="px-4 py-2">{{ $customer->phone }}</td>
                        <td class="px-4 py-2">{{ $customer->email }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if($customers->isEmpty())
                <div class="text-center text-gray-500 mt-4">No customers found.</div>
            @endif
        </div>
    </div>
</div>
@endsection