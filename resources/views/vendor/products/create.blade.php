@extends('layouts.vendor')

@section('title', 'Add Product - ' . $vendor->business_name)
@section('page_title', 'Add New Product')

@section('content')
<div class="mb-3">
    <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
        <i class="bi bi-arrow-left me-1"></i> Back to Products
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 rounded-4 shadow-sm bg-white p-4 p-md-5">
            <h4 class="fw-bold mb-3">Product Information</h4>

            <form action="{{ route('vendor.products.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label small fw-bold">Product Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. 20L Purified Bubble Water Jar or Shahi Paneer Thali" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Product Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Selling Unit</label>
                        <select name="unit" class="form-select" required>
                            <option value="piece">Piece (Pcs)</option>
                            <option value="jar">Jar / Can (20 Litre)</option>
                            <option value="pack">Pack / Box</option>
                            <option value="bottle">Bottle</option>
                            <option value="kg">Kilogram (kg)</option>
                            <option value="g">Gram (g)</option>
                            <option value="litre">Litre (L)</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Base Price (₹)</label>
                        <input type="number" step="0.01" name="base_price" class="form-control" value="{{ old('base_price') }}" placeholder="e.g. 150.00" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Sale / Discounted Price (₹)</label>
                        <input type="number" step="0.01" name="sale_price" class="form-control" value="{{ old('sale_price') }}" placeholder="e.g. 139.00">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Stock Quantity</label>
                        <input type="number" name="stock_quantity" class="form-control" value="{{ old('stock_quantity', 50) }}" required>
                    </div>
                </div>

                <!-- Reusable Deposit Section -->
                <div class="p-3 rounded-3 bg-light border mb-3">
                    <label class="form-label small fw-bold text-primary">
                        <i class="bi bi-arrow-repeat me-1"></i> Reusable Container Deposit (Optional)
                    </label>
                    <input type="number" step="0.01" name="deposit_amount" class="form-control" value="{{ old('deposit_amount', 0) }}" placeholder="e.g. 150 for 20L reusable water can">
                    <span class="small text-muted">Applicable for drinking water bubble jars and reusable crates. Refunded to customer upon container return.</span>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Preparation / Packing SLA (Minutes)</label>
                        <input type="number" name="prep_time_minutes" class="form-control" value="{{ old('prep_time_minutes', 10) }}" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold">Description & Key Specifications</label>
                    <textarea name="description" rows="3" class="form-control" placeholder="Provide product freshness details, purity grades, or food accompaniments.">{{ old('description') }}</textarea>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-vm-primary btn-lg rounded-pill fw-bold">
                        Save Product to Catalogue
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
