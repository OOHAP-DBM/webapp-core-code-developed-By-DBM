@extends('layouts.customer')

@section('title', 'Home - OOHAPP')

@push('styles')
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 60px 0;
            color: black;
            border-radius: 20px;
            margin-bottom: 40px;
        }
        
        .search-box-main {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            max-width: 800px;
            margin: 0 auto;
        }
        
        .location-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            font-size: 14px;
            margin-bottom: 16px;
        }
        
        .stats-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-card .number {
            font-size: 36px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 8px;
        }
        
        .stats-card .label {
            font-size: 14px;
            color: #64748b;
        }
        
        .category-chip {
            display: inline-flex;
            align-items: center;
            padding: 12px 20px;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-weight: 500;
            color: #334155;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .category-chip:hover {
            border-color: #667eea;
            background: #f8fafc;
            color: #667eea;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .section-title {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
        }
    </style>
@endpush
@section('content')
    <div class="p-6 bg-gray-50 " id="dashboardApp">
            <!-- TITLE -->
            <h2 class="text-lg text-gray-700 mb-6">
                Good Morning, <span class="text-blue-600 font-semibold">{{ auth()->user()->name ?? 'Customer' }}</span>!
            </h2>
            <!-- STATS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <!-- TOTAL CAMPAIGN -->
                    <div class="bg-[#F3F4F6] rounded-xl p-4 flex items-start gap-3 w-full max-w-xs">

                        <!-- ICON -->
                        <div class="w-10 h-10 rounded-full bg-[#E5E7EB] flex items-center justify-center flex-shrink-0">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M18 13V11H22V13H18ZM19.2 20L16 17.6L17.2 16L20.4 18.4L19.2 20ZM17.2 8L16 6.4L19.2 4L20.4 5.6L17.2 8ZM5 19V15H4C3.45 15 2.97933 14.8043 2.588 14.413C2.19667 14.0217 2.00067 13.5507 2 13V11C2 10.45 2.196 9.97933 2.588 9.588C2.98 9.19667 3.45067 9.00067 4 9H8L13 6V18L8 15H7V19H5ZM14 15.35V8.65C14.45 9.05 14.8127 9.53767 15.088 10.113C15.3633 10.6883 15.5007 11.3173 15.5 12C15.4993 12.6827 15.3617 13.312 15.087 13.888C14.8123 14.464 14.45 14.9513 14 15.35Z"
                                    fill="#374151"/>
                            </svg>
                        </div>

                        <!-- CONTENT -->
                        <div>
                            <p class="text-sm font-medium text-gray-700 leading-tight">
                                Total Campaign
                            </p>

                            <p class="text-xl font-semibold text-gray-900 leading-snug mt-1" id="totalCampaign">
                                ₹22K
                            </p>

                            <p class="text-xs text-gray-500 mt-0.5">
                                Spend till date
                            </p>
                        </div>

                    </div>
                    <!-- ACTIVE CAMPAIGN -->
                    <div class="bg-[#DCFCE7] rounded-xl p-4 flex items-start gap-3 w-full max-w-xs">

                        <!-- ICON -->
                        <div class="w-10 h-10 rounded-full bg-[#86EFAC] flex items-center justify-center flex-shrink-0">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M18 13V11H22V13H18ZM19.2 20L16 17.6L17.2 16L20.4 18.4L19.2 20ZM17.2 8L16 6.4L19.2 4L20.4 5.6L17.2 8ZM5 19V15H4C3.45 15 2.97933 14.8043 2.588 14.413C2.19667 14.0217 2.00067 13.5507 2 13V11C2 10.45 2.196 9.97933 2.588 9.588C2.98 9.19667 3.45067 9.00067 4 9H8L13 6V18L8 15H7V19H5ZM14 15.35V8.65C14.45 9.05 14.8127 9.53767 15.088 10.113C15.3633 10.6883 15.5007 11.3173 15.5 12C15.4993 12.6827 15.3617 13.312 15.087 13.888C14.8123 14.464 14.45 14.9513 14 15.35Z"
                                    fill="#166534"/>
                            </svg>
                        </div>

                        <!-- CONTENT -->
                        <div>
                            <p class="text-sm font-medium text-gray-900 leading-tight">
                                Active Campaign
                            </p>

                            <p class="text-2xl font-semibold text-gray-900 leading-snug mt-1" id="activeCampaign">
                                15
                            </p>

                            <p class="text-xs text-gray-600 mt-0.5">
                                All Active Campaign
                            </p>
                        </div>

                    </div>
                    <!-- LIVE CAMPAIGN -->
                <div class="bg-[#DBEAFE] rounded-xl p-4 flex items-start gap-3 w-full max-w-xs">

                    <!-- ICON -->
                    <div class="w-10 h-10 rounded-full bg-[#93C5FD] flex items-center justify-center flex-shrink-0">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M18 13V11H22V13H18ZM19.2 20L16 17.6L17.2 16L20.4 18.4L19.2 20ZM17.2 8L16 6.4L19.2 4L20.4 5.6L17.2 8ZM5 19V15H4C3.45 15 2.97933 14.8043 2.588 14.413C2.19667 14.0217 2.00067 13.5507 2 13V11C2 10.45 2.196 9.97933 2.588 9.588C2.98 9.19667 3.45067 9.00067 4 9H8L13 6V18L8 15H7V19H5ZM14 15.35V8.65C14.45 9.05 14.8127 9.53767 15.088 10.113C15.3633 10.6883 15.5007 11.3173 15.5 12C15.4993 12.6827 15.3617 13.312 15.087 13.888C14.8123 14.464 14.45 14.9513 14 15.35Z"
                                    fill="#374151"/>
                            </svg>
                    </div>

                    <!-- CONTENT -->
                    <div>
                        <p class="text-sm font-medium text-gray-700 leading-tight">
                            Live Campaign
                        </p>

                        <p class="text-2xl font-semibold text-gray-900 leading-snug mt-1" id="liveCampaign">
                            02
                        </p>
                    </div>

                </div>
                    <!-- ENDED CAMPAIGN -->
                    <div class="bg-[#FECACA] rounded-xl p-4 flex items-start gap-3 w-full max-w-xs">

                        <!-- ICON -->
                        <div class="w-10 h-10 rounded-full bg-[#F87171] flex items-center justify-center flex-shrink-0">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M18 13V11H22V13H18ZM19.2 20L16 17.6L17.2 16L20.4 18.4L19.2 20ZM17.2 8L16 6.4L19.2 4L20.4 5.6L17.2 8ZM5 19V15H4C3.45 15 2.97933 14.8043 2.588 14.413C2.19667 14.0217 2.00067 13.5507 2 13V11C2 10.45 2.196 9.97933 2.588 9.588C2.98 9.19667 3.45067 9.00067 4 9H8L13 6V18L8 15H7V19H5ZM14 15.35V8.65C14.45 9.05 14.8127 9.53767 15.088 10.113C15.3633 10.6883 15.5007 11.3173 15.5 12C15.4993 12.6827 15.3617 13.312 15.087 13.888C14.8123 14.464 14.45 14.9513 14 15.35Z"
                                    fill="#374151"/>
                            </svg>
                        </div>

                        <!-- CONTENT -->
                        <div>
                            <p class="text-sm font-medium text-gray-700 leading-tight">
                                Ended Campaign
                            </p>

                            <p class="text-2xl font-semibold text-gray-900 leading-snug mt-1" id="endedCampaign">
                                0
                            </p>
                        </div>

                    </div>
            </div>
            <!-- BOOKED STATISTICS -->
            <div class="bg-white rounded-xl p-5 shadow mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold">Booked Statistics</h3>
                    <span class="text-xs text-gray-500">9–15 Sep, 2024</span>
                </div>
                <canvas id="bookingChart" height="90"></canvas>
            </div>

            <!-- ALL ENQUIRIES -->
            <div class="bg-white rounded-xl p-5 shadow">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold">All Enquiries</h3>
                    <div class="relative w-56">
                        <input
                            type="text"
                            placeholder="Search Campaign..."
                            class="w-full border border-gray-300 rounded-md pl-3 pr-9 py-1.5 text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-green-500"
                            onkeyup="filterEnquiries(this.value)"
                        >

                        <!-- SEARCH ICON -->
                        <div class="absolute inset-y-0 right-2 flex items-center pointer-events-none">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M21 21L16.65 16.65M19 11
                                        C19 15.4183 15.4183 19 11 19
                                        C6.58172 19 3 15.4183 3 11
                                        C3 6.58172 6.58172 3 11 3
                                        C15.4183 3 19 6.58172 19 11Z"
                                    stroke="#9CA3AF"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="space-y-3" id="enquiryList"></div>
            </div>
    </div>
