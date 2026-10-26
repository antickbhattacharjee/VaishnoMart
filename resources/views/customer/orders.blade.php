@extends('layouts.app')

@section('title', 'My Orders - VaishnoMart')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">My Orders</h3>
            <p class="text-muted small">Track current orders and view previous order history</p>
        </div>
        <a href="{{ route('home') }}" class="btn btn-vm-outline btn-sm">
            <i class="bi bi-cart-plus me-1"></i> Order Again
        </a>
    </div>

    @if($orders->isEmpty())
        <div class="card border-0 rounded-4 p-5 text-center bg-white shadow-sm">
            <i class="bi bi-bag-x text-muted display-3 mb-3"></i>
            <h4 class="fw-bold">No orders found</h4>
            <p class="text-muted small mb-4">You have not placed any orders yet. Explore neighbourhood stores!</p>
            <div>
                <a href="{{ route('home') }}" class="btn btn-vm-primary px-4 py-2 rounded-pill">Explore Marketplace</a>
            </div>
        </div>
    @else
        <div class="d-flex flex-column gap-3 mb-4">
            @foreach($orders as $order)
                <div class="card border-0 rounded-4 shadow-sm bg-white p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 pb-3 border-bottom">
                        <div>
                            <span class="fw-bold text-dark fs-5">#{{ $order->order_number }}</span>
                            <div class="small text-muted">Placed on {{ $order->created_at->format('M d, Y • h:i A') }}</div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-{{ $order->status_badge_class }} px-3 py-2 rounded-pill fw-bold">
                                {{ $order->status_label }}
                            </span>
                            <a href="{{ route('customer.orders.show', $order->id) }}" class="btn btn-vm-primary btn-sm rounded-pill px-3">
                                Track & View <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>

                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <div class="small fw-semibold text-dark"><i class="bi bi-shop text-success me-1"></i> Store: {{ $order->vendor->business_name ?? 'Local Store' }}</div>
                            <div class="small text-muted">
                                {{ $order->items->pluck('product_name')->implode(', ') }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small text-muted">Payment Method:</div>
                            <div class="small fw-semibold text-capitalize text-dark">{{ $order->payment_method }} ({{ $order->payment_status }})</div>
                        </div>
                        <div class="col-md-3 text-md-end">
                            <div class="small text-muted">Total Amount:</div>
                            <div class="fs-5 fw-bold text-success">₹{{ number_format($order->total_amount, 2) }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
