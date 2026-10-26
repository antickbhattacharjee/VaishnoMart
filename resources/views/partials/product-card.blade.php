<div class="vm-product-card">
    <div class="vm-product-img-box">
        @if($product->category && $product->category->icon)
            <i class="bi {{ $product->category->icon }} vm-product-icon"></i>
        @else
            <i class="bi bi-box-seam vm-product-icon"></i>
        @endif

        @if($product->deposit_amount > 0)
            <span class="position-absolute top-0 start-0 m-2 badge bg-info text-dark shadow-sm">
                <i class="bi bi-arrow-repeat me-1"></i>₹{{ number_format($product->deposit_amount, 0) }} Deposit
            </span>
        @endif

        @if($product->stock_quantity <= 5 && $product->stock_quantity > 0)
            <span class="position-absolute top-0 end-0 m-2 badge bg-warning text-dark shadow-sm">
                Only {{ $product->stock_quantity }} left!
            </span>
        @elseif($product->stock_quantity <= 0)
            <span class="position-absolute top-0 end-0 m-2 badge bg-danger text-white shadow-sm">
                Out of Stock
            </span>
        @endif
    </div>

    <div class="vm-store-tag">
        <i class="bi bi-shop text-success"></i>
        <a href="{{ route('vendor.show', $product->vendor_profile_id) }}" class="text-muted text-decoration-none text-truncate">
            {{ $product->vendor->business_name ?? 'Local Store' }}
        </a>
    </div>

    <a href="{{ route('product.show', $product->slug) }}" class="vm-product-title text-decoration-none">
        {{ $product->name }}
    </a>

    <div class="small text-muted mb-2">
        <span class="badge bg-light text-dark border">{{ $product->unit ?? 'Unit' }}</span>
        @if($product->prep_time_minutes > 0)
            <span class="text-muted ms-1"><i class="bi bi-clock"></i> {{ $product->prep_time_minutes }}m</span>
        @endif
    </div>

    <div class="mt-auto d-flex justify-content-between align-items-center pt-2 border-top">
        <div>
            <span class="vm-price-tag">₹{{ number_format($product->effective_price, 2) }}</span>
            @if($product->sale_price && $product->sale_price < $product->base_price)
                <span class="vm-price-old">₹{{ number_format($product->base_price, 0) }}</span>
            @endif
        </div>

        @if($product->stock_quantity > 0)
            <form action="{{ route('cart.add') }}" method="POST" class="add-to-cart-form">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="btn btn-sm btn-vm-primary rounded-pill px-3">
                    <i class="bi bi-plus-lg me-1"></i>Add
                </button>
            </form>
        @else
            <button class="btn btn-sm btn-secondary rounded-pill px-3" disabled>Sold Out</button>
        @endif
    </div>
</div>
