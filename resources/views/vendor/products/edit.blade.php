@extends('layouts.vendor')

@section('title', 'Edit ' . $product->name . ' - ' . $vendor->business_name)
@section('page_title', 'Edit Product')

@section('content')
<div class="mb-3">
    <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
        <i class="bi bi-arrow-left me-1"></i> Back to Products
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 rounded-4 shadow-sm bg-white p-4 p-md-5">
            <h4 class="fw-bold mb-3">Edit Product Information</h4>

            <form action="{{ route('vendor.products.update', $product->id) }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label small fw-bold">Product Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Product Category</label>
                        <select name="category_id" class="form-select" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Selling Unit</label>
                        <select name="unit" class="form-select" required>
                            @foreach(['piece', 'jar', 'pack', 'bottle', 'kg', 'g', 'litre'] as $u)
                                <option value="{{ $u }}" {{ old('unit', $product->unit) == $u ? 'selected' : '' }}>
                                    {{ ucfirst($u) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Base Price (₹)</label>
                        <input type="number" step="0.01" name="base_price" class="form-control" value="{{ old('base_price', $product->base_price) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Sale / Discounted Price (₹)</label>
                        <input type="number" step="0.01" name="sale_price" class="form-control" value="{{ old('sale_price', $product->sale_price) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Stock Quantity</label>
                        <input type="number" name="stock_quantity" class="form-control" value="{{ old('stock_quantity', $product->stock_quantity) }}" required>
                    </div>
                </div>

                <!-- Reusable Deposit Section -->
                <div class="p-3 rounded-3 bg-light border mb-3">
                    <label class="form-label small fw-bold text-primary">
                        <i class="bi bi-arrow-repeat me-1"></i> Reusable Container Deposit (Optional)
                    </label>
                    <input type="number" step="0.01" name="deposit_amount" class="form-control" value="{{ old('deposit_amount', $product->deposit_amount) }}">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Preparation / Packing SLA (Minutes)</label>
                        <input type="number" name="prep_time_minutes" class="form-control" value="{{ old('prep_time_minutes', $product->prep_time_minutes) }}" required>
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" name="is_available" value="1" id="switchAvailable" {{ $product->is_available ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small" for="switchAvailable">Product Available for Ordering</label>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold">Description</label>
                    <textarea name="description" rows="3" class="form-control">{{ old('description', $product->description) }}</textarea>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-vm-primary btn-lg rounded-pill fw-bold">
                        Update Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
