@extends('layouts.app')

@section('title', 'Your Shopping Cart - VaishnoMart')

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <h3 class="fw-bold mb-1">Your Shopping Cart</h3>
        <p class="text-muted small">Review your items before proceeding to secure checkout</p>
    </div>

    @if($items->isEmpty())
        <div class="card border-0 rounded-4 p-5 text-center bg-white shadow-sm">
            <i class="bi bi-cart-x text-muted display-3 mb-3"></i>
            <h4 class="fw-bold">Your cart is currently empty</h4>
            <p class="text-muted small mb-4">Discover verified neighbourhood vendors and add daily essentials or hot meals.</p>
            <div>
                <a href="{{ route('home') }}" class="btn btn-vm-primary px-4 py-2 rounded-pill">
                    <i class="bi bi-shop me-1"></i> Start Shopping
                </a>
            </div>
        </div>
    @else
        <div class="row g-4">
            <!-- Cart Items Table -->
            <div class="col-lg-8">
                <!-- Single Vendor Info Banner -->
                @if($vendor)
                    <div class="alert alert-success border-0 rounded-4 shadow-sm mb-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-shop fs-4 text-success"></i>
                            <div>
                                <div class="fw-bold text-dark">Fulfilled by: {{ $vendor->business_name }}</div>
                                <div class="small text-muted">{{ $vendor->address }} &bull; ~{{ $distance }} km from your location</div>
                            </div>
                        </div>
                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 px-2 py-1 small">
                            Single Store Order
                        </span>
                    </div>
                @endif

                <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden mb-3">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light small text-muted text-uppercase">
                                <tr>
                                    <th class="ps-4">Product</th>
                                    <th>Price</th>
                                    <th style="width: 140px;">Quantity</th>
                                    <th>Subtotal</th>
                                    <th class="pe-4 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="rounded-3 bg-light p-2 text-success fs-4 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                    <i class="bi {{ $item->product->category->icon ?? 'bi-box-seam' }}"></i>
                                                </div>
                                                <div>
                                                    <a href="{{ route('product.show', $item->product->slug) }}" class="fw-bold text-dark text-decoration-none">
                                                        {{ $item->product->name }}
                                                    </a>
                                                    @if($item->variant)
                                                        <div class="small text-muted">Variant: {{ $item->variant->name }}</div>
                                                    @endif
                                                    @if($item->product->deposit_amount > 0)
                                                        <span class="badge bg-info text-dark small">
                                                            + ₹{{ number_format($item->product->deposit_amount * $item->quantity, 0) }} Can Deposit
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-semibold">₹{{ number_format($item->unit_price, 2) }}</span>
                                        </td>
                                        <td>
                                            <form action="{{ route('cart.update', $item->id) }}" method="POST" class="d-flex align-items-center gap-1">
                                                @csrf
                                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $item->product->stock_quantity }}" class="form-control form-control-sm text-center fw-bold" style="width: 70px;">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Update">
                                                    <i class="bi bi-arrow-repeat"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success">₹{{ number_format($item->subtotal, 2) }}</span>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <form action="{{ route('cart.remove', $item->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Remove">
                                                    <i class="bi bi-trash3 fs-5"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary rounded-pill btn-sm px-3">
                        <i class="bi bi-arrow-left me-1"></i> Continue Shopping
                    </a>
                    <form action="{{ route('cart.clear') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-light text-danger border rounded-pill btn-sm px-3">
                            <i class="bi bi-x-circle me-1"></i> Clear Cart
                        </button>
                    </form>
                </div>
            </div>

            <!-- Summary Order Breakdown -->
            <div class="col-lg-4">
                <div class="card border-0 rounded-4 shadow-sm bg-white p-4 sticky-top" style="top: 90px;">
                    <h5 class="fw-bold mb-3">Order Summary</h5>

                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Items Subtotal:</span>
                        <span class="fw-semibold">₹{{ number_format($subtotal, 2) }}</span>
                    </div>

                    @if($depositTotal > 0)
                        <div class="d-flex justify-content-between small mb-2 text-primary">
                            <span><i class="bi bi-arrow-repeat me-1"></i>Reusable Can Deposit:</span>
                            <span class="fw-semibold">₹{{ number_format($depositTotal, 2) }}</span>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Estimated Delivery Fee:</span>
                        @if($deliveryFee == 0)
                            <span class="fw-bold text-success">FREE</span>
                        @else
                            <span class="fw-semibold">₹{{ number_format($deliveryFee, 2) }}</span>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between small mb-3">
                        <span class="text-muted">Applicable Taxes (5% GST):</span>
                        <span class="fw-semibold">₹{{ number_format($taxAmount, 2) }}</span>
                    </div>

                    <hr class="my-3">

                    <div class="d-flex justify-content-between align-items-baseline mb-4">
                        <span class="fw-bold fs-5">Estimated Total:</span>
                        <span class="display-6 fw-bold text-success">₹{{ number_format($estimatedTotal, 2) }}</span>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="{{ route('checkout.index') }}" class="btn btn-vm-primary btn-lg rounded-pill" id="btnProceedCheckout">
                            Proceed to Checkout <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>

                    <div class="mt-3 text-center small text-muted">
                        <i class="bi bi-shield-check text-success me-1"></i> Secure OTP verification & direct vendor dispatch
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
