@extends('layouts.agent')

@section('title', 'Delivery History - Agent Terminal')

@section('content')
<div class="mb-3">
    <h5 class="fw-bold mb-1">Delivery History</h5>
    <p class="small text-muted mb-0">Record of your successfully fulfilled orders</p>
</div>

@if($orders->isEmpty())
    <div class="card border-0 rounded-4 shadow-sm p-5 text-center bg-white">
        <i class="bi bi-clock-history text-muted display-4 mb-2"></i>
        <h6>No deliveries completed yet</h6>
        <p class="small text-muted mb-0">Accepted deliveries will appear here once verified and handed over.</p>
    </div>
@else
    <div class="d-flex flex-column gap-2 mb-3">
        @foreach($orders as $ord)
            <div class="card border-0 rounded-4 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-bold text-dark font-monospace">#{{ $ord->order_number }}</span>
                    <span class="badge bg-success">Delivered</span>
                </div>
                <div class="small text-dark mb-1">
                    <strong>{{ $ord->vendor->business_name }}</strong> &rarr; {{ $ord->deliveryAddress->recipient_name ?? 'Customer' }}
                </div>
                <div class="d-flex justify-content-between align-items-center small text-muted">
                    <span>{{ $ord->delivered_at ? $ord->delivered_at->format('M d, Y • h:i A') : $ord->updated_at->format('M d, Y') }}</span>
                    <span class="fw-bold text-success fs-6">+₹{{ number_format($ord->delivery_agent_earnings_amount, 2) }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-center">
        {{ $orders->links() }}
    </div>
@endif
@endsection
