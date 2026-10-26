@extends('layouts.app')

@section('title', 'My Account - VaishnoMart')

@section('content')
<div class="container py-4">
    <div class="row g-4">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
                <div class="d-flex align-items-center gap-3 pb-3 border-bottom mb-3">
                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fs-4 fw-bold" style="width: 50px; height: 50px;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="overflow-hidden">
                        <h6 class="fw-bold mb-0 text-truncate">{{ $user->name }}</h6>
                        <small class="text-muted text-xs d-block">{{ $user->phone ?? $user->email }}</small>
                    </div>
                </div>

                <div class="nav flex-column nav-pills gap-1">
                    <a href="{{ route('customer.dashboard') }}" class="nav-link active rounded-3 py-2 px-3 fw-semibold">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                    <a href="{{ route('customer.orders') }}" class="nav-link rounded-3 py-2 px-3 text-secondary fw-semibold">
                        <i class="bi bi-bag-check me-2"></i> My Orders
                    </a>
                    <a href="{{ route('customer.addresses') }}" class="nav-link rounded-3 py-2 px-3 text-secondary fw-semibold">
                        <i class="bi bi-geo-alt me-2"></i> Saved Addresses
                    </a>
                    <a href="{{ route('cart.index') }}" class="nav-link rounded-3 py-2 px-3 text-secondary fw-semibold">
                        <i class="bi bi-cart me-2"></i> My Cart
                    </a>
                </div>

                <div class="pt-3 border-top mt-3">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100 btn-sm rounded-3">
                            <i class="bi bi-box-arrow-right me-1"></i> Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Dashboard Area -->
        <div class="col-lg-9">
            <!-- Active Orders Notice -->
            @if($activeOrders->isNotEmpty())
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-primary-subtle border-primary-subtle">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold mb-0 text-primary-emphasis">
                            <i class="bi bi-lightning-charge-fill text-warning me-2"></i>Active Orders in Progress ({{ $activeOrders->count() }})
                        </h5>
                    </div>

                    <div class="row g-3">
                        @foreach($activeOrders as $activeOrder)
                            <div class="col-md-6">
                                <div class="bg-white rounded-3 p-3 shadow-xs">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold">#{{ $activeOrder->order_number }}</span>
                                        <span class="badge bg-primary text-uppercase">{{ str_replace('_', ' ', $activeOrder->status) }}</span>
                                    </div>
                                    <div class="text-xs text-muted mb-2">
                                        Store: <strong>{{ $activeOrder->vendor->business_name ?? 'Assigned Vendor' }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-success">₹{{ number_format($activeOrder->total_amount, 2) }}</span>
                                        <a href="{{ route('customer.orders.show', $activeOrder->id) }}" class="btn btn-sm btn-primary px-3 rounded-pill">
                                            Track Order & OTP <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Dashboard Welcome Banner -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #059669 0%, #064e3b 100%);">
                <div class="position-relative z-1">
                    <h4 class="fw-bold mb-1">Namaste, {{ $user->name }}! 👋</h4>
                    <p class="mb-3 text-white-50 text-sm">Welcome to your VaishnoMart account. Order water cans, hot kitchen meals, daily groceries and farm-fresh essentials from verified neighbourhood stores.</p>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('home') }}" class="btn btn-light btn-sm rounded-pill fw-bold text-success px-3">
                            <i class="bi bi-search me-1"></i> Browse Stores
                        </a>
                        <a href="{{ route('customer.addresses') }}" class="btn btn-outline-light btn-sm rounded-pill px-3">
                            <i class="bi bi-geo-alt me-1"></i> Manage Addresses
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Orders Section -->
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold mb-0">Recent Orders</h5>
                    <a href="{{ route('customer.orders') }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                        View All Orders &rarr;
                    </a>
                </div>

                @if($recentOrders->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-bag-x fs-1 d-block mb-2 text-secondary"></i>
                        <h6>No orders placed yet</h6>
                        <p class="text-xs mb-3">Explore thousands of products delivered directly to your doorstep.</p>
                        <a href="{{ route('home') }}" class="btn btn-success btn-sm rounded-pill px-4">
                            Start Shopping
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Store</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th class="text-end">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                    <tr>
                                        <td>
                                            <a href="{{ route('customer.orders.show', $order->id) }}" class="fw-bold text-decoration-none">
                                                {{ $order->order_number }}
                                            </a>
                                        </td>
                                        <td>{{ $order->vendor->business_name ?? 'Local Store' }}</td>
                                        <td class="fw-bold">₹{{ number_format($order->total_amount, 2) }}</td>
                                        <td>
                                            @php
                                                $statusBadge = match($order->status) {
                                                    'delivered' => 'success',
                                                    'cancelled', 'rejected' => 'danger',
                                                    'out_for_delivery', 'picked_up' => 'primary',
                                                    default => 'warning text-dark',
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $statusBadge }}">
                                                {{ strtoupper(str_replace('_', ' ', $order->status)) }}
                                            </span>
                                        </td>
                                        <td class="text-xs text-muted">{{ $order->created_at->format('d M, Y') }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('customer.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
