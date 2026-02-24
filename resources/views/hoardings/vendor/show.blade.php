@extends('layouts.vendor')
@section('title', 'Preview: ' . \Illuminate\Support\Str::limit($hoarding->title, 30))

@section('content')
<div class="hoarding-wrapper">
   <!-- <a href="{{ url()->previous() == url()->current() ? '#' : url()->previous() }}" 
       onclick="if(window.history.length > 1) { window.history.back(); return false; }"
       class="inline-flex items-center gap-2 mb-6 text-sm font-semibold text-gray-500 hover:text-blue-600 transition-colors group">
        <span class="transform group-hover:-translate-x-1 transition-transform">←</span>
        Back to Previous
    </a> -->
    {{-- Tabs --}}
    <div class="mb-6  border-gray-200 flex items-center justify-between">
        <ul class="flex -mb-px text-sm" id="hoardingTabs">
            <li>
                <button class="tab-btn px-4 py-2 border-b-2 font-semibold text-gray-700 border-transparent hover:text-green-600 hover:border-green-600 focus:outline-none" data-tab="general" id="tabGeneralBtn">General</button>
            </li>
            <li>
                <button class="tab-btn px-4 py-2 border-b-2 font-semibold text-gray-700 border-transparent hover:text-green-600 hover:border-green-600 focus:outline-none" data-tab="review" id="tabReviewBtn">Review</button>
            </li>
        </ul>
        <a href="{{ route('vendor.hoardings.edit', $hoarding['id']) }}" class="ml-auto px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">Edit Details</a>    </div>
       
        <div id="tabGeneral" class="tab-content">
            @include('hoardings.vendor.hoarding-preview.media')
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                <div class="lg:col-span-12 space-y-6 bg-white">
                    @include('hoardings.vendor.hoarding-preview.hoarding-details')
                    @include('hoardings.vendor.hoarding-preview.hoarding-location')
                    @include('hoardings.vendor.hoarding-preview.hoarding-attribute')
                    @include('hoardings.vendor.hoarding-preview.hoarding-price')
                    @include('hoardings.vendor.hoarding-preview.price-summery')
                    @include('hoardings.vendor.hoarding-preview.long-term-offer')
                </div>
            </div>
        </div>

        <div id="tabReview" class="tab-content hidden">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                <div class="lg:col-span-12 space-y-6">
                    @include('hoardings.vendor.hoarding-preview.review')
                </div>
            </div>
        </div>
</div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabGeneralBtn = document.getElementById('tabGeneralBtn');
        const tabReviewBtn = document.getElementById('tabReviewBtn');
        const tabGeneral = document.getElementById('tabGeneral');
        const tabReview = document.getElementById('tabReview');
        function activateTab(tab) {
            if (tab === 'general') {
                tabGeneral.classList.remove('hidden');
                tabReview.classList.add('hidden');
                tabGeneralBtn.classList.add('border-green-600', 'text-green-600');
                tabReviewBtn.classList.remove('border-green-600', 'text-green-600');
            } else {
                tabGeneral.classList.add('hidden');
                tabReview.classList.remove('hidden');
                tabReviewBtn.classList.add('border-green-600', 'text-green-600');
                tabGeneralBtn.classList.remove('border-green-600', 'text-green-600');
            }
        }
        tabGeneralBtn.addEventListener('click', function() { activateTab('general'); });
        tabReviewBtn.addEventListener('click', function() { activateTab('review'); });
        // Default to General tab
        activateTab('general');
    });
</script>
@endpush
@endsection