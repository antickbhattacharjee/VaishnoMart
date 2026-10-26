@extends('layouts.app')

@section('title', 'Secure Checkout - VaishnoMart')

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <h3 class="fw-bold mb-1">Secure Checkout</h3>
        <p class="text-muted small">Confirm your delivery location, apply discounts, and complete your order</p>
    </div>

    <form action="{{ route('checkout.place-order') }}" method="POST" id="formCheckout">
        @csrf

        <div class="row g-4">
            <!-- Left Side: Address & Payment Selection -->
            <div class="col-lg-8">

                <!-- 1. Delivery Address Card -->
                <div class="card border-0 rounded-4 shadow-sm bg-white p-4 mb-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="rounded-circle bg-success text-white p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">1</div>
                        <h5 class="fw-bold mb-0">Delivery Address</h5>
                    </div>

                    @if($addresses->isNotEmpty())
                        <div class="row g-3 mb-3">
                            @foreach($addresses as $addr)
                                <div class="col-md-6">
                                    <label class="card h-100 p-3 rounded-3 border address-option cursor-pointer position-relative {{ $defaultAddress && $defaultAddress->id === $addr->id ? 'border-success bg-light' : '' }}">
                                        <input type="radio" name="address_id" value="{{ $addr->id }}" class="form-check-input position-absolute top-0 end-0 m-3" {{ $defaultAddress && $defaultAddress->id === $addr->id ? 'checked' : '' }}>
                                        <div class="fw-bold mb-1"><i class="bi bi-geo-alt text-success me-1"></i>{{ $addr->label }}</div>
                                        <div class="small fw-semibold text-dark">{{ $addr->recipient_name }} ({{ $addr->recipient_phone }})</div>
                                        <div class="small text-muted">{{ $addr->formatted_address }}</div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-2">
                        <button class="btn btn-outline-secondary btn-sm rounded-pill" type="button" data-bs-toggle="collapse" data-bs-target="#newAddressCollapse">
                            <i class="bi bi-plus-lg me-1"></i> Deliver to a New Address
                        </button>
                    </div>

                    <div class="collapse mt-3 {{ $addresses->isEmpty() ? 'show' : '' }}" id="newAddressCollapse">
                        <div class="p-3 bg-light rounded-3 border">
                            <h6 class="fw-bold mb-3">Enter Delivery Address Details</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Contact Phone</label>
                                    <input type="text" name="new_phone" class="form-control form-control-sm" placeholder="10-digit mobile number" value="{{ auth()->user()->phone ?? '' }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">City</label>
                                    <input type="text" name="new_city" class="form-control form-control-sm" value="New Delhi">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Flat / House No. / Building / Street</label>
                                    <input type="text" name="new_address_line1" class="form-control form-control-sm" placeholder="e.g. Flat 301, Tower 2, Barakhamba Road">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Postal PIN Code</label>
                                    <input type="text" name="new_postal_code" class="form-control form-control-sm" value="110001">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Payment Method Card -->
                <div class="card border-0 rounded-4 shadow-sm bg-white p-4 mb-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="rounded-circle bg-success text-white p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">2</div>
                        <h5 class="fw-bold mb-0">Select Payment Method</h5>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="card p-3 rounded-3 border h-100 cursor-pointer text-center payment-option border-success bg-light">
                                <input type="radio" name="payment_method" value="cod" class="form-check-input mx-auto mb-2" checked>
                                <i class="bi bi-cash-coin fs-3 text-success mb-1"></i>
                                <div class="fw-bold">Cash on Delivery</div>
                                <div class="small text-muted">Pay cash/UPI upon verified delivery</div>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <label class="card p-3 rounded-3 border h-100 cursor-pointer text-center payment-option">
                                <input type="radio" name="payment_method" value="online" class="form-check-input mx-auto mb-2">
                                <i class="bi bi-credit-card fs-3 text-primary mb-1"></i>
                                <div class="fw-bold">Online Gateway</div>
                                <div class="small text-muted">Cards, Netbanking & Wallets</div>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <label class="card p-3 rounded-3 border h-100 cursor-pointer text-center payment-option">
                                <input type="radio" name="payment_method" value="upi" class="form-check-input mx-auto mb-2">
                                <i class="bi bi-qr-code-scan fs-3 text-warning mb-1"></i>
                                <div class="fw-bold">UPI Payment</div>
                                <div class="small text-muted">Google Pay, PhonePe, Paytm</div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 3. Delivery Instructions -->
                <div class="card border-0 rounded-4 shadow-sm bg-white p-4">
                    <h6 class="fw-bold mb-2">Delivery Instructions or Notes (Optional)</h6>
                    <textarea name="customer_notes" rows="2" class="form-control" placeholder="e.g. Leave with security guard, or call upon reaching building gate."></textarea>
                </div>
            </div>

            <!-- Right Side: Order Summary & Coupon Card -->
            <div class="col-lg-4">
                <!-- Coupon Input Card -->
                <div class="card border-0 rounded-4 shadow-sm bg-white p-4 mb-3">
                    <h6 class="fw-bold mb-2"><i class="bi bi-tag-fill text-warning me-1"></i> Apply Promo Coupon</h6>
                    <div class="input-group mb-2">
                        <input type="text" id="couponCodeInput" name="coupon_code" class="form-control text-uppercase fw-bold" placeholder="e.g. WELCOME50">
                        <button type="button" class="btn btn-dark" id="btnApplyCoupon">Apply</button>
                    </div>
                    <div id="couponMessageArea" class="small">
                        <span class="text-muted">Use <strong>WELCOME50</strong> for ₹50 off or <strong>VAISHNO10</strong> for 10% off!</span>
                    </div>
                </div>

                <!-- Order Final Breakdown Card -->
                <div class="card border-0 rounded-4 shadow-sm bg-white p-4 sticky-top" style="top: 90px;">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <span class="badge bg-success bg-opacity-10 text-success fw-bold">
                            <i class="bi bi-shop me-1"></i>{{ $vendor->business_name ?? 'Local Store' }}
                        </span>
                        <span class="small text-muted">{{ $cart->itemsCount }} Items</span>
                    </div>

                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Items Subtotal:</span>
                        <span class="fw-semibold">₹{{ number_format($subtotal, 2) }}</span>
                    </div>

                    @if($depositTotal > 0)
                        <div class="d-flex justify-content-between small mb-2 text-primary">
                            <span><i class="bi bi-arrow-repeat me-1"></i>Can Deposit:</span>
                            <span class="fw-semibold">₹{{ number_format($depositTotal, 2) }}</span>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Delivery Charge:</span>
                        <span class="fw-semibold">{{ $deliveryFee == 0 ? 'FREE' : '₹' . number_format($deliveryFee, 2) }}</span>
                    </div>

                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Taxes (5% GST):</span>
                        <span class="fw-semibold">₹{{ number_format($taxAmount, 2) }}</span>
                    </div>

                    <div class="d-flex justify-content-between small mb-2 text-success d-none" id="discountRow">
                        <span>Coupon Discount:</span>
                        <span class="fw-bold" id="discountValueText">-₹0.00</span>
                    </div>

                    <hr class="my-3">

                    <div class="d-flex justify-content-between align-items-baseline mb-4">
                        <span class="fw-bold fs-5">Total Payable:</span>
                        <span class="display-6 fw-bold text-success" id="finalPayableText">
                            ₹{{ number_format($totalAmount, 2) }}
                        </span>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-vm-primary btn-lg rounded-pill fw-bold" id="btnConfirmPlaceOrder">
                            <i class="bi bi-shield-check me-1"></i> Place Order Now
                        </button>
                    </div>

                    <div class="mt-3 text-center small text-muted">
                        By placing this order, you agree to VaishnoMart's cancellation and refund policies.
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Radio button styling toggle
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('border-success', 'bg-light'));
            this.closest('.payment-option').classList.add('border-success', 'bg-light');
        });
    });

    document.querySelectorAll('input[name="address_id"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.address-option').forEach(el => el.classList.remove('border-success', 'bg-light'));
            this.closest('.address-option').classList.add('border-success', 'bg-light');
        });
    });

    // Interactive Coupon Application
    const btnApply = document.getElementById('btnApplyCoupon');
    const inputCode = document.getElementById('couponCodeInput');
    const msgArea = document.getElementById('couponMessageArea');
    const discountRow = document.getElementById('discountRow');
    const discountVal = document.getElementById('discountValueText');
    const finalPayable = document.getElementById('finalPayableText');

    let basePayable = {{ $totalAmount }};

    if (btnApply) {
        btnApply.addEventListener('click', function() {
            const code = inputCode.value.trim();
            if (!code) {
                msgArea.innerHTML = '<span class="text-danger">Please enter a coupon code.</span>';
                return;
            }

            btnApply.disabled = true;
            btnApply.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            fetch("{{ route('checkout.apply-coupon') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ coupon_code: code })
            })
            .then(res => res.json())
            .then(data => {
                btnApply.disabled = false;
                btnApply.innerText = 'Apply';

                if (data.success) {
                    msgArea.innerHTML = `<span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i>${data.message}</span>`;
                    discountRow.classList.remove('d-none');
                    discountVal.innerText = `-₹${parseFloat(data.discount).toFixed(2)}`;

                    const newTotal = Math.max(0, basePayable - parseFloat(data.discount));
                    finalPayable.innerText = `₹${newTotal.toFixed(2)}`;
                } else {
                    msgArea.innerHTML = `<span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i>${data.message}</span>`;
                    discountRow.classList.add('d-none');
                    finalPayable.innerText = `₹${basePayable.toFixed(2)}`;
                }
            })
            .catch(err => {
                btnApply.disabled = false;
                btnApply.innerText = 'Apply';
                msgArea.innerHTML = '<span class="text-danger">Failed to validate coupon.</span>';
            });
        });
    }
</script>
@endpush
