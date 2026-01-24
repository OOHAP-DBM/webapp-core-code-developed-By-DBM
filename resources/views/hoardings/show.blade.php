@extends('layouts.app')
@section('title', $hoarding->title)

@section('content')
@include('components.customer.navbar')
<style>
    

    /* ===== WRAPPER ===== */
    #hoarding-wrapper{
        max-width:1500px;
        margin:auto;
        padding:24px 16px;
    }

    /* ===== GALLERY ===== */
    .main-image{
        height:420px;
        object-fit:cover;
        border-radius:12px;
    }
    .thumb-image{
        height:200px;
        object-fit:cover;
        border-radius:10px;
        cursor:pointer;
        border:2px solid transparent;
    }
    .thumb-image.active{
        border-color:#20c997;
    }

    /* ===== INFO ===== */
    .offer-badge{
        background:#ff4d4f;
        color:#fff;
        font-size:12px;
        padding:6px 12px;
        border-radius:6px;
        font-weight:600;
    }
    .rating-badge{
        background:#f1f3f5;
        padding:4px 10px;
        border-radius:6px;
        font-size:13px;
    }

    /* ===== PRICING ===== */
    .price-box{
        border:1px solid #e9ecef;
        border-radius:14px;
        padding:16px;
    }
    .offer-row{
        border:1px solid #dee2e6;
        border-radius:10px;
        padding:14px;
        margin-bottom:12px;
        cursor:pointer;
    }
    .offer-row.active{
        border-color:#20c997;
        background:#f3fffb;
    }
    .save{
        background:#e6fcf5;
        color:#0ca678;
        font-size:11px;
        padding:4px 10px;
        border-radius:20px;
        font-weight:600;
    }
    .old-price{
        text-decoration:line-through;
        color:#adb5bd;
        font-size:13px;
    }
    .book-btn{
        background:#20c997;
        color:#fff;
        border:none;
        padding:12px;
        border-radius:8px;
        font-weight:600;
    }
    .book-btn:hover{ background:#12b886; }

    /* ===== MOBILE ===== */
    @media(max-width:768px){
        .main-image{ height:260px; }
        .thumb-image{ height:140px; }
    }
.package-card {
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 0.75rem;
    margin-top: 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.package-card:hover {
    border-color: #14b8a6;
}

.package-card.active {
    border-color: #14b8a6;
    background-color: #f0fdfa;
}

</style>
<div id="hoarding-wrapper">

    @include('hoardings.partials.gallery')

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

        {{-- LEFT --}}
        <div class="lg:col-span-8 space-y-6">

            @include('hoardings.partials.basic-info')
            @include('hoardings.partials.details')
            @include('hoardings.partials.gazeflow')
            @include('hoardings.partials.audience')
            @include('hoardings.partials.location')
            @include('hoardings.partials.attributes')
            @include('hoardings.partials.reviews')

        </div>

        {{-- RIGHT --}}
        <div class="lg:col-span-4">
            @include('hoardings.partials.price-box')
        </div>

    </div>
</div>

@endsection

@push('scripts')

   <script>
        window.allPackages = @json(
            $hoarding->packages->map(fn($p)=>[
                'id'   => $p->id,
                'name' => $p->package_name,
                'price'=> $hoarding->hoarding_type === 'ooh'
                    ? $p->base_monthly_price * $p->min_booking_duration
                    : $p->slots_per_month
            ])
        );
        console.log('[DEBUG] All Packages Data:', window.allPackages);
    </script>

    {{-- ENQUIRY MODAL JS --}}

@endpush