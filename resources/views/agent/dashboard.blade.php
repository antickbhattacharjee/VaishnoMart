@extends('layouts.agent')

@section('title', 'Delivery Requests - Agent Terminal')

@section('content')
<!-- Earnings & Completed Deliveries Quick Summary -->
<div class="row g-2 mb-3">
    <div class="col-6">
        <div class="card border-0 rounded-4 shadow-sm bg-white p-3">
            <div class="small text-muted fw-bold">Today's Earnings</div>
            <div class="fs-4 fw-bold text-success">₹{{ number_format($todayEarnings, 2) }}</div>
        </div>
    </div>
    <div class="col-6">
        <div class="card border-0 rounded-4 shadow-sm bg-white p-3">
            <div class="small text-muted fw-bold">Deliveries Done</div>
            <div class="fs-4 fw-bold text-dark">{{ $completedCount }} Orders</div>
        </div>
    </div>
</div>

<!-- Active Trip Alert (if any) -->
@if($currentOrder)
    <div class="card border-0 rounded-4 shadow-sm text-white p-4 mb-3" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge bg-dark fw-bold text-warning">TRIP IN PROGRESS</span>
            <span class="small font-monospace">#{{ $currentOrder->order_number }}</span>
        </div>
        <h5 class="fw-bold mb-1">{{ $currentOrder->vendor->business_name }} &rarr; {{ $currentOrder->customer->name }}</h5>
        <p class="small text-white opacity-90 mb-3">Status: {{ $currentOrder->status_label }}</p>
        <a href="{{ route('agent.current-order') }}" class="btn btn-dark fw-bold rounded-pill w-100">
            Open Trip Navigation & OTP Terminal <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
@endif

<!-- Offline Warning if offline -->
@if(!$profile->is_online)
    <div class="card border-0 rounded-4 shadow-sm p-4 text-center bg-white mb-3">
        <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary mx-auto mb-2 p-3" style="width: 60px; height: 60px;">
            <i class="bi bi-moon-stars-fill fs-3"></i>
        </div>
        <h5 class="fw-bold">You are currently OFFLINE</h5>
        <p class="small text-muted mb-3">Switch your status to ONLINE using the button in the top bar to start receiving nearby delivery requests.</p>
        <form action="{{ route('agent.online.toggle') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success fw-bold rounded-pill px-4">
                <i class="bi bi-power me-1"></i> Go Online Now
            </button>
        </form>
    </div>
@else
    <!-- Available Delivery Requests -->
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-bold text-uppercase small text-muted mb-0">Available Delivery Requests ({{ $availableAssignments->count() }})</h6>
        <span class="badge bg-success rounded-pill px-2 py-1 small">Live Broadcast</span>
    </div>

    @if($availableAssignments->isEmpty())
        <div class="card border-0 rounded-4 shadow-sm p-5 text-center bg-white">
            <div class="spinner-grow text-success mx-auto mb-3" role="status">
                <span class="visually-hidden">Waiting...</span>
            </div>
            <h6 class="fw-bold mb-1">Scanning for Nearby Ready Orders</h6>
            <p class="small text-muted mb-0">Stay online! New delivery requests from stores near you will appear here instantly.</p>
        </div>
    @else
        <div class="d-flex flex-column gap-3">
            @foreach($availableAssignments as $assign)
                @php $ord = $assign->order; @endphp
                <div class="card border-0 rounded-4 shadow-sm bg-white p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-light text-dark border">
                            <i class="bi bi-clock me-1"></i> Ready for pickup
                        </span>
                        <span class="fs-5 fw-bold text-success">
                            ₹{{ number_format($ord->delivery_agent_earnings_amount, 2) }}
                            <span class="small text-muted" style="font-size: 0.7rem;">payout</span>
                        </span>
                    </div>

                    <!-- Pickup Location -->
                    <div class="d-flex align-items-start gap-2 mb-2">
                        <i class="bi bi-shop text-success fs-5"></i>
                        <div>
                            <div class="fw-bold text-dark small">PICKUP: {{ $ord->vendor->business_name }}</div>
                            <div class="small text-muted text-truncate" style="max-width: 260px;">{{ $ord->vendor->address }}</div>
                        </div>
                    </div>

                    <!-- Drop Location -->
                    <div class="d-flex align-items-start gap-2 mb-3">
                        <i class="bi bi-geo-alt-fill text-danger fs-5"></i>
                        <div>
                            <div class="fw-bold text-dark small">DROP: {{ $ord->deliveryAddress->formatted_address ?? 'Customer Destination' }}</div>
                            <div class="small text-muted">{{ $ord->items->count() }} package items</div>
                        </div>
                    </div>

                    <!-- Accept Delivery Button -->
                    <form action="{{ route('agent.deliveries.accept', $ord->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-warning text-dark fw-bold w-100 rounded-pill py-2 shadow-sm">
                            <i class="bi bi-check-circle-fill me-1"></i> Accept Delivery Request
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
@endif
@endsection
