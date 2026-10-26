@extends('layouts.admin')

@section('title', 'All Orders - Live Order Monitor')

@section('content')
<div class="mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Live Order Monitor</h3>
        <p class="text-muted text-sm mb-0">Track platform-wide order routing, vendor acceptance SLAs, and delivery status in real-time.</p>
    </div>
</div>

<!-- Status Filters -->
<div class="admin-card p-3 mb-4">
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <span class="text-xs text-muted fw-bold text-uppercase me-2">Filter By Status:</span>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm {{ empty($status) ? 'btn-primary' : 'btn-outline-secondary' }}">
            All Orders
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'pending_vendor']) }}" class="btn btn-sm {{ $status === 'pending_vendor' ? 'btn-warning' : 'btn-outline-secondary' }}">
            Pending Vendor
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'accepted']) }}" class="btn btn-sm {{ $status === 'accepted' ? 'btn-info text-white' : 'btn-outline-secondary' }}">
            Accepted
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'preparing']) }}" class="btn btn-sm {{ $status === 'preparing' ? 'btn-primary' : 'btn-outline-secondary' }}">
            Preparing
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'ready_for_pickup']) }}" class="btn btn-sm {{ $status === 'ready_for_pickup' ? 'btn-secondary' : 'btn-outline-secondary' }}">
            Ready for Pickup
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'out_for_delivery']) }}" class="btn btn-sm {{ $status === 'out_for_delivery' ? 'btn-primary' : 'btn-outline-secondary' }}">
            Out for Delivery
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'delivered']) }}" class="btn btn-sm {{ $status === 'delivered' ? 'btn-success' : 'btn-outline-secondary' }}">
            Delivered
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}" class="btn btn-sm {{ $status === 'cancelled' ? 'btn-danger' : 'btn-outline-secondary' }}">
            Cancelled / Rejected
        </a>
    </div>
</div>

<!-- Orders Table -->
<div class="admin-card p-4">
    @if($orders->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
            <h5>No orders found</h5>
            <p class="text-xs mb-0">No orders match the selected filter criteria.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Store / Vendor</th>
                        <th>Payment</th>
                        <th>Gross Total</th>
                        <th>Commission</th>
                        <th>Status</th>
                        <th>Delivery Rider</th>
                        <th>Date & Time</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="fw-bold text-decoration-none">
                                    {{ $order->order_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $order->customer->name ?? 'Guest' }}</div>
                                <small class="text-muted text-xs">{{ $order->customer->phone ?? '' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $order->vendor->business_name ?? 'Awaiting Routing' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary text-uppercase">
                                    {{ $order->payment_method }} ({{ $order->payment_status }})
                                </span>
                            </td>
                            <td class="fw-bold">
                                ₹{{ number_format($order->total_amount, 2) }}
                            </td>
                            <td class="text-success fw-semibold">
                                ₹{{ number_format($order->platform_commission_amount, 2) }}
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
                                    <span class="text-muted text-xs fst-italic">None</span>
                                @endif
                            </td>
                            <td class="text-xs text-muted">
                                {{ $order->created_at->format('d M, h:i A') }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
