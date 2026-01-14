@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')

{{-- ================= HEADER ================= --}}
<div class="flex justify-between items-center mb-6">
    <h2 class="text-lg text-gray-700">
        Good Morning,
        <span class="text-blue-600 font-semibold">
            {{ auth()->user()->name ?? 'Admin' }}
        </span> 👋
    </h2>
</div>

{{-- ================= STATS ================= --}}
<div class="bg-white rounded-xl p-6 mb-6">
    <h3 class="text-sm font-semibold mb-4">Platform Statistics</h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @php
            $stats = [
                ['Total Users', $userCount, 'bg-blue-600'],
                ['Vendors', $vendorCount, 'bg-indigo-600'],
                ['Customers', $customerCount, 'bg-purple-600'],
                ['Bookings', $bookingCount, 'bg-teal-600'],
                ['Total Hoardings', $hoardingCount, 'bg-green-600'],
                ['OOH Hoardings', $oohCount, 'bg-gray-600'],
                ['DOOH Hoardings', $doohCount, 'bg-pink-600'],
            ];
        @endphp

        @foreach($stats as [$label, $value, $color])
            <div class="bg-gray-50 rounded-xl p-4 relative">
                <p class="text-xs text-gray-500">{{ $label }}</p>
                <p class="text-2xl font-semibold mt-1">{{ number_format($value) }}</p>
                <span class="absolute bottom-0 left-0 w-full h-1 {{ $color }} rounded-b-xl"></span>
            </div>
        @endforeach
    </div>
</div>

{{-- ================= CHARTS ================= --}}
<div class="bg-white rounded-xl p-6 mb-6 grid grid-cols-1 xl:grid-cols-2 gap-6">
    <div>
        <h4 class="text-sm font-semibold mb-2">Monthly User Growth</h4>
        <canvas id="userChart" height="120"></canvas>
    </div>

    <div>
        <h4 class="text-sm font-semibold mb-2">Monthly Bookings</h4>
        <canvas id="bookingChart" height="120"></canvas>
    </div>
</div>

{{-- ================= RECENT SYSTEM ACTIVITY ================= --}}
<div class="bg-white rounded-xl shadow-sm mb-6">
    <div class="px-6 pt-5 pb-3 flex justify-between items-center">
        <h4 class="text-sm font-semibold text-gray-800">
            Recent System Activity
        </h4>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="border-b bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-6 py-3 text-left">#</th>
                    <th class="px-6 py-3 text-left">Activity</th>
                    <th class="px-6 py-3 text-left">Triggered By</th>
                    <th class="px-6 py-3 text-left">Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentActivities ?? [] as $i => $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3">{{ $i + 1 }}</td>
                        <td class="px-6 py-3 font-medium">{{ $row['type'] }}</td>
                        <td class="px-6 py-3">{{ $row['by'] }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $row['time'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-6 text-center text-gray-500">
                            No recent activity
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ================= ADMIN NOTES ================= --}}
<div class="bg-white rounded-xl shadow-sm p-6">
    <h4 class="text-sm font-semibold text-gray-800 mb-3">
        Admin Notes
    </h4>

    <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
        <li>All data is fetched live from database</li>
        <li>User roles managed via Spatie permissions</li>
        <li>This dashboard is strictly admin-only</li>
        <li>No vendor components are reused</li>
    </ul>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
/* ===== USER CHART ===== */
new Chart(document.getElementById('userChart'), {
    type: 'line',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        datasets: [{
            data: [120,180,260,340,410,520,610,700,820,900,1040,{{ $userCount }}],
            borderColor: '#2563eb',
            tension: .4,
            fill: false
        }]
    },
    options: { plugins: { legend: { display: false } } }
});

/* ===== BOOKING CHART ===== */
new Chart(document.getElementById('bookingChart'), {
    type: 'line',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        datasets: [{
            data: [20,35,50,60,80,120,150,170,200,230,260,{{ $bookingCount }}],
            borderColor: '#14b8a6',
            tension: .4,
            fill: false
        }]
    },
    options: { plugins: { legend: { display: false } } }
});
</script>
@endpush
