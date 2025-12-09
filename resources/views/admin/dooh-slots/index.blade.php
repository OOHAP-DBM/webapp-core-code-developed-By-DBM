@extends('layouts.admin')

@section('title', 'DOOH Slots - ' . $hoarding->title)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>DOOH Slots Management</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.hoardings.index') }}">Hoardings</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.hoardings.show', $hoarding->id) }}">{{ $hoarding->title }}</a></li>
                            <li class="breadcrumb-item active">DOOH Slots</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('admin.hoarding.dooh-slots.booking', $hoarding->id) }}" class="btn btn-success">
                        <i class="fas fa-calendar-check"></i> Book Slots
                    </a>
                    <a href="{{ route('admin.hoarding.dooh-slots.create', $hoarding->id) }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Slot
                    </a>
                    <form action="{{ route('admin.hoarding.dooh-slots.setup-defaults', $hoarding->id) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-magic"></i> Setup Defaults
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Total Slots</h5>
                    <h2>{{ $stats['total_slots'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Available</h5>
                    <h2>{{ $stats['available'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Booked</h5>
                    <h2>{{ $stats['booked'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>Occupancy Rate</h5>
                    <h2>{{ $stats['occupancy_rate'] }}%</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Slots List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Available Time Slots</h4>
                </div>
                <div class="card-body">
                    @if($slots->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Slot Name</th>
                                        <th>Time Range</th>
                                        <th>Frequency</th>
                                        <th>Daily Displays</th>
                                        <th>Daily Cost</th>
                                        <th>Monthly Cost</th>
                                        <th>Status</th>
                                        <th>Prime Time</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($slots as $slot)
                                        <tr>
                                            <td>{{ $slot->id }}</td>
                                            <td>{{ $slot->slot_name ?? 'Slot #' . $slot->id }}</td>
                                            <td>{{ $slot->time_range }}</td>
                                            <td>
                                                {{ $slot->frequency_per_hour }} times/hour
                                                <br>
                                                <small class="text-muted">Every {{ round($slot->interval_seconds / 60, 1) }} min</small>
                                            </td>
                                            <td>{{ number_format($slot->total_daily_displays) }}</td>
                                            <td>₹{{ number_format($slot->daily_cost, 2) }}</td>
                                            <td>₹{{ number_format($slot->monthly_cost, 2) }}</td>
                                            <td>
                                                <span class="badge badge-{{ $slot->status_color }}">
                                                    {{ $slot->status_label }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($slot->is_prime_time)
                                                    <span class="badge badge-warning">Prime</span>
                                                @else
                                                    <span class="badge badge-secondary">Regular</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.dooh-slots.show', $slot->id) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.dooh-slots.edit', $slot->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($slot->status === 'booked')
                                                    <form action="{{ route('admin.dooh-slots.release', $slot->id) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Release this slot?')">
                                                            <i class="fas fa-unlock"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                @if($slot->status !== 'booked')
                                                    <form action="{{ route('admin.dooh-slots.destroy', $slot->id) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this slot?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        {{ $slots->links() }}
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No DOOH slots configured for this hoarding yet.
                            <a href="{{ route('admin.hoarding.dooh-slots.create', $hoarding->id) }}">Add your first slot</a> or 
                            <a href="#" onclick="event.preventDefault(); document.querySelector('form[action*=setup-defaults]').submit();">setup default slots</a>.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
