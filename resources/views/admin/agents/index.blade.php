@extends('layouts.admin')

@section('title', 'Manage Delivery Agents')

@section('content')
<div class="mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Delivery Agent Fleet</h3>
        <p class="text-muted text-sm mb-0">Approve rider onboarding, manage vehicle details, and monitor fleet availability.</p>
    </div>
</div>

<div class="admin-card p-4">
    @if($agents->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-bicycle fs-1 d-block mb-2 text-secondary"></i>
            <h5>No delivery agents registered</h5>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Rider Name</th>
                        <th>Contact Details</th>
                        <th>Vehicle</th>
                        <th>Fleet Status</th>
                        <th>Total Deliveries</th>
                        <th>Rating</th>
                        <th>Approval</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($agents as $agent)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center fs-5 fw-bold" style="width: 42px; height: 42px;">
                                        {{ strtoupper(substr($agent->user->name ?? 'R', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $agent->user->name ?? 'Agent' }}</div>
                                        <small class="text-muted text-xs">ID #{{ $agent->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>{{ $agent->user->phone ?? 'N/A' }}</div>
                                <small class="text-muted text-xs">{{ $agent->user->email }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-bicycle me-1"></i> {{ ucfirst($agent->vehicle_type ?? 'Bike') }} ({{ $agent->vehicle_number ?? 'DL-1' }})
                                </span>
                            </td>
                            <td>
                                @if($agent->is_online)
                                    <span class="badge bg-success-subtle text-success">
                                        <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Online
                                        {{ $agent->is_available ? '(Available)' : '(Busy)' }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">
                                        Offline
                                    </span>
                                @endif
                            </td>
                            <td class="fw-semibold">
                                {{ $agent->total_deliveries_completed }} orders
                            </td>
                            <td>
                                <span class="text-warning fw-bold">
                                    <i class="bi bi-star-fill"></i> {{ number_format($agent->rating_avg ?? 5.0, 1) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $statusBadge = match($agent->status) {
                                        'approved' => 'success',
                                        'pending' => 'warning text-dark',
                                        'suspended' => 'danger',
                                        'rejected' => 'secondary',
                                        default => 'light text-dark',
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusBadge }}">
                                    {{ strtoupper($agent->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Status
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <form action="{{ route('admin.agents.status', $agent->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" class="dropdown-item text-success">
                                                    <i class="bi bi-check-circle me-2"></i> Approve Rider
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="{{ route('admin.agents.status', $agent->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="status" value="suspended">
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-pause-circle me-2"></i> Suspend Rider
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="{{ route('admin.agents.status', $agent->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" class="dropdown-item text-secondary">
                                                    <i class="bi bi-x-circle me-2"></i> Reject Rider
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $agents->links() }}
        </div>
    @endif
</div>
@endsection
