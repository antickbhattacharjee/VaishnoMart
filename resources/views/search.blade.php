@extends('layouts.app')

@section('title', 'Search Results - VaishnoMart')

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <h3 class="fw-bold mb-1">
            @if($query)
                Search Results for "<span class="text-success">{{ $query }}</span>"
            @else
                Explore All Products
            @endif
        </h3>
        <p class="text-muted small">Showing {{ $products->total() }} matching products from neighbourhood stores</p>
    </div>

    <!-- Matched Stores Carousel/Row if any -->
    @if($vendors->isNotEmpty())
        <div class="card border-0 rounded-4 shadow-sm p-4 mb-4 bg-white">
            <h6 class="fw-bold mb-3"><i class="bi bi-shop text-success me-1"></i> Matching Stores</h6>
            <div class="row row-cols-1 row-cols-md-3 g-3">
                @foreach($vendors as $v)
                    <div class="col">
                        <div class="p-3 rounded-3 bg-light border d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-0">{{ $v->business_name }}</h6>
                                <div class="small text-muted">{{ $v->primaryCategory->name ?? 'Store' }}</div>
                            </div>
                            <a href="{{ route('vendor.show', $v->id) }}" class="btn btn-sm btn-outline-success">Visit</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- Filter Sidebar -->
        <div class="col-lg-3">
            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white sticky-top" style="top: 90px;">
                <h6 class="fw-bold mb-3">Filters</h6>
                <form action="{{ route('search') }}" method="GET">
                    <input type="hidden" name="q" value="{{ $query }}">

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Category</label>
                        <select name="category" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Max Price (₹)</label>
                        <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="e.g. 500" class="form-control form-control-sm">
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-vm-primary btn-sm">Apply Filters</button>
                        <a href="{{ route('search', ['q' => $query]) }}" class="btn btn-light btn-sm border">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Products List -->
        <div class="col-lg-9">
            @if($products->isEmpty())
                <div class="card border-0 rounded-4 p-5 text-center bg-white shadow-sm">
                    <i class="bi bi-search text-muted display-4 mb-2"></i>
                    <h5>No products matched your search</h5>
                    <p class="text-muted small">Try searching for generic terms like "water", "thali", "almonds", or "atta".</p>
                    <div>
                        <a href="{{ route('home') }}" class="btn btn-vm-primary btn-sm">Return to Home</a>
                    </div>
                </div>
            @else
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3 mb-4">
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
    </div>
</div>
@endsection
