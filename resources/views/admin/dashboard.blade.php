@extends('layouts.admin')

@section('title', 'Platform Executive Dashboard')

@section('content')
<div class="mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Platform Control Dashboard</h3>
        <p class="text-muted text-sm mb-0">Real-time hyperlocal operations, order routing, vendor stats, and platform earnings.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-clock-history me-1"></i> Live Order Stream
        </a>
        <a href="{{ route('admin.settlements.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-cash-stack me-1"></i> Process Settlements
        </a>
    </div>
</div>

<!-- Key Performance Indicators -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="admin-card p-3 d-flex align-items-center gap-3">
            <div class="rounded-3 bg-primary-subtle text-primary p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                <i class="bi bi-bag-check-fill"></i>
            </div>
            <div>
                <span class="text-muted text-xs fw-semibold text-uppercase">Total Orders</span>
                <h3 class="fw-bold mb-0">{{ number_format($totalOrders) }}</h3>
                <small class="text-success text-xs"><i class="bi bi-arrow-up"></i> Lifetime volume</small>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="admin-card p-3 d-flex align-items-center gap-3">
            <div class="rounded-3 bg-success-subtle text-success p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                <i class="bi bi-currency-rupee"></i>
            </div>
            <div>
                <span class="text-muted text-xs fw-semibold text-uppercase">Platform GMV</span>
                <h3 class="fw-bold mb-0">₹{{ number_format($totalGmv, 2) }}</h3>
                <small class="text-muted text-xs">Gross merchandise value</small>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="admin-card p-3 d-flex align-items-center gap-3">
            <div class="rounded-3 bg-info-subtle text-info p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                <i class="bi bi-pie-chart-fill"></i>
            </div>
            <div>
                <span class="text-muted text-xs fw-semibold text-uppercase">Earned Commissions</span>
                <h3 class="fw-bold mb-0">₹{{ number_format($totalCommissions, 2) }}</h3>
                <small class="text-info text-xs">Net platform revenue</small>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="admin-card p-3 d-flex align-items-center gap-3">
            <div class="rounded-3 bg-warning-subtle text-warning p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <span class="text-muted text-xs fw-semibold text-uppercase">Registered Customers</span>
                <h3 class="fw-bold mb-0">{{ number_format($totalCustomers) }}</h3>
                <small class="text-muted text-xs">Active consumer base</small>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Metrics: Vendors & Agents -->
<div class="row g-3 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="admin-card p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted text-xs fw-bold">ACTIVE VENDORS</span>
                <span class="badge bg-success-subtle text-success">Verified</span>
            </div>
            <h4 class="fw-bold mb-0">{{ $activeVendors }} Stores</h4>
            <a href="{{ route('admin.vendors.index') }}" class="text-xs text-primary text-decoration-none mt-2 d-inline-block">
                View all vendors &rarr;
            </a>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="admin-card p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted text-xs fw-bold">PENDING VENDORS</span>
                <span class="badge bg-warning-subtle text-warning">Action Req.</span>
            </div>
            <h4 class="fw-bold mb-0">{{ $pendingVendors }} Awaiting Review</h4>
            <a href="{{ route('admin.vendors.index') }}" class="text-xs text-primary text-decoration-none mt-2 d-inline-block">
                Review store applications &rarr;
            </a>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="admin-card p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted text-xs fw-bold">DELIVERY FLEET</span>
                <span class="badge bg-primary-subtle text-primary">Fleet Agents</span>
            </div>
            <h4 class="fw-bold mb-0">{{ $activeAgents }} Active Riders</h4>
            <a href="{{ route('admin.agents.index') }}" class="text-xs text-primary text-decoration-none mt-2 d-inline-block">
                Manage delivery fleet &rarr;
            </a>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="admin-card p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted text-xs fw-bold">SYSTEM INTEGRITY</span>
                <span class="badge bg-success text-white">Online</span>
            </div>
            <h4 class="fw-bold mb-0 text-success"><i class="bi bi-shield-check"></i> 100% Operational</h4>
            <span class="text-xs text-muted mt-2 d-inline-block">Haversine matching active</span>
        </div>
    </div>
</div>

<!-- Recent Orders Table -->
<div class="admin-card p-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h5 class="fw-bold mb-0">Recent Order Activity</h5>
            <small class="text-muted">Live incoming customer orders and their matching status</small>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary btn-sm">
            View All Orders ({{ $totalOrders }})
        </a>
    </div>

    @if($recentOrders->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
            <p class="mb-0">No orders recorded in the system yet.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Store / Vendor</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Delivery Rider</th>
                        <th>Placed At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentOrders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="fw-bold text-decoration-none">
                                    {{ $order->order_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $order->customer->name ?? 'Guest/Unknown' }}</div>
                                <small class="text-muted text-xs">{{ $order->customer->phone ?? '' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $order->vendor->business_name ?? 'Pending Assignment' }}
                                </span>
                            </td>
                            <td class="fw-bold">
                                ₹{{ number_format($order->total_amount, 2) }}
                            </td>
                            <td>
                                @php
                                    $statusBadge = match($order->status) {
                                        'pending_vendor' => 'warning text-dark',
                                        'accepted' => 'info text-dark',
                                        'preparing' => 'primary',
                                        'ready_for_pickup' => 'secondary',
                                        'delivery_requested' => 'warning text-dark',
                                        'delivery_assigned' => 'info text-dark',
                                        'picked_up', 'out_for_delivery' => 'primary',
                                        'delivered' => 'success',
                                        'cancelled', 'rejected' => 'danger',
                                        default => 'light text-dark',
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusBadge }}">
                                    {{ strtoupper(str_replace('_', ' ', $order->status)) }}
                                </span>
                            </td>
                            <td>
                                @if($order->deliveryAgent)
                                    <div class="small fw-semibold">{{ $order->deliveryAgent->name }}</div>
                                    <small class="text-muted text-xs">{{ $order->deliveryAgent->phone }}</small>
                                @else
                                    <span class="text-muted text-xs fst-italic">Unassigned</span>
                                @endif
                            </td>
                            <td class="text-xs text-muted">
                                {{ $order->created_at->format('d M, h:i A') }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i> Details
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
