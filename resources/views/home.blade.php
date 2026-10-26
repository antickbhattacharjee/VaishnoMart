@extends('layouts.app')

@section('title', 'VaishnoMart - Hyperlocal Delivery')

@section('content')
<div class="container py-4">

    <!-- Promotional Hero Banner -->
    <div class="card border-0 rounded-4 overflow-hidden mb-5 text-white shadow-sm" style="background: linear-gradient(135deg, #064e3b 0%, #059669 60%, #10b981 100%);">
        <div class="card-body p-4 p-md-5">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill mb-3">
                        <i class="bi bi-lightning-charge-fill me-1"></i> Direct Store Delivery • No Intermediaries
                    </span>
                    <h1 class="display-6 fw-extrabold mb-2 text-white">Your Needs. Your Neighbourhood. Delivered.</h1>
                    <p class="lead text-light mb-4 opacity-90" style="font-size: 1.05rem;">
                        Order 20L bubble water cans, freshly cooked cloud kitchen thalis, supreme dry fruits, and daily groceries directly from certified stores around you.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="#sectionWater" class="btn btn-warning text-dark fw-bold px-4 py-2 rounded-pill">
                            <i class="bi bi-droplet-fill me-1"></i> Order Water Cans
                        </a>
                        <a href="#sectionFood" class="btn btn-outline-light fw-bold px-4 py-2 rounded-pill">
                            <i class="bi bi-fire me-1"></i> Cloud Kitchen Meals
                        </a>
                        <a href="#sectionVendors" class="btn btn-light text-success fw-bold px-4 py-2 rounded-pill">
                            <i class="bi bi-shop me-1"></i> Nearby Stores ({{ $nearbyVendors->count() }})
                        </a>
                    </div>
                </div>
                <div class="col-lg-5 d-none d-lg-block text-center">
                    <div class="p-3 bg-white bg-opacity-10 rounded-4 border border-white border-opacity-20 d-inline-block shadow">
                        <div class="d-flex align-items-center gap-3 text-start mb-2">
                            <div class="rounded-circle bg-warning p-2 text-dark"><i class="bi bi-shield-lock-fill fs-4"></i></div>
                            <div>
                                <div class="fw-bold">Safe Two-Stage OTP</div>
                                <div class="small text-light opacity-75">Pickup & Delivery Confirmed</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3 text-start">
                            <div class="rounded-circle bg-light p-2 text-success"><i class="bi bi-speedometer fs-4"></i></div>
                            <div>
                                <div class="fw-bold">Nearest Store First</div>
                                <div class="small text-light opacity-75">Fastest route matching</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Category Navigation (Section 5) -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold mb-0">Browse by Category</h4>
                <div class="text-muted small">Explore neighbourhood stores across all business categories</div>
            </div>
        </div>
        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-3">
            @foreach($categories as $category)
                <div class="col">
                    <a href="{{ route('category.show', $category->slug) }}" class="vm-cat-card">
                        <div class="vm-cat-icon-circle">
                            <i class="bi {{ $category->icon ?? 'bi-bag-check-fill' }}"></i>
                        </div>
                        <span class="small fw-bold text-center lh-sm">{{ $category->name }}</span>
                    </a>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Nearby Vendors (Section 7) -->
    <div class="mb-5" id="sectionVendors">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold mb-0"><i class="bi bi-geo-alt-fill text-danger me-1"></i> Nearby Stores Serving You</h4>
                <div class="text-muted small">Showing local vendors within delivery radius of your location</div>
            </div>
            <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-2 rounded-pill">
                {{ $nearbyVendors->count() }} Stores Available
            </span>
        </div>

        @if($nearbyVendors->isEmpty())
            <div class="card border-0 rounded-4 p-4 text-center bg-white shadow-sm">
                <i class="bi bi-shop text-muted display-4 mb-2"></i>
                <h5>No vendors currently within range</h5>
                <p class="text-muted small mb-3">Try changing your location or selecting Connaught Place in the header.</p>
                <div>
                    <button class="btn btn-vm-outline btn-sm" data-bs-toggle="modal" data-bs-target="#locationModal">Change Location</button>
                </div>
            </div>
        @else
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3">
                @foreach($nearbyVendors as $vendor)
                    <div class="col">
                        <div class="card border-0 rounded-4 shadow-sm h-100 overflow-hidden bg-white">
                            <div class="p-3 border-bottom d-flex align-items-center gap-3">
                                <div class="vm-cat-icon-circle" style="width: 44px; height: 44px; font-size: 1.2rem;">
                                    <i class="bi bi-shop"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <h6 class="fw-bold mb-0 text-truncate">{{ $vendor->business_name }}</h6>
                                    <span class="badge bg-light text-secondary border small">{{ $vendor->primaryCategory->name ?? 'Marketplace' }}</span>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center small mb-2">
                                    <span class="text-muted"><i class="bi bi-clock me-1"></i>Prep: {{ $vendor->prep_time_minutes ?? 15 }} mins</span>
                                    <span class="vm-badge-distance"><i class="bi bi-pin-map text-danger"></i> {{ $vendor->distance_from_user ?? 0 }} km</span>
                                </div>
                                <p class="text-muted small text-truncate mb-3">{{ $vendor->address }}</p>
                                <a href="{{ route('vendor.show', $vendor->id) }}" class="btn btn-vm-outline w-100 btn-sm">
                                    Visit Store & Products <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- AI/ML Personalized Recommendations Section -->
    @if(isset($aiRecommendedProducts) && $aiRecommendedProducts->isNotEmpty())
        <div class="card border-0 rounded-4 shadow-sm mb-5 overflow-hidden" style="background: linear-gradient(135deg, #f5f3ff 0%, #ecfdf5 50%, #ffffff 100%); border: 1px solid rgba(139, 92, 246, 0.2) !important;">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge rounded-pill text-white px-3 py-1 shadow-sm" style="background: linear-gradient(135deg, #7c3aed 0%, #059669 100%); font-size: 0.78rem;">
                                <i class="bi bi-stars me-1"></i> AI & ML Powered
                            </span>
                            <span class="badge bg-white text-secondary border rounded-pill px-2 py-1 small">
                                Hyperlocal Collaborative Match
                            </span>
                        </div>
                        <h4 class="fw-bold mb-0 text-dark">
                            Recommended For You
                        </h4>
                        <div class="text-muted small">Personalized items selected using machine learning based on your preferences and local trends</div>
                    </div>
                    <div>
                        <span class="text-xs text-muted font-monospace bg-white px-3 py-2 rounded-pill border">
                            <i class="bi bi-cpu text-purple me-1"></i> Cosine Similarity • 95%+ Confidence
                        </span>
                    </div>
                </div>

                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3">
                    @foreach($aiRecommendedProducts as $product)
                        <div class="col">
                            @include('partials.product-card', ['product' => $product])
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Section 9: Drinking Water & Packaged Water -->
    <div class="mb-5" id="sectionWater">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold mb-0 text-primary-dark">
                    <i class="bi bi-droplet-fill text-info me-1"></i> Drinking Water & Packaged Cans
                </h4>
                <div class="text-muted small">Sanitized 20L commercial bubble jars and mineral water bottles</div>
            </div>
            <a href="{{ route('category.show', 'drinking-water') }}" class="btn btn-sm btn-link text-decoration-none fw-bold">View All &rarr;</a>
        </div>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3">
            @foreach($waterProducts as $product)
                <div class="col">
                    @include('partials.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>

    <!-- Section 10: Cloud Kitchens & Meals -->
    <div class="mb-5" id="sectionFood">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold mb-0 text-primary-dark">
                    <i class="bi bi-fire text-danger me-1"></i> Fresh Cloud Kitchen Meals
                </h4>
                <div class="text-muted small">Freshly prepared thalis, biryanis, and snacks from top kitchens</div>
            </div>
            <a href="{{ route('category.show', 'cloud-kitchens') }}" class="btn btn-sm btn-link text-decoration-none fw-bold">View All &rarr;</a>
        </div>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3">
            @foreach($foodProducts as $product)
                <div class="col">
                    @include('partials.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>

    <!-- Section 11: Dry Fruits & Nuts -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold mb-0 text-primary-dark">
                    <i class="bi bi-box2-heart-fill text-warning me-1"></i> Premium Dry Fruits & Nuts
                </h4>
                <div class="text-muted small">California almonds, cashews, raisins, walnuts, and healthy snacking</div>
            </div>
            <a href="{{ route('category.show', 'dry-fruits') }}" class="btn btn-sm btn-link text-decoration-none fw-bold">View All &rarr;</a>
        </div>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3">
            @foreach($dryFruitsProducts as $product)
                <div class="col">
                    @include('partials.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>

    <!-- Section 12: Grocery & Daily Essentials -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold mb-0 text-primary-dark">
                    <i class="bi bi-basket2-fill text-success me-1"></i> Grocery & Daily Staples
                </h4>
                <div class="text-muted small">Flour, oils, rice, spices, and everyday kitchen essentials</div>
            </div>
            <a href="{{ route('category.show', 'grocery') }}" class="btn btn-sm btn-link text-decoration-none fw-bold">View All &rarr;</a>
        </div>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3">
            @foreach($groceryProducts as $product)
                <div class="col">
                    @include('partials.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>

    <!-- Section 15: Platform Trust & Customer Benefits -->
    <div class="card border-0 rounded-4 bg-white shadow-sm p-4 mb-4">
        <div class="row g-4 text-center">
            <div class="col-md-3">
                <div class="vm-cat-icon-circle mx-auto mb-2 text-success">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h6 class="fw-bold mb-1">Direct from Stores</h6>
                <p class="text-muted small mb-0">No middleman markups. Buy directly at authentic store prices.</p>
            </div>
            <div class="col-md-3">
                <div class="vm-cat-icon-circle mx-auto mb-2 text-primary">
                    <i class="bi bi-pin-map-fill"></i>
                </div>
                <h6 class="fw-bold mb-1">Nearest Store First</h6>
                <p class="text-muted small mb-0">Smart Haversine routing delivers from the closest certified vendor.</p>
            </div>
            <div class="col-md-3">
                <div class="vm-cat-icon-circle mx-auto mb-2 text-warning">
                    <i class="bi bi-key-fill"></i>
                </div>
                <h6 class="fw-bold mb-1">Verified OTP Delivery</h6>
                <p class="text-muted small mb-0">Server-verified pickup & delivery codes ensure complete order integrity.</p>
            </div>
            <div class="col-md-3">
                <div class="vm-cat-icon-circle mx-auto mb-2 text-danger">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
                <h6 class="fw-bold mb-1">Stock Protection</h6>
                <p class="text-muted small mb-0">Atomic inventory reservations eliminate overselling and order cancels.</p>
            </div>
        </div>
    </div>

</div>
@endsection
