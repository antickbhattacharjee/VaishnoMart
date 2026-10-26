@extends('layouts.app')

@section('title', $vendor->business_name . ' - VaishnoMart')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('category.show', $vendor->primaryCategory->slug ?? 'grocery') }}" class="text-decoration-none">{{ $vendor->primaryCategory->name ?? 'Category' }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $vendor->business_name }}</li>
        </ol>
    </nav>

    <!-- Vendor Store Profile Card -->
    <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden mb-4">
        <div class="p-4 p-md-5" style="background: linear-gradient(135deg, #064e3b 0%, #059669 100%); color: white;">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill">
                            <i class="bi bi-patch-check-fill me-1"></i> Verified Local Merchant
                        </span>
                        @if($vendor->isAvailable())
                            <span class="badge bg-success bg-opacity-75 text-white fw-bold px-3 py-1 rounded-pill">Open & Accepting Orders</span>
                        @else
                            <span class="badge bg-danger text-white fw-bold px-3 py-1 rounded-pill">Currently Closed</span>
                        @endif
                    </div>
                    <h1 class="fw-bold mb-2">{{ $vendor->business_name }}</h1>
                    <p class="mb-2 text-light opacity-90 small">
                        <i class="bi bi-geo-alt-fill text-warning me-1"></i> {{ $vendor->address }}
                    </p>
                    <div class="d-flex flex-wrap gap-3 small text-light opacity-90">
                        <span><i class="bi bi-clock me-1"></i> {{ date('g:i A', strtotime($vendor->opening_time)) }} - {{ date('g:i A', strtotime($vendor->closing_time)) }}</span>
                        <span><i class="bi bi-bicycle me-1"></i> Service Radius: {{ $vendor->service_radius_km }} km</span>
                        <span><i class="bi bi-hourglass-split me-1"></i> Avg Prep Time: {{ $vendor->prep_time_minutes ?? 15 }} mins</span>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="p-3 bg-white bg-opacity-10 rounded-3 d-inline-block text-start border border-white border-opacity-20">
                        <div class="text-uppercase small text-light opacity-75 fw-bold">Distance from you</div>
                        <div class="display-6 fw-bold text-warning">{{ $distance }} <span class="fs-6">km</span></div>
                        <div class="small text-light opacity-75">Direct Delivery Feasible</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Catalogue -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0">Store Products ({{ $products->total() }})</h4>
            <span class="small text-muted">Single-store checkout applies to orders from this merchant</span>
        </div>

        @if($products->isEmpty())
            <div class="card border-0 rounded-4 p-5 text-center bg-white shadow-sm">
                <i class="bi bi-box text-muted display-4 mb-2"></i>
                <h5>No products currently listed</h5>
                <p class="text-muted small">The store will update its catalogue shortly.</p>
            </div>
        @else
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3 mb-4">
                @foreach($products as $product)
                    <div class="col">
                        @include('partials.product-card', ['product' => $product])
                    </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-center">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <!-- Reviews Section -->
    @if($vendor->reviews && $vendor->reviews->isNotEmpty())
        <div class="card border-0 rounded-4 p-4 bg-white shadow-sm mb-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-star-fill text-warning me-1"></i> Customer Ratings & Reviews</h5>
            <div class="row g-3">
                @foreach($vendor->reviews as $review)
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light border">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold small">{{ $review->customer->name ?? 'Verified Customer' }}</span>
                                <div class="text-warning small">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi {{ $i <= $review->vendor_rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                    @endfor
                                </div>
                            </div>
                            <p class="small text-muted mb-0">{{ $review->vendor_review ?? 'Great store service and quick preparation!' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
