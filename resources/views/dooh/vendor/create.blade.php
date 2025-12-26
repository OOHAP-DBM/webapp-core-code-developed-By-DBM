
@extends('layouts.vendor')

@section('title', 'Add DOOH Hoarding')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        {{-- <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h4 mb-0 font-weight-bold">Add New DOOH Screen</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 bg-transparent p-0">
                        <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                        <li class="breadcrumb-item active">Add DOOH</li>
                    </ol>
                </nav>
            </div> --}}

            <!-- Stepper -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center stepper">
                    <div class="step {{ $step == 1 ? 'active' : ($step > 1 ? 'completed' : '') }}">
                        <div class="circle">1</div>
                        <div class="label">Step 1</div>
                    </div>
                    <div class="step-line"></div>
                    <div class="step {{ $step == 2 ? 'active' : ($step > 2 ? 'completed' : '') }}">
                        <div class="circle">2</div>
                        <div class="label">Step 2</div>
                    </div>
                    <div class="step-line"></div>
                    <div class="step {{ $step == 3 ? 'active' : '' }}">
                        <div class="circle">3</div>
                        <div class="label">Step 3</div>
                    </div>
                </div>
            </div>

            <form action="{{ route('vendor.dooh.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="step" value="{{ $step }}">

                @if($step == 1)
                    @include('dooh.vendor.partials.step1', ['draft' => $draft])
                @elseif($step == 2)
                    @include('dooh.vendor.partials.step2', ['draft' => $draft])
                @elseif($step == 3)
                    @include('dooh.vendor.partials.step3', ['draft' => $draft])
                @endif

                <div class="d-flex justify-content-end gap-2 mb-5">
                    <a href="{{ route('hoardings.index') }}" class="btn btn-light px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-5 shadow-sm">
                        @if($step < 3)
                            Next
                        @else
                            Save Draft
                        @endif
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .stepper { gap: 0.5rem; }
    .step { display: flex; flex-direction: column; align-items: center; min-width: 80px; }
    .circle {
        width: 32px; height: 32px; border-radius: 50%;
        background: #fff; border: 2px solid #C8C8C8; color: #C8C8C8;
        display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 1.1rem;
        margin-bottom: 4px;
        transition: all 0.2s;
    }
    .step.active .circle {
        border-color: #009A5C; color: #009A5C; background: #E6F7F0;
    }
    .step.completed .circle {
        border-color: #009A5C; color: #fff; background: #009A5C;
    }
    .label { font-size: 0.95rem; color: #7D7D7D; font-weight: 500; }
    .step-line {
        flex: 1 1 0%; height: 2px; background: #C8C8C8; margin: 0 0.5rem;
    }
    .step.completed ~ .step-line { background: #009A5C; }
    .form-label { font-weight: 500; color: #4b5563; font-size: 0.9rem; }
    .form-control:focus { border-color: #6366f1; box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.15); }
    .card { border-radius: 12px; }
    .btn-primary { background-color: #009A5C; border: none; }
</style>
@endsection