@endsection
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        /* ===== DUMMY DYNAMIC DATA ===== */
        const dashboardData = {
            stats: {
                totalCampaign: 22000,
                activeCampaign: 15,
                liveCampaign: 2,
                endedCampaign: 0,
            },
            chart: [12, 14, 10, 8, 38, 22, 45, 32, 41, 44, 46, 47],
            enquiries: [
                {
                    title: "Bandra West Mumbai, 14360 Hoarding",
                    duration: "Jan 8, 2025 - Feb 8, 2025",
                    budget: "N/A",
                    spend: "₹50,000",
                    isNew: true
                },
                {
                    title: "Hazratganj Hoarding Limited",
                    duration: "Jan 8, 2025 - Feb 8, 2025",
                    budget: "₹5,00,000",
                    spend: "₹50,000",
                    isNew: false
                },
                {
                    title: "Hazratganj Hoarding Limited",
                    duration: "Jan 8, 2025 - Feb 8, 2025",
                    budget: "N/A",
                    spend: "₹50,000",
                    isNew: false
                }
            ]
        };

        /* ===== STATS FILL ===== */
        document.getElementById('totalCampaign').innerText = dashboardData.stats.totalCampaign.toLocaleString();
        document.getElementById('activeCampaign').innerText = dashboardData.stats.activeCampaign;
        document.getElementById('liveCampaign').innerText = dashboardData.stats.liveCampaign;
        document.getElementById('endedCampaign').innerText = dashboardData.stats.endedCampaign;

        /* ===== CHART ===== */
        new Chart(document.getElementById('bookingChart'), {
            type: 'line',
            data: {
                labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                datasets: [{
                    data: dashboardData.chart,
                    borderColor: '#ec4899',
                    tension: 0.4,
                    pointRadius: 3,
                    fill: false
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true },
                    x: { grid: { display: false } }
                }
            }
        });

        /* ===== ENQUIRIES RENDER ===== */
        function renderEnquiries(list){
            const container = document.getElementById('enquiryList');
            container.innerHTML = '';

            list.forEach(item => {
                container.innerHTML += `
                    <div class="border rounded-lg p-4 text-sm border-gray-300">
                        <p class="font-medium">
                            ${item.title}
                            ${item.isNew ? `<span class="ml-2 text-xs bg-orange-100 text-orange-600 px-2 py-0.5 rounded">New</span>` : ``}
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2 text-xs text-gray-500">
                            <div>
                                <p>Duration</p>
                                <p class="text-gray-700">${item.duration}</p>
                            </div>
                            <div>
                                <p>Your Budget</p>
                                <p class="text-gray-700">${item.budget}</p>
                            </div>
                            <div>
                                <p>Spend</p>
                                <p class="text-gray-700">${item.spend}</p>
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        /* ===== SEARCH FILTER ===== */
        function filterEnquiries(keyword){
            const filtered = dashboardData.enquiries.filter(e =>
                e.title.toLowerCase().includes(keyword.toLowerCase())
            );
            renderEnquiries(filtered);
        }

        /* INIT */
        renderEnquiries(dashboardData.enquiries);
    </script>
@endpush
