@extends('layouts.admin')

@section('title', 'Manage Vendors & Stores')

@section('content')
<div class="mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Vendors & Store Directory</h3>
        <p class="text-muted text-sm mb-0">Approve new store onboarding, suspend non-compliant vendors, and monitor commission allocations.</p>
    </div>
</div>

<div class="admin-card p-4">
    @if($vendors->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-shop fs-1 d-block mb-2 text-secondary"></i>
            <h5>No vendors registered</h5>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Store Details</th>
                        <th>Owner & Contact</th>
                        <th>Category</th>
                        <th>Service Radius</th>
                        <th>Operating Status</th>
                        <th>Approval Status</th>
                        <th class="text-end">Manage Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vendors as $vendor)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-3 bg-light border p-2 d-flex align-items-center justify-content-center text-primary fs-4" style="width: 48px; height: 48px;">
                                        <i class="bi bi-shop"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $vendor->business_name }}</div>
                                        <small class="text-muted text-xs">{{ Str::limit($vendor->business_address, 35) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $vendor->owner_name ?? $vendor->user->name }}</div>
                                <small class="text-muted text-xs d-block">{{ $vendor->user->phone ?? 'No Phone' }}</small>
                                <small class="text-muted text-xs">{{ $vendor->user->email }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $vendor->primaryCategory->name ?? 'General' }}
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold">{{ $vendor->serviceable_radius_km ?? 7 }} km</span>
                            </td>
                            <td>
                                @if($vendor->is_online && $vendor->is_open)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Open & Live
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border">
                                        Closed / Offline
                                    </span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusBadge = match($vendor->status) {
                                        'approved' => 'success',
                                        'pending' => 'warning text-dark',
                                        'suspended' => 'danger',
                                        'rejected' => 'secondary',
                                        default => 'light text-dark',
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusBadge }}">
                                    {{ strtoupper($vendor->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Change Status
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <form action="{{ route('admin.vendors.status', $vendor->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" class="dropdown-item text-success">
                                                    <i class="bi bi-check-circle me-2"></i> Approve Store
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="{{ route('admin.vendors.status', $vendor->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="status" value="suspended">
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-pause-circle me-2"></i> Suspend Store
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="{{ route('admin.vendors.status', $vendor->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" class="dropdown-item text-secondary">
                                                    <i class="bi bi-x-circle me-2"></i> Reject Application
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
            {{ $vendors->links() }}
        </div>
    @endif
</div>
@endsection
