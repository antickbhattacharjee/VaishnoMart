@extends('layouts.app')

@section('title', $category->name . ' - VaishnoMart')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $category->name }}</li>
        </ol>
    </nav>

    <!-- Category Header Card -->
    <div class="card border-0 rounded-4 p-4 mb-4 text-white shadow-sm" style="background: linear-gradient(135deg, #064e3b, #059669);">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-white bg-opacity-20 p-3 text-white fs-2">
                <i class="bi {{ $category->icon ?? 'bi-bag-fill' }}"></i>
            </div>
            <div>
                <h2 class="fw-bold mb-1">{{ $category->name }}</h2>
                <p class="mb-0 text-light opacity-90 small">{{ $category->description ?? 'Explore authentic products directly from certified local vendors.' }}</p>
            </div>
        </div>
    </div>

    <!-- Participating Vendors in this Category -->
    @if($vendors->isNotEmpty())
        <div class="mb-4">
            <h5 class="fw-bold mb-3">Certified Stores in this Category</h5>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
                @foreach($vendors as $v)
                    <div class="col">
                        <div class="card border-0 rounded-3 shadow-sm p-3 bg-white h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="fw-bold mb-0 text-truncate">{{ $v->business_name }}</h6>
                                <span class="badge bg-success bg-opacity-10 text-success small">Open</span>
                            </div>
                            <p class="text-muted small text-truncate mb-2">{{ $v->address }}</p>
                            <a href="{{ route('vendor.show', $v->id) }}" class="btn btn-sm btn-outline-success">
                                View Store Catalogue &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Products Grid -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Products Available ({{ $products->total() }})</h5>
    </div>

    @if($products->isEmpty())
        <div class="card border-0 rounded-4 p-5 text-center bg-white shadow-sm">
            <i class="bi bi-box-seam text-muted display-4 mb-3"></i>
            <h5>No products available in this category yet</h5>
            <p class="text-muted small">Local vendors are continuously adding fresh inventory.</p>
            <div>
                <a href="{{ route('home') }}" class="btn btn-vm-primary btn-sm">Explore Other Categories</a>
            </div>
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
@endsection
