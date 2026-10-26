@extends('layouts.vendor')

@section('title', 'Product Catalogue - ' . $vendor->business_name)
@section('page_title', 'Product Catalogue')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Store Products</h4>
        <p class="text-muted small">Manage items, prices, inventory levels, and deposit amounts</p>
    </div>
    <a href="{{ route('vendor.products.create') }}" class="btn btn-vm-primary btn-sm rounded-pill px-3">
        <i class="bi bi-plus-lg me-1"></i> Add New Product
    </a>
</div>

<div class="card border-0 rounded-4 shadow-sm bg-white p-4">
    @if($products->isEmpty())
        <div class="p-5 text-center text-muted">
            <i class="bi bi-box display-4 mb-2"></i>
            <h5>No products added yet</h5>
            <p class="small mb-3">Add drinking water cans, meals, dry fruits, or grocery staples to begin selling.</p>
            <a href="{{ route('vendor.products.create') }}" class="btn btn-vm-primary btn-sm rounded-pill">Add Your First Product</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock Level</th>
                        <th>Unit / Deposit</th>
                        <th>Availability</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $p)
                        <tr>
                            <td class="py-3">
                                <div class="fw-bold text-dark">{{ $p->name }}</div>
                                <div class="small text-muted">SKU: {{ $p->sku ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $p->category->name ?? 'General' }}</span>
                            </td>
                            <td>
                                <span class="fw-bold text-success">₹{{ number_format($p->effective_price, 2) }}</span>
                                @if($p->sale_price && $p->sale_price < $p->base_price)
                                    <div class="small text-decoration-line-through text-muted">₹{{ number_format($p->base_price, 2) }}</div>
                                @endif
                            </td>
                            <td>
                                @if($p->stock_quantity <= 5)
                                    <span class="badge bg-warning text-dark">{{ $p->stock_quantity }} Low</span>
                                @else
                                    <span class="badge bg-success bg-opacity-10 text-success">{{ $p->stock_quantity }} In Stock</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border">{{ $p->unit }}</span>
                                @if($p->deposit_amount > 0)
                                    <div class="small text-info fw-semibold mt-1">+ ₹{{ number_format($p->deposit_amount, 0) }} Deposit</div>
                                @endif
                            </td>
                            <td>
                                @if($p->is_available && $p->is_active)
                                    <span class="badge bg-success">Available</span>
                                @else
                                    <span class="badge bg-secondary">Hidden / Offline</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('vendor.products.edit', $p->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
