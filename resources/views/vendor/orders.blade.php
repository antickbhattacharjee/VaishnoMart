@extends('layouts.vendor')

@section('title', 'Manage Orders - ' . $vendor->business_name)
@section('page_title', 'Store Orders')

@section('content')
<!-- Filter Navigation Tabs -->
<div class="card border-0 rounded-4 shadow-sm bg-white p-2 mb-4">
    <ul class="nav nav-pills gap-1">
        <li class="nav-item">
            <a class="nav-link rounded-pill px-3 {{ !$status ? 'active bg-success' : 'text-dark' }}" href="{{ route('vendor.orders') }}">
                All Orders
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill px-3 {{ $status === 'incoming' ? 'active bg-warning text-dark fw-bold' : 'text-dark' }}" href="{{ route('vendor.orders', ['status' => 'incoming']) }}">
                Incoming
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill px-3 {{ $status === 'active' ? 'active bg-primary' : 'text-dark' }}" href="{{ route('vendor.orders', ['status' => 'active']) }}">
                In Progress / Ready
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill px-3 {{ $status === 'completed' ? 'active bg-success' : 'text-dark' }}" href="{{ route('vendor.orders', ['status' => 'completed']) }}">
                Completed
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill px-3 {{ $status === 'cancelled' ? 'active bg-danger' : 'text-dark' }}" href="{{ route('vendor.orders', ['status' => 'cancelled']) }}">
                Cancelled
            </a>
        </li>
    </ul>
</div>

<!-- Orders Table Card -->
<div class="card border-0 rounded-4 shadow-sm bg-white p-4">
    @if($orders->isEmpty())
        <div class="p-5 text-center text-muted">
            <i class="bi bi-inbox display-4 mb-2"></i>
            <h5>No orders found in this view</h5>
            <p class="small mb-0">Check other tabs or wait for new customer requests.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>Order #</th>
                        <th>Placed</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Net Earning</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td>
                                <span class="fw-bold text-dark">#{{ $order->order_number }}</span>
                                @if($order->status === App\Models\Order::STATUS_PENDING_VENDOR)
                                    <span class="badge bg-warning text-dark small ms-1">New!</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $order->created_at->format('M d • h:i A') }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $order->customer->name ?? 'Customer' }}</div>
                                <div class="small text-muted">{{ $order->customer->phone ?? '' }}</div>
                            </td>
                            <td>
                                <div class="small text-dark fw-semibold">{{ $order->items->count() }} item(s)</div>
                                <div class="small text-muted text-truncate" style="max-width: 200px;">
                                    {{ $order->items->pluck('product_name')->implode(', ') }}
                                </div>
                            </td>
                            <td>
                                <span class="fw-bold text-success">₹{{ number_format($order->vendor_earnings_amount, 2) }}</span>
                                <div class="small text-muted" style="font-size: 0.7rem;">(Bill: ₹{{ number_format($order->total_amount, 2) }})</div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $order->status_badge_class }} rounded-pill">
                                    {{ $order->status_label }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('vendor.orders.show', $order->id) }}" class="btn btn-sm btn-vm-primary rounded-pill px-3">
                                    Manage &rarr;
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
