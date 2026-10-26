@extends('layouts.agent')

@section('title', 'My Earnings - Agent Terminal')

@section('content')
<div class="mb-3">
    <h5 class="fw-bold mb-1">My Earnings</h5>
    <p class="small text-muted mb-0">Delivery base pay and distance incentives</p>
</div>

<!-- Total Earnings Balance Card -->
<div class="card border-0 rounded-4 shadow-sm text-white p-4 mb-3" style="background: linear-gradient(135deg, #064e3b, #059669);">
    <div class="small text-light opacity-75 fw-bold text-uppercase">Total Earned to Date</div>
    <div class="display-6 fw-bold mb-2">₹{{ number_format($totalEarned, 2) }}</div>
    <div class="small text-light opacity-75">
        <i class="bi bi-shield-check me-1"></i> Paid weekly directly into registered bank account.
    </div>
</div>

<!-- Ledger of Deliveries -->
<div class="card border-0 rounded-4 shadow-sm bg-white p-3">
    <h6 class="fw-bold mb-3">Earnings per Order</h6>
    @if($earnings->isEmpty())
        <div class="p-4 text-center text-muted small">
            No earnings credited yet. Complete delivery trips to see earnings breakdown.
        </div>
    @else
        <div class="d-flex flex-column gap-2">
            @foreach($earnings as $e)
                <div class="p-3 rounded-3 bg-light border d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold text-dark font-monospace small">#{{ $e->order->order_number ?? 'Order' }}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">
                            Base: ₹{{ number_format($e->base_earning, 0) }} | Incentive: ₹{{ number_format($e->distance_incentive, 0) }}
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-success fs-5">+₹{{ number_format($e->total_earning, 2) }}</div>
                        <span class="badge bg-{{ $e->status === 'settled' ? 'success' : 'warning' }} text-capitalize" style="font-size: 0.65rem;">
                            {{ $e->status }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $earnings->links() }}
        </div>
    @endif
</div>
@endsection
