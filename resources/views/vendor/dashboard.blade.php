@extends('layouts.vendor')

@section('title', 'Vendor Dashboard - ' . $vendor->business_name)
@section('page_title', 'Store Overview')

@section('content')
<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 rounded-4 shadow-sm bg-white p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold">Today's Revenue</span>
                <div class="rounded-circle bg-success bg-opacity-10 text-success p-2 fs-5">
                    <i class="bi bi-currency-rupee"></i>
                </div>
            </div>
            <div class="fs-3 fw-bold text-dark">₹{{ number_format($todaySales, 2) }}</div>
            <span class="small text-muted">Net after platform commission</span>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 rounded-4 shadow-sm p-3 h-100 {{ $incomingCount > 0 ? 'bg-warning text-dark' : 'bg-white' }}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-bold {{ $incomingCount > 0 ? 'text-dark' : 'text-muted' }}">Incoming Orders</span>
                <div class="rounded-circle {{ $incomingCount > 0 ? 'bg-dark text-warning' : 'bg-warning bg-opacity-10 text-warning' }} p-2 fs-5">
                    <i class="bi bi-bell-fill"></i>
                </div>
            </div>
            <div class="fs-3 fw-bold">{{ $incomingCount }}</div>
            @if($incomingCount > 0)
                <a href="{{ route('vendor.orders', ['status' => 'incoming']) }}" class="small fw-bold text-dark text-decoration-underline">
                    Action required! Review & accept &rarr;
                </a>
            @else
                <span class="small text-muted">No pending orders</span>
            @endif
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 rounded-4 shadow-sm bg-white p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold">Active Orders</span>
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 fs-5">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
            <div class="fs-3 fw-bold text-dark">{{ $activeOrdersCount }}</div>
            <span class="small text-muted">Preparing & waiting for pickup</span>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 rounded-4 shadow-sm bg-white p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold">Store Wallet</span>
                <div class="rounded-circle bg-info bg-opacity-10 text-info p-2 fs-5">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
            <div class="fs-3 fw-bold text-success">₹{{ number_format($walletBalance, 2) }}</div>
            <a href="{{ route('vendor.earnings') }}" class="small text-decoration-none fw-semibold">View Ledger & Payouts &rarr;</a>
        </div>
    </div>
</div>

<!-- Incoming Orders Urgent Action Alert Banner -->
@if($incomingCount > 0)
    <div class="alert alert-warning border-0 rounded-4 shadow-sm p-4 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-warning text-dark p-3 fs-3 d-flex align-items-center justify-content-center">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-1 text-dark">You have {{ $incomingCount }} new incoming order(s)!</h5>
                <p class="mb-0 small text-dark">Please review and accept incoming orders before the countdown timer expires and the order gets reassigned.</p>
            </div>
        </div>
        <a href="{{ route('vendor.orders', ['status' => 'incoming']) }}" class="btn btn-dark fw-bold rounded-pill px-4">
            View Incoming Orders Now
        </a>
    </div>
@endif

<!-- Recent Store Orders Table -->
<div class="card border-0 rounded-4 shadow-sm bg-white p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Recent Orders</h5>
        <a href="{{ route('vendor.orders') }}" class="btn btn-sm btn-outline-success">View All Orders</a>
    </div>

    @if($recentOrders->isEmpty())
        <div class="p-4 text-center text-muted">
            <i class="bi bi-inbox fs-2 mb-2"></i>
            <p class="small mb-0">No orders received yet.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentOrders as $order)
                        <tr>
                            <td>
                                <span class="fw-bold">#{{ $order->order_number }}</span>
                                <div class="small text-muted">{{ $order->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $order->customer->name ?? 'Customer' }}</div>
                                <div class="small text-muted">{{ $order->customer->phone ?? '' }}</div>
                            </td>
                            <td>
                                <div class="small text-muted">{{ $order->items->count() }} item(s)</div>
                            </td>
                            <td>
                                <span class="fw-bold text-success">₹{{ number_format($order->vendor_earnings_amount, 2) }}</span>
                                <div class="small text-muted" style="font-size: 0.7rem;">Net Earning</div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $order->status_badge_class }} rounded-pill">
                                    {{ $order->status_label }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('vendor.orders.show', $order->id) }}" class="btn btn-sm btn-vm-outline">
                                    Manage &rarr;
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
