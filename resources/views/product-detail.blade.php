@extends('layouts.app')

@section('title', $product->name . ' - VaishnoMart')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('category.show', $product->category->slug ?? 'grocery') }}" class="text-decoration-none">{{ $product->category->name ?? 'Category' }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row g-4 mb-5">
        <!-- Product Visual -->
        <div class="col-lg-5">
            <div class="card border-0 rounded-4 shadow-sm bg-white p-5 text-center">
                <div class="vm-product-img-box mb-0" style="height: 320px;">
                    <i class="bi {{ $product->category->icon ?? 'bi-box-seam' }} display-1 text-success"></i>
                </div>
            </div>
        </div>

        <!-- Product Specifications & Purchase Box -->
        <div class="col-lg-7">
            <div class="card border-0 rounded-4 shadow-sm bg-white p-4 p-md-5 h-100">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-light text-dark border">{{ $product->category->name ?? 'General' }}</span>
                    <span class="badge bg-success bg-opacity-10 text-success">
                        <i class="bi bi-check-circle-fill me-1"></i> In Stock ({{ $product->stock_quantity }} units)
                    </span>
                </div>

                <h2 class="fw-bold text-dark mb-2">{{ $product->name }}</h2>

                <!-- Store Info -->
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="text-muted small">Sold by:</span>
                    <a href="{{ route('vendor.show', $product->vendor_profile_id) }}" class="fw-bold text-success text-decoration-none small">
                        <i class="bi bi-shop me-1"></i>{{ $product->vendor->business_name ?? 'Local Store' }}
                    </a>
                </div>

                <!-- Price Breakdown -->
                <div class="p-3 rounded-3 bg-light border mb-4">
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-2 fw-bold text-success">₹{{ number_format($product->effective_price, 2) }}</span>
                        @if($product->sale_price && $product->sale_price < $product->base_price)
                            <span class="text-decoration-line-through text-muted">₹{{ number_format($product->base_price, 2) }}</span>
                            <span class="badge bg-danger">Save ₹{{ number_format($product->base_price - $product->sale_price, 0) }}</span>
                        @endif
                        <span class="text-muted small ms-2">/ {{ $product->unit ?? 'unit' }}</span>
                    </div>

                    @if($product->deposit_amount > 0)
                        <div class="mt-2 pt-2 border-top small text-primary d-flex align-items-center gap-2">
                            <i class="bi bi-arrow-repeat fs-5"></i>
                            <div>
                                <strong>+ ₹{{ number_format($product->deposit_amount, 2) }} Refundable Deposit:</strong>
                                <span class="text-muted">A deposit is collected for the reusable can/container and refunded upon return.</span>
                            </div>
                        </div>
                    @endif
                </div>

                <p class="text-secondary mb-4">{{ $product->description }}</p>

                <!-- Category Specifications -->
                @if(!empty($product->specifications))
                    <div class="mb-4">
                        <h6 class="fw-bold mb-2">Product Specifications</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered small mb-0">
                                <tbody>
                                    @foreach($product->specifications as $key => $val)
                                        <tr>
                                            <th class="bg-light text-muted w-25">{{ $key }}</th>
                                            <td>{{ is_array($val) ? implode(', ', $val) : $val }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Purchase Form -->
                <form action="{{ route('cart.add') }}" method="POST" class="mt-auto">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-auto">
                            <label class="fw-bold small text-muted">Quantity:</label>
                        </div>
                        <div class="col-auto">
                            <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock_quantity }}" class="form-control text-center fw-bold" style="width: 90px;">
                        </div>
                        <div class="col">
                            <button type="submit" class="btn btn-vm-primary btn-lg w-100 rounded-pill">
                                <i class="bi bi-cart-plus me-2"></i>Add to Cart
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Frequently Bought Together (ML Collaborative Filtering) -->
    @if(isset($frequentlyBoughtTogether) && $frequentlyBoughtTogether->isNotEmpty())
        <div class="card border-0 rounded-4 shadow-sm p-4 mb-4" style="background: linear-gradient(135deg, #fffbeb 0%, #ffffff 100%); border: 1px solid rgba(245, 158, 11, 0.25) !important;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <span class="badge bg-warning text-dark rounded-pill px-3 py-1 fw-bold mb-1">
                        <i class="bi bi-basket3-fill me-1"></i> AI Bundle Analysis
                    </span>
                    <h5 class="fw-bold mb-0">Frequently Bought Together</h5>
                    <div class="small text-muted">Customers who purchased this item also regularly add these to their cart:</div>
                </div>
                <span class="badge bg-white text-secondary border rounded-pill px-3 py-2 small d-none d-md-inline-block">
                    <i class="bi bi-diagram-3 text-warning me-1"></i> Basket Co-occurrence Matrix
                </span>
            </div>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
                @foreach($frequentlyBoughtTogether as $fbt)
                    <div class="col">
                        @include('partials.product-card', ['product' => $fbt])
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Similar Products (ML Content-Based Similarity) -->
    @if(isset($similarProducts) && $similarProducts->isNotEmpty())
        <div class="mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-stars text-primary me-1"></i> Similar Products
                    </h5>
                    <div class="small text-muted">Items with matching specifications, category, and taste profile</div>
                </div>
                <span class="badge bg-primary-subtle text-primary border rounded-pill px-3 py-1 small">
                    TF-IDF Cosine Similarity
                </span>
            </div>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3">
                @foreach($similarProducts as $sim)
                    <div class="col">
                        @include('partials.product-card', ['product' => $sim])
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
