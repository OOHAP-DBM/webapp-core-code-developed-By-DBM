@extends('layouts.vendor')
@section('page-title', 'Dashboard')
@section('content')
    @php
        $vendorProfile = auth()->user()->vendorProfile ?? null;
        $pendingStatuses = ['pending_approval', 'draft'];
    @endphp
    @if(session('status') === 'pending' || ($vendorProfile && in_array($vendorProfile->onboarding_status, $pendingStatuses)))
        <div class="mb-6 flex items-start gap-4 rounded-xl border border-red-200 bg-red-50 px-5 py-4">

            <!-- ICON -->
            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-red-100">
                <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <!-- CONTENT -->
            <div class="flex-1">
                <h4 class="text-sm font-semibold text-red-700">
                    Vendor Approval Pending
                </h4>
                <p class="mt-1 text-sm text-red-600">
                    Your vendor request is under review. Once approved by the admin, you’ll be able to access all OOHAPP vendor features.
                </p>
            </div>

            <!-- BADGE -->
            <span class="ml-auto mt-1 rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">
                Pending
            </span>
        </div>
    @endif
    @php
        /* =========================
        | DATA PASSED FROM CONTROLLER
        ========================= */
    @endphp
    <!-- HEADER -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg text-gray-700">
            Good Morning, <span class="text-blue-600 font-semibold">{{ auth()->user()->name ?? 'Vendor' }}</span>!
        </h2>
        <a href="{{ route('vendor.hoardings.create') }}" class="bg-black text-white px-4 py-2 rounded-lg text-sm">+ Add Hoarding</a>
    </div>
    <!-- STATISTICS -->
    <div class="bg-white rounded-xl p-6 mb-6">
        <h3 class="text-sm font-semibold mb-4">Statistics</h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
            @foreach([
                ['Total Earnings','₹ '.number_format($stats['earnings']),'earnings'],
                ['Total Hoardings',$stats['total_hoardings'],'hoardings'],
                ['OOH Hoardings',$stats['ooh'],'ooh'],
                ['DOOH Hoardings',$stats['dooh'],'dooh'],
                ['Active Hoardings',$stats['active'],'active'],
                ['Inactive Hoardings',$stats['inactive'],'inactive'],
                ['Unsold Hoardings',$stats['unsold'],'unsold'],
                ['Total Bookings',$stats['total_bookings'],'bookings'],
                ['My Orders',$stats['my_orders'],'orders'],
                ['POS Bookings',$stats['pos'],'pos'],
            ] as [$label,$value,$key])
            @php
                $statColors = [
                    'earnings'  => 'bg-blue-500',
                    'hoardings' => 'bg-green-500',
                    'ooh'       => 'bg-gray-500',
                    'dooh'      => 'bg-pink-500',
                    'active'    => 'bg-green-500',
                    'inactive'  => 'bg-gray-500',
                    'unsold'    => 'bg-gray-500',
                    'bookings'  => 'bg-blue-500',
                    'orders'    => 'bg-purple-500',
                    'pos'       => 'bg-blue-500',
                ];
            @endphp
            <div class="bg-gray-50 rounded-xl p-4 relative">
                <p class="text-xs text-gray-500">{{ $label }}</p>
                <p class="text-2xl font-semibold mt-1">{{ $value }}</p>
                <span class="absolute rounded-b-full bottom-0 left-0 w-full h-1 {{ $statColors[$key] ?? 'bg-gray-300' }}"></span>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-4">
            <button class="text-blue-600 text-sm" style="cursor:pointer;">Show more</button>
        </div>
    </div>
    <!-- CHARTS -->
    <div class="bg-white rounded-xl p-6 mb-6 grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div>
            <h4 class="text-sm font-semibold mb-2">Earning statistics</h4>
            <canvas id="earningChart" height="120"></canvas>
        </div>
        <div>
            <h4 class="text-sm font-semibold mb-2">Booked statistics</h4>
            <canvas id="bookingChart" height="120"></canvas>
        </div>
    </div>
    <!-- Best Selling Hoardings -->
    <div class="bg-white rounded-xl shadow-sm mb-6">
        <div class="px-6 pt-5 pb-3 flex justify-between items-center">
            <h4 class="text-sm font-semibold text-gray-800">
                Top 5 Best Selling Hoardings
            </h4>
            <div class="text-xs text-gray-500 flex items-center gap-1">
                SORT BY:
                <select name="" id="">
                    <option value="">1 Month</option>
                    <option value="">6 Month</option>
                    <option value="">1 Year</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3 text-left">SN</th>
                        <th class="px-6 py-3 text-left">Hoarding Title</th>
                        <th class="px-6 py-3 text-left">Type</th>
                        <th class="px-6 py-3 text-left">Categories</th>
                        <th class="px-6 py-3 text-left">Hoarding Location</th>
                        <th class="px-6 py-3 text-left">Size</th>
                        <!-- <th class="px-6 py-3 text-left"># Of Bookings</th> -->
                        <th class="px-6 py-3 text-left">Published By</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($topHoardings as $i => $h)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">{{ sprintf('%02d', $i+1) }}</td>

                        <td class="px-6 py-4 font-medium text-gray-800 truncate max-w-[180px]">
                            {{ $h['title'] }}
                        </td>

                        <td class="px-6 py-4">{{ $h['type'] }}</td>
                        <td class="px-6 py-4">{{ $h['cat'] }}</td>
                        <td class="px-6 py-4 truncate max-w-[160px]">{{ $h['loc'] }}</td>
                        <td class="px-6 py-4">{{ $h['size'] }}</td>

                        <!-- <td class="px-6 py-4 text-green-600 font-semibold">
                            {{ $h['bookings'] }}
                        </td> -->

                        <td class="px-6 py-4 text-blue-600 hover:underline cursor-pointer">
                            {{ $h['publisher'] }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <!-- Top 5 Customers -->
    <div class="bg-white rounded-xl shadow-sm mb-6">
        <div class="px-6 pt-5 pb-3 flex justify-between items-center">
            <h4 class="text-sm font-semibold text-gray-800">
                Top 5 Customers
            </h4>
            <div class="text-xs text-gray-500 flex items-center gap-1">
                SORT BY:
                <select name="" id="">
                    <option value="">1 Month</option>
                    <option value="">6 Month</option>
                    <option value="">1 Year</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3 text-left">SN</th>
                        <th class="px-6 py-3 text-left">Customer</th>
                        <th class="px-6 py-3 text-left">Customer ID</th>
                        <th class="px-6 py-3 text-left">Registered By</th>
                        <!-- <th class="px-6 py-3 text-left"># Of Bookings</th> -->
                        <th class="px-6 py-3 text-left">Total Amount</th>
                        <th class="px-6 py-3 text-left">Location</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($topCustomers as $i => $c)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">{{ sprintf('%02d', $i+1) }}</td>

                        <td class="px-6 py-4 font-medium">{{ $c['name'] }}</td>
                        <td class="px-6 py-4">{{ $c['id'] }}</td>

                        <td class="px-6 py-4 text-blue-600">
                            By {{ $c['by'] }}
                        </td>

                        <!-- <td class="px-6 py-4 text-green-600 font-semibold">
                            {{ $c['bookings'] }}
                        </td> -->

                        <td class="px-6 py-4 text-blue-600 font-semibold">
                            ₹{{ number_format($c['amount']) }}
                        </td>

                        <td class="px-6 py-4">{{ $c['loc'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <!-- Recent Transactions -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 pt-5 pb-1 flex justify-between items-center">
            <div>
                <h4 class="text-sm font-semibold text-gray-800">
                    Recent Transactions
                </h4>
                <p class="text-xs text-gray-500">
                    Total {{ count($transactions) }} Transactions done in this week
                </p>
            </div>

            <div class="text-xs text-gray-500 flex items-center gap-1">
                SORT BY: 
                <select name="" id="">
                    <option value="">1 Month</option>
                    <option value="">6 Month</option>
                    <option value="">1 Year</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm mt-4">
                <thead class="border-b border-gray-200 bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3 text-left">SN</th>
                        <th class="px-6 py-3 text-left">Transaction ID</th>
                        <th class="px-6 py-3 text-left">Customer</th>
                        <th class="px-6 py-3 text-left"># Of Bookings</th>
                        <th class="px-6 py-3 text-left">Payment Status</th>
                        <th class="px-6 py-3 text-left">Booking Type</th>
                        <th class="px-6 py-3 text-left">Date</th>
                        <th class="px-6 py-3 text-left">Amount Received</th>
                        <th class="px-6 py-3 text-left">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($transactions as $i => $t)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">{{ sprintf('%02d', $i+1) }}</td>
                        <td class="px-6 py-4 font-medium">{{ $t['id'] }}</td>
                        <td class="px-6 py-4">{{ $t['customer'] }}</td>

                        <td class="px-6 py-4 text-green-600 font-semibold">
                            {{ $t['bookings'] }}
                        </td>

                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">
                                {{ $t['status'] }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-blue-600">
                            {{ $t['type'] }}
                        </td>

                        <td class="px-6 py-4">{{ $t['date'] }}</td>

                        <td class="px-6 py-4 text-blue-600 font-semibold">
                            ₹{{ number_format($t['amount']) }}
                        </td>

                        <td class="px-6 py-4">
                            <button class="bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded text-xs">
                                Invoice
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        new Chart(document.getElementById('earningChart'),{
            type:'line',
            data:{
                labels:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                datasets:[{
                    data:[12000,13000,5000,36000,26000,42000,32000,45000,36000,46000,48000,50000],
                    borderColor:'#2563eb',
                    tension:.4,
                    fill:false
                }]
            },
            options:{plugins:{legend:{display:false}}}
        });

        new Chart(document.getElementById('bookingChart'),{
            type:'line',
            data:{
                labels:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                datasets:[{
                    data:[12,14,8,12,36,25,45,35,45,46,48,50],
                    borderColor:'#ec4899',
                    tension:.4,
                    fill:false
                }]
            },
            options:{plugins:{legend:{display:false}}}
        });
    </script>
@endpush
