@extends('layouts.admin')

@section('title', 'Commission Rules')

@section('content')
<style>
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .stat-card h3 {
        font-size: 2.5rem;
        font-weight: bold;
        margin: 0;
    }
    .stat-card p {
        margin: 5px 0 0 0;
        opacity: 0.9;
    }
    .rule-card {
        transition: all 0.3s;
        border-left: 4px solid;
    }
    .rule-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .rule-card.vendor { border-left-color: #667eea; }
    .rule-card.hoarding { border-left-color: #f093fb; }
    .rule-card.location { border-left-color: #4facfe; }
    .rule-card.flat { border-left-color: #43e97b; }
    .rule-card.time_based { border-left-color: #fa709a; }
    .rule-card.seasonal { border-left-color: #feca57; }
</style>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-diagram-3 me-2"></i>Commission Rules Engine</h2>
            <p class="text-muted mb-0">Manage flexible commission rules for revenue sharing and booking distribution</p>
        </div>
        <a href="{{ route('admin.commission-rules.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>New Rule
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h3>{{ $statistics['total_rules'] }}</h3>
                <p>Total Rules</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <h3>{{ $statistics['active_rules'] }}</h3>
                <p>Active Rules</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <h3>{{ $statistics['active_seasonal'] }}</h3>
                <p>Seasonal Offers</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <h3>{{ $statistics['most_used_rule']->usage_count ?? 0 }}</h3>
                <p>Top Rule Usage</p>
            </div>
        </div>
    </div>

    <!-- Active Seasonal Offers -->
    @if($seasonalOffers->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Active Seasonal Offers</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($seasonalOffers as $offer)
                <div class="col-md-6 mb-3">
                    <div class="border rounded p-3 bg-light">
                        <h6 class="text-primary">{{ $offer->season_name }}</h6>
                        <p class="mb-1"><strong>{{ $offer->name }}</strong></p>
                        <small class="text-muted">
                            {{ $offer->valid_from?->format('M d') }} - {{ $offer->valid_to?->format('M d, Y') }}
                            | Commission: {{ $offer->commission_type === 'percentage' ? $offer->commission_value . '%' : '₹' . $offer->commission_value }}
                        </small>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Rules Table -->
    <div class="card shadow">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">All Commission Rules</h5>
        </div>
        <div class="card-body p-0">
            @if($rules->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #dee2e6;"></i>
                <p class="text-muted mt-3">No commission rules found. Create your first rule to get started.</p>
                <a href="{{ route('admin.commission-rules.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>Create Rule
                </a>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Priority</th>
                            <th>Rule Name</th>
                            <th>Type</th>
                            <th>Scope</th>
                            <th>Commission</th>
                            <th>Valid Period</th>
                            <th>Usage</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rules as $rule)
                        <tr class="rule-card {{ $rule->rule_type }}">
                            <td><span class="badge bg-dark">{{ $rule->priority }}</span></td>
                            <td>
                                <strong>{{ $rule->name }}</strong>
                                @if($rule->description)
                                <br><small class="text-muted">{{ Str::limit($rule->description, 50) }}</small>
                                @endif
                            </td>
                            <td><span class="badge bg-info">{{ $rule->getRuleTypeLabel() }}</span></td>
                            <td>
                                <small>
                                    @if($rule->vendor_id)
                                    <i class="bi bi-person"></i> {{ $rule->vendor->name }}<br>
                                    @endif
                                    @if($rule->hoarding_id)
                                    <i class="bi bi-sign-stop"></i> {{ $rule->hoarding->title }}<br>
                                    @endif
                                    @if($rule->city)
                                    <i class="bi bi-geo-alt"></i> {{ $rule->city }}<br>
                                    @endif
                                    @if($rule->area)
                                    <i class="bi bi-pin-map"></i> {{ $rule->area }}
                                    @endif
                                    @if(!$rule->vendor_id && !$rule->hoarding_id && !$rule->city && !$rule->area)
                                    <span class="text-muted">All</span>
                                    @endif
                                </small>
                            </td>
                            <td>
                                @if($rule->commission_type === 'percentage')
                                <strong class="text-success">{{ $rule->commission_value }}%</strong>
                                @elseif($rule->commission_type === 'fixed')
                                <strong class="text-primary">₹{{ number_format($rule->commission_value, 2) }}</strong>
                                @else
                                <strong class="text-warning">Tiered</strong>
                                @endif
                                <br><small class="text-muted">{{ $rule->getCommissionTypeLabel() }}</small>
                            </td>
                            <td>
                                <small>
                                    @if($rule->valid_from || $rule->valid_to)
                                    {{ $rule->valid_from?->format('M d, Y') ?? 'Anytime' }}<br>to {{ $rule->valid_to?->format('M d, Y') ?? 'Forever' }}
                                    @else
                                    <span class="text-muted">Always Valid</span>
                                    @endif
                                </small>
                            </td>
                            <td>
                                <strong>{{ $rule->usage_count }}</strong>
                                @if($rule->last_used_at)
                                <br><small class="text-muted">{{ $rule->last_used_at->diffForHumans() }}</small>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('admin.commission-rules.toggle', $rule) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-{{ $rule->is_active ? 'success' : 'secondary' }}">
                                        <i class="bi bi-{{ $rule->is_active ? 'check-circle' : 'x-circle' }}"></i>
                                        {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.commission-rules.show', $rule) }}" class="btn btn-outline-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.commission-rules.edit', $rule) }}" class="btn btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.commission-rules.duplicate', $rule) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-secondary" title="Duplicate">
                                            <i class="bi bi-files"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.commission-rules.destroy', $rule) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this rule?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-3">
                {{ $rules->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<script>
    // Auto-dismiss alerts after 5 seconds
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
</script>
@endsection
