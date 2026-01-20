@extends('layouts.admin')
@section('title', 'Hoarding Preview - ' . $hoarding->title)

@section('content')
<div class="px-6 py-6 max-w-7xl">
    {{-- Header with Back Button --}}
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ url()->previous() }}" class="text-gray-600 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Hoarding Preview</h1>
        </div>
        
        {{-- Status Badge --}}
        <div class="flex items-center gap-3">
            @php
                $statusColors = [
                    'active' => 'bg-green-100 text-green-800',
                    'pending_approval' => 'bg-yellow-100 text-yellow-800',
                    'draft' => 'bg-gray-100 text-gray-800',
                    'inactive' => 'bg-red-100 text-red-800',
                    'suspended' => 'bg-orange-100 text-orange-800',
                ];
                $statusClass = $statusColors[$hoarding->status] ?? 'bg-gray-100 text-gray-800';
            @endphp
            <span class="px-4 py-2 rounded-full text-sm font-semibold {{ $statusClass }}">
                {{ ucwords(str_replace('_', ' ', $hoarding->status)) }}
            </span>
            
            @if($hoarding->is_featured)
                <span class="px-4 py-2 rounded-full text-sm font-semibold bg-purple-100 text-purple-800">
                    Featured
                </span>
            @endif
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- LEFT COLUMN (2/3 width) - Main Details --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Media Gallery Section --}}
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Media Gallery
                        <span class="text-sm text-gray-500 font-normal">({{ $hoarding->media_files->count() }} files)</span>
                    </h2>
                    
                    {{-- Main Image Display --}}
                    @if($hoarding->media_files->isNotEmpty())
                        <div class="mb-4">
                            <img 
                                id="mainPreviewImage" 
                                src="{{ $hoarding->media_files->where('is_primary', true)->first()['url'] ?? $hoarding->media_files->first()['url'] }}" 
                                alt="{{ $hoarding->title }}"
                                class="w-full h-96 object-cover rounded-lg border border-gray-200"
                            >
                        </div>
                        
                        {{-- Thumbnail Gallery --}}
                        @if($hoarding->media_files->count() > 1)
                            <div class="grid grid-cols-4 gap-3">
                                @foreach($hoarding->media_files as $index => $media)
                                    <img 
                                        src="{{ $media['url'] }}" 
                                        alt="Thumbnail {{ $index + 1 }}"
                                        class="w-full h-24 object-cover rounded-lg border-2 cursor-pointer hover:border-green-500 transition {{ $loop->first ? 'border-green-500' : 'border-gray-200' }}"
                                        onclick="document.getElementById('mainPreviewImage').src = '{{ $media['url'] }}'; event.target.parentElement.querySelectorAll('img').forEach(img => img.classList.remove('border-green-500')); event.target.classList.add('border-green-500');"
                                    >
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="flex items-center justify-center h-96 bg-gray-100 rounded-lg">
                            <span class="text-gray-400">No media available</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Basic Information --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Basic Information
                </h2>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase">Hoarding Title</label>
                        <p class="text-sm text-gray-900 mt-1">{{ $hoarding->title ?? '—' }}</p>
                    </div>
                    
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase">Hoarding Type</label>
                        <p class="text-sm text-gray-900 mt-1">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $hoarding->hoarding_type === 'dooh' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                {{ strtoupper($hoarding->hoarding_type) }}
                            </span>
                        </p>
                    </div>
                    
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase">Category</label>
                        <p class="text-sm text-gray-900 mt-1">{{ $hoarding->category ?? '—' }}</p>
                    </div>
                    
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase">View Count</label>
                        <p class="text-sm text-gray-900 mt-1">{{ number_format($hoarding->view_count ?? 0) }} views</p>
                    </div>
                    
                    <div class="col-span-2">
                        <label class="text-xs font-semibold text-gray-500 uppercase">Description</label>
                        <p class="text-sm text-gray-700 mt-1">{{ $hoarding->description ?? 'No description provided' }}</p>
                    </div>
                </div>
            </div>

            {{-- Location Details --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Location Details
                </h2>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="text-xs font-semibold text-gray-500 uppercase">Address</label>
                        <p class="text-sm text-gray-900 mt-1">{{ $hoarding->address ?? '—' }}</p>
                    </div>
                    
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase">City</label>
                        <p class="text-sm text-gray-900 mt-1">{{ $hoarding->city ?? '—' }}</p>
                    </div>
                    
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase">State</label>
                        <p class="text-sm text-gray-900 mt-1">{{ $hoarding->state ?? '—' }}</p>
                    </div>
                    
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase">Pincode</label>
                        <p class="text-sm text-gray-900 mt-1">{{ $hoarding->pincode ?? '—' }}</p>
                    </div>
                    
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase">Landmark</label>
                        <p class="text-sm text-gray-900 mt-1">{{ $hoarding->landmark ?? '—' }}</p>
                    </div>
                    
                    @if($hoarding->latitude && $hoarding->longitude)
                        <div class="col-span-2">
                            <label class="text-xs font-semibold text-gray-500 uppercase">Coordinates</label>
                            <p class="text-sm text-gray-900 mt-1">
                                {{ $hoarding->latitude }}, {{ $hoarding->longitude }}
                                @if($hoarding->geolocation_verified)
                                    <span class="ml-2 text-xs text-green-600">✓ Verified</span>
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Physical Specifications --}}
            @if($hoarding->dimensions)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                        </svg>
                        Dimensions & Specifications
                    </h2>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase">Width</label>
                            <p class="text-sm text-gray-900 mt-1">{{ $hoarding->dimensions['width'] ?? '—' }} {{ $hoarding->dimensions['unit'] ?? '' }}</p>
                        </div>
                        
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase">Height</label>
                            <p class="text-sm text-gray-900 mt-1">{{ $hoarding->dimensions['height'] ?? '—' }} {{ $hoarding->dimensions['unit'] ?? '' }}</p>
                        </div>
                        
                        @if(isset($hoarding->dimensions['area_sqft']))
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase">Area</label>
                                <p class="text-sm text-gray-900 mt-1">{{ number_format($hoarding->dimensions['area_sqft'], 2) }} sq ft</p>
                            </div>
                        @endif
                        
                        @if(isset($hoarding->dimensions['screen_size']))
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase">Screen Size</label>
                                <p class="text-sm text-gray-900 mt-1">{{ $hoarding->dimensions['screen_size'] }}</p>
                            </div>
                        @endif
                        
                        @if($hoarding->facing_direction)
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase">Facing Direction</label>
                                <p class="text-sm text-gray-900 mt-1">{{ $hoarding->facing_direction }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Technical Specifications (Type-specific) --}}
            @if($hoarding->technical_specs)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                        </svg>
                        Technical Specifications
                    </h2>
                    
                    <div class="grid grid-cols-2 gap-4">
                        @if($hoarding->hoarding_type === 'dooh')
                            {{-- DOOH Specifications --}}
                            @if(isset($hoarding->technical_specs['screen_type']))
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Screen Type</label>
                                    <p class="text-sm text-gray-900 mt-1">{{ $hoarding->technical_specs['screen_type'] }}</p>
                                </div>
                            @endif
                            
                            @if(isset($hoarding->technical_specs['resolution_width']) && isset($hoarding->technical_specs['resolution_height']))
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Resolution</label>
                                    <p class="text-sm text-gray-900 mt-1">
                                        {{ $hoarding->technical_specs['resolution_width'] }} x {{ $hoarding->technical_specs['resolution_height'] }}
                                    </p>
                                </div>
                            @endif
                            
                            @if(isset($hoarding->technical_specs['slot_duration_seconds']))
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Slot Duration</label>
                                    <p class="text-sm text-gray-900 mt-1">{{ $hoarding->technical_specs['slot_duration_seconds'] }} seconds</p>
                                </div>
                            @endif
                            
                            @if(isset($hoarding->technical_specs['loop_duration_seconds']))
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Loop Duration</label>
                                    <p class="text-sm text-gray-900 mt-1">{{ round($hoarding->technical_specs['loop_duration_seconds'] / 60, 1) }} minutes</p>
                                </div>
                            @endif
                            
                            @if(isset($hoarding->technical_specs['slots_per_loop']))
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Slots Per Loop</label>
                                    <p class="text-sm text-gray-900 mt-1">{{ $hoarding->technical_specs['slots_per_loop'] }} slots</p>
                                </div>
                            @endif
                            
                            @if(isset($hoarding->technical_specs['total_slots_per_day']))
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Total Slots/Day</label>
                                    <p class="text-sm text-gray-900 mt-1">{{ number_format($hoarding->technical_specs['total_slots_per_day']) }} slots</p>
                                </div>
                            @endif
                            
                            @if(isset($hoarding->technical_specs['available_slots_per_day']))
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Available Slots/Day</label>
                                    <p class="text-sm text-gray-900 mt-1">{{ number_format($hoarding->technical_specs['available_slots_per_day']) }} slots</p>
                                </div>
                            @endif
                            
                            @if(isset($hoarding->technical_specs['max_file_size_mb']))
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Max File Size</label>
                                    <p class="text-sm text-gray-900 mt-1">{{ $hoarding->technical_specs['max_file_size_mb'] }} MB</p>
                                </div>
                            @endif
                            
                            @if(isset($hoarding->technical_specs['video_length']))
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Video Length</label>
                                    <p class="text-sm text-gray-900 mt-1">{{ $hoarding->technical_specs['video_length'] }}</p>
                                </div>
                            @endif
                            
                            @if(isset($hoarding->technical_specs['allowed_formats']) && is_array($hoarding->technical_specs['allowed_formats']))
                                <div class="col-span-2">
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Allowed Formats</label>
                                    <p class="text-sm text-gray-900 mt-1">
                                        @foreach($hoarding->technical_specs['allowed_formats'] as $format)
                                            <span class="inline-block px-2 py-1 bg-gray-100 rounded text-xs mr-2 mb-1">{{ $format }}</span>
                                        @endforeach
                                    </p>
                                </div>
                            @endif
                        @else
                            {{-- OOH Specifications --}}
                            @if(isset($hoarding->technical_specs['lighting_type']))
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Lighting Type</label>
                                    <p class="text-sm text-gray-900 mt-1">{{ ucfirst($hoarding->technical_specs['lighting_type']) }}</p>
                                </div>
                            @endif
                            
                            @if(isset($hoarding->technical_specs['material_type']))
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Material Type</label>
                                    <p class="text-sm text-gray-900 mt-1">{{ $hoarding->technical_specs['material_type'] }}</p>
                                </div>
                            @endif
                            
                            @if(isset($hoarding->technical_specs['mounting_type']))
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Mounting Type</label>
                                    <p class="text-sm text-gray-900 mt-1">{{ $hoarding->technical_specs['mounting_type'] }}</p>
                                </div>
                            @endif
                        @endif
                        
                        @if($hoarding->road_type)
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase">Road Type</label>
                                <p class="text-sm text-gray-900 mt-1">{{ $hoarding->road_type }}</p>
                            </div>
                        @endif
                        
                        @if($hoarding->traffic_type)
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase">Traffic Type</label>
                                <p class="text-sm text-gray-900 mt-1">{{ $hoarding->traffic_type }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Visibility & Audience --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Visibility & Audience
                </h2>
                
                <div class="grid grid-cols-2 gap-4">
                    @if($hoarding->visibility_start && $hoarding->visibility_end)
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase">Visibility Hours</label>
                            <p class="text-sm text-gray-900 mt-1">{{ $hoarding->visibility_start }} - {{ $hoarding->visibility_end }}</p>
                        </div>
                    @endif
                    
                    @if($hoarding->hoarding_visibility)
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase">Visibility Level</label>
                            <p class="text-sm text-gray-900 mt-1">{{ ucfirst($hoarding->hoarding_visibility) }}</p>
                        </div>
                    @endif
                    
                    @if($hoarding->expected_footfall)
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase">Expected Footfall</label>
                            <p class="text-sm text-gray-900 mt-1">{{ number_format($hoarding->expected_footfall) }}/day</p>
                        </div>
                    @endif
                    
                    @if($hoarding->expected_eyeball)
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase">Expected Eyeball</label>
                            <p class="text-sm text-gray-900 mt-1">{{ number_format($hoarding->expected_eyeball) }}/day</p>
                        </div>
                    @endif
                    
                    @if($hoarding->audience_types && is_array($hoarding->audience_types) && count($hoarding->audience_types) > 0)
                        <div class="col-span-2">
                            <label class="text-xs font-semibold text-gray-500 uppercase">Target Audience</label>
                            <p class="text-sm text-gray-900 mt-1">
                                @foreach($hoarding->audience_types as $audience)
                                    <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs mr-2 mb-1">{{ $audience }}</span>
                                @endforeach
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Packages & Offers --}}
            @if($hoarding->packages && $hoarding->packages->isNotEmpty())
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        Available Packages
                        <span class="text-sm text-gray-500 font-normal">({{ $hoarding->packages->count() }})</span>
                    </h2>
                    
                    <div class="space-y-3">
                        @foreach($hoarding->packages as $package)
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-green-500 transition">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h3 class="font-semibold text-gray-900">{{ $package->package_name ?? 'Package ' . $loop->iteration }}</h3>
                                        @if($package->description)
                                            <p class="text-xs text-gray-600 mt-1">{{ $package->description }}</p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        @if($hoarding->hoarding_type === 'dooh')
                                            <p class="text-lg font-bold text-green-600">
                                                {{ $package->slots_per_month ?? '—' }} slots/month
                                            </p>
                                            @if($package->price_per_slot)
                                                <p class="text-xs text-gray-500">₹{{ number_format($package->price_per_slot, 2) }}/slot</p>
                                            @endif
                                        @else
                                            <p class="text-lg font-bold text-green-600">
                                                ₹{{ number_format($package->base_monthly_price ?? 0, 2) }}
                                            </p>
                                            <p class="text-xs text-gray-500">/month</p>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-3 gap-2 text-xs text-gray-600 mt-3">
                                    @if($package->min_booking_duration)
                                        <div>
                                            <span class="font-semibold">Min Duration:</span> {{ $package->min_booking_duration }} months
                                        </div>
                                    @endif
                                    @if($package->discount_percent)
                                        <div>
                                            <span class="font-semibold">Discount:</span> {{ $package->discount_percent }}%
                                        </div>
                                    @endif
                                    @if($package->valid_till)
                                        <div>
                                            <span class="font-semibold">Valid Till:</span> {{ \Carbon\Carbon::parse($package->valid_till)->format('d M Y') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Legal & Permits --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Legal & Permits
                </h2>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase">Nagar Nigam Approval</label>
                        <p class="text-sm mt-1">
                            @if($hoarding->nagar_nigam_approved)
                                <span class="text-green-600 font-semibold">✓ Approved</span>
                            @else
                                <span class="text-red-600 font-semibold">✗ Not Approved</span>
                            @endif
                        </p>
                    </div>
                    
                    @if($hoarding->permit_number)
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase">Permit Number</label>
                            <p class="text-sm text-gray-900 mt-1">{{ $hoarding->permit_number }}</p>
                        </div>
                    @endif
                    
                    @if($hoarding->permit_valid_till)
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase">Permit Valid Till</label>
                            <p class="text-sm text-gray-900 mt-1">
                                {{ \Carbon\Carbon::parse($hoarding->permit_valid_till)->format('d M Y') }}
                                @if(\Carbon\Carbon::parse($hoarding->permit_valid_till)->isPast())
                                    <span class="text-red-600 text-xs ml-2">Expired</span>
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- RIGHT COLUMN (1/3 width) - Sidebar Info --}}
        <div class="lg:col-span-1 space-y-6">
            
            {{-- Pricing Summary --}}
            <div class="bg-white rounded-lg shadow-md p-6 sticky top-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Pricing Summary</h2>
                
                @if($hoarding->hoarding_type === 'dooh')
                    {{-- DOOH Pricing --}}
                    @if($hoarding->price_per_slot)
                        <div class="mb-3 pb-3 border-b border-gray-200">
                            <label class="text-xs font-semibold text-gray-500 uppercase">Base Price (Per Slot)</label>
                            <p class="text-2xl font-bold text-green-600 mt-1">₹{{ number_format($hoarding->price_per_slot, 2) }}</p>
                        </div>
                    @endif
                    
                    @if($hoarding->price_per_slot)
                        <div class="mb-2">
                            <label class="text-xs text-gray-500">second </label>
                            <p class="text-sm font-semibold text-gray-900">₹{{ number_format($hoarding->price_per_10_sec, 2) }}</p>
                        </div>
                    @endif
                    
                    @if($hoarding->price_per_30_sec)
                        <div class="mb-2">
                            <label class="text-xs text-gray-500">30-second slot</label>
                            <p class="text-sm font-semibold text-gray-900">₹{{ number_format($hoarding->price_per_30_sec, 2) }}</p>
                        </div>
                    @endif
                    
                    @if($hoarding->minimum_booking_amount)
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <label class="text-xs text-gray-500">Minimum Booking Amount</label>
                            <p class="text-sm font-semibold text-gray-900">₹{{ number_format($hoarding->minimum_booking_amount, 2) }}</p>
                        </div>
                    @endif
                @else
                    {{-- OOH Pricing --}}
                    @if($hoarding->monthly_price)
                        <div class="mb-3 pb-3 border-b border-gray-200">
                            <label class="text-xs font-semibold text-gray-500 uppercase">Monthly Price</label>
                            <p class="text-2xl font-bold text-green-600 mt-1">₹{{ number_format($hoarding->monthly_price, 2) }}</p>
                        </div>
                    @endif
                    
                    @if($hoarding->base_monthly_price && $hoarding->base_monthly_price != $hoarding->monthly_price)
                        <div class="mb-2">
                            <label class="text-xs text-gray-500">Base Monthly Price</label>
                            <p class="text-sm text-gray-500 line-through">₹{{ number_format($hoarding->base_monthly_price, 2) }}</p>
                        </div>
                    @endif
                    
                    @if($hoarding->supports_weekly && $hoarding->weekly_price)
                        <div class="mb-2">
                            <label class="text-xs text-gray-500">Weekly Price</label>
                            <p class="text-sm font-semibold text-gray-900">₹{{ number_format($hoarding->weekly_price, 2) }}</p>
                        </div>
                    @endif
                @endif
                
                {{-- Additional Charges --}}
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Additional Charges</h3>
                    
                    @if($hoarding->hoarding_type === 'ooh')
                        @if(isset($hoarding->printing_charge) && $hoarding->printing_charge > 0)
                            <div class="flex justify-between text-xs mb-2">
                                <span class="text-gray-600">
                                    Printing 
                                    @if($hoarding->printing_included)
                                        <span class="text-green-600">(Included)</span>
                                    @endif
                                </span>
                                <span class="font-semibold">₹{{ number_format($hoarding->printing_charge, 2) }}</span>
                            </div>
                        @endif
                        
                        @if(isset($hoarding->mounting_charge) && $hoarding->mounting_charge > 0)
                            <div class="flex justify-between text-xs mb-2">
                                <span class="text-gray-600">
                                    Mounting 
                                    @if($hoarding->mounting_included)
                                        <span class="text-green-600">(Included)</span>
                                    @endif
                                </span>
                                <span class="font-semibold">₹{{ number_format($hoarding->mounting_charge, 2) }}</span>
                            </div>
                        @endif
                        
                        @if(isset($hoarding->lighting_charge) && $hoarding->lighting_charge > 0)
                            <div class="flex justify-between text-xs mb-2">
                                <span class="text-gray-600">
                                    Lighting 
                                    @if($hoarding->lighting_included)
                                        <span class="text-green-600">(Included)</span>
                                    @endif
                                </span>
                                <span class="font-semibold">₹{{ number_format($hoarding->lighting_charge, 2) }}</span>
                            </div>
                        @endif
                    @endif
                    
                    @if(isset($hoarding->graphics_charge) && $hoarding->graphics_charge > 0)
                        <div class="flex justify-between text-xs mb-2">
                            <span class="text-gray-600">
                                Graphics 
                                @if($hoarding->graphics_included)
                                    <span class="text-green-600">(Included)</span>
                                @endif
                            </span>
                            <span class="font-semibold">₹{{ number_format($hoarding->graphics_charge, 2) }}</span>
                        </div>
                    @endif
                    
                    @if($hoarding->survey_charge && $hoarding->survey_charge > 0)
                        <div class="flex justify-between text-xs mb-2">
                            <span class="text-gray-600">Survey</span>
                            <span class="font-semibold">₹{{ number_format($hoarding->survey_charge, 2) }}</span>
                        </div>
                    @endif
                </div>
                
                {{-- Commission --}}
                @if($hoarding->commission_percent)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Platform Commission</span>
                            <span class="font-semibold text-gray-900">{{ $hoarding->commission_percent }}%</span>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Vendor Information --}}
            @if($hoarding->vendor)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Vendor Information</h2>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase">Vendor Name</label>
                            <p class="text-sm text-gray-900 mt-1">{{ $hoarding->vendor->name }}</p>
                        </div>
                        
                        @if($hoarding->vendor->email)
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase">Email</label>
                                <p class="text-sm text-gray-900 mt-1">{{ $hoarding->vendor->email }}</p>
                            </div>
                        @endif
                        
                        @if($hoarding->vendor->phone)
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase">Phone</label>
                                <p class="text-sm text-gray-900 mt-1">{{ $hoarding->vendor->phone }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Booking Rules --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Booking Rules</h2>
                
                <div class="space-y-3">
                    @if($hoarding->min_booking_duration)
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase">Min Booking Duration</label>
                            <p class="text-sm text-gray-900 mt-1">{{ $hoarding->min_booking_duration }} months</p>
                        </div>
                    @endif
                    
                    @if($hoarding->max_booking_months)
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase">Max Booking Duration</label>
                            <p class="text-sm text-gray-900 mt-1">{{ $hoarding->max_booking_months }} months</p>
                        </div>
                    @endif
                    
                    @if($hoarding->grace_period_days)
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase">Grace Period</label>
                            <p class="text-sm text-gray-900 mt-1">{{ $hoarding->grace_period_days }} days</p>
                        </div>
                    @endif
                    
                    @if($hoarding->available_from)
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase">Available From</label>
                            <p class="text-sm text-gray-900 mt-1">{{ \Carbon\Carbon::parse($hoarding->available_from)->format('d M Y') }}</p>
                        </div>
                    @endif
                    
                    @if($hoarding->available_to)
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase">Available To</label>
                            <p class="text-sm text-gray-900 mt-1">{{ \Carbon\Carbon::parse($hoarding->available_to)->format('d M Y') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Meta Information --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Meta Information</h2>
                
                <div class="space-y-3 text-xs text-gray-600">
                    <div>
                        <label class="font-semibold text-gray-500 uppercase">Created At</label>
                        <p class="text-gray-900 mt-1">{{ $hoarding->created_at->format('d M Y, h:i A') }}</p>
                    </div>
                    
                    <div>
                        <label class="font-semibold text-gray-500 uppercase">Last Updated</label>
                        <p class="text-gray-900 mt-1">{{ $hoarding->updated_at->format('d M Y, h:i A') }}</p>
                    </div>
                    
                    @if($hoarding->bookings_count > 0)
                        <div>
                            <label class="font-semibold text-gray-500 uppercase">Total Bookings</label>
                            <p class="text-gray-900 mt-1">{{ $hoarding->bookings_count }}</p>
                        </div>
                    @endif
                    
                    @if($hoarding->last_booked_at)
                        <div>
                            <label class="font-semibold text-gray-500 uppercase">Last Booked</label>
                            <p class="text-gray-900 mt-1">{{ \Carbon\Carbon::parse($hoarding->last_booked_at)->format('d M Y') }}</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Image gallery functionality is inline in the HTML above
    console.log('Admin Hoarding Preview - ID: {{ $hoarding->id }}');
</script>
@endpush
