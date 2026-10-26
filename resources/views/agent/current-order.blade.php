@extends('layouts.agent')

@section('title', 'Active Delivery #' . $order->order_number)

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center">
    <a href="{{ route('agent.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
        <i class="bi bi-arrow-left me-1"></i> Dashboard
    </a>
    <span class="badge bg-{{ $order->status_badge_class }} rounded-pill px-3 py-2 fw-bold">
        {{ $order->status_label }}
    </span>
</div>

<div class="card border-0 rounded-4 shadow-sm bg-white p-4 mb-4">
    <div class="d-flex justify-content-between align-items-baseline mb-2">
        <span class="text-muted small">Order Reference</span>
        <span class="fw-bold font-monospace text-dark fs-5">#{{ $order->order_number }}</span>
    </div>
    <div class="d-flex justify-content-between align-items-baseline border-bottom pb-3 mb-3">
        <span class="text-muted small">Trip Earning:</span>
        <span class="fs-4 fw-bold text-success">₹{{ number_format($order->delivery_agent_earnings_amount, 2) }}</span>
    </div>

    <!-- STAGE A: PICKUP VERIFICATION (If order not yet picked up) -->
    @if(in_array($order->status, [App\Models\Order::STATUS_DELIVERY_ASSIGNED, App\Models\Order::STATUS_READY_FOR_PICKUP]))
        <div class="p-3 rounded-4 mb-4" style="background: linear-gradient(135deg, #fef3c7, #fde68a); border: 2px solid #f59e0b;">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-dark text-warning fw-bold">STEP 1: PICKUP FROM STORE</span>
            </div>
            <h5 class="fw-bold text-dark mb-1">{{ $order->vendor->business_name }}</h5>
            <p class="small text-dark mb-2">{{ $order->vendor->address }}</p>
            <div class="d-flex gap-2 mb-3">
                <a href="https://www.google.com/maps/search/?api=1&query={{ $order->vendor->latitude }},{{ $order->vendor->longitude }}" target="_blank" class="btn btn-dark btn-sm rounded-pill px-3">
                    <i class="bi bi-geo-alt-fill text-warning me-1"></i> Navigate to Store
                </a>
                <a href="tel:{{ $order->vendor->phone }}" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                    <i class="bi bi-telephone-fill me-1"></i> Call Store
                </a>
            </div>

            <!-- Pickup OTP Verification Form -->
            <div class="card border-0 rounded-4 p-3 p-md-4 bg-white shadow-sm">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-shield-lock-fill text-success me-1"></i> Store Handover OTP
                    </h5>
                    <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold" onclick="fillOtpGroup('pickup', '123456')">
                        <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Quick Fill 123456
                    </button>
                </div>
                <p class="text-secondary small mb-3">
                    Collect the packed order from the store manager and ask for their 6-digit pickup verification code.
                </p>

                @if($errors->has('pickup_otp'))
                    <div class="alert alert-danger py-2 px-3 small rounded-3 mb-3 d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                        <div>{{ $errors->first('pickup_otp') }}</div>
                    </div>
                @endif

                <form action="{{ route('agent.deliveries.verify-pickup', $order->id) }}" method="POST" id="pickupOtpForm">
                    @csrf
                    <!-- Hidden Master Input for Form Submission -->
                    <input type="hidden" name="pickup_otp" id="pickup_otp_hidden" value="" required>

                    <!-- Large Segmented 6-Digit OTP Inputs -->
                    <div class="d-flex justify-content-center gap-2 gap-sm-3 my-3" id="pickup-otp-container">
                        @for($i = 0; $i < 6; $i++)
                            <input type="text"
                                   inputmode="numeric"
                                   pattern="[0-9]*"
                                   maxlength="1"
                                   class="form-control text-center fw-bold otp-digit-box"
                                   data-group="pickup"
                                   data-index="{{ $i }}"
                                   autocomplete="off"
                                   {{ $i === 0 ? 'autofocus' : '' }}>
                        @endfor
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100 py-3 rounded-4 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2 mt-3">
                        <i class="bi bi-box-seam-fill fs-5"></i> Verify Pickup & Proceed
                    </button>
                </form>

                <form action="{{ route('agent.deliveries.resend-otp', $order->id) }}" method="POST" class="text-center mt-3 pt-2 border-top">
                    @csrf
                    <button type="submit" class="btn btn-link btn-sm text-decoration-none text-muted p-0">
                        <i class="bi bi-arrow-clockwise me-1"></i> Store code expired or not received? Request / Resend Code
                    </button>
                </form>
            </div>
        </div>

    <!-- STAGE B: CUSTOMER DELIVERY CONFIRMATION (If out for delivery) -->
    @elseif($order->status === App\Models\Order::STATUS_OUT_FOR_DELIVERY)
        <div class="p-3 rounded-4 mb-4" style="background: linear-gradient(135deg, #ecfdf5, #d1fae5); border: 2px solid #059669;">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-success text-white fw-bold">STEP 2: DELIVER TO CUSTOMER</span>
            </div>
            <h5 class="fw-bold text-dark mb-1">{{ $order->deliveryAddress->recipient_name ?? $order->customer->name }}</h5>
            <p class="small text-dark mb-2">{{ $order->deliveryAddress->formatted_address ?? 'Customer Destination' }}</p>
            <div class="d-flex gap-2 mb-3">
                <a href="https://www.google.com/maps/search/?api=1&query={{ $order->deliveryAddress->latitude ?? 28.6315 }},{{ $order->deliveryAddress->longitude ?? 77.2200 }}" target="_blank" class="btn btn-success btn-sm rounded-pill px-3">
                    <i class="bi bi-geo-alt-fill text-white me-1"></i> Navigate to Customer
                </a>
                <a href="tel:{{ $order->deliveryAddress->phone ?? $order->customer->phone }}" class="btn btn-outline-success btn-sm rounded-pill px-3">
                    <i class="bi bi-telephone-fill me-1"></i> Call Customer
                </a>
            </div>

            @if($order->payment_method === 'cod')
                <div class="card border-0 rounded-3 p-3 bg-white mb-3 shadow-sm border-start border-warning border-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-warning text-dark fw-bold mb-1">CASH ON DELIVERY</span>
                            <div class="small text-muted">Collect exact amount before handing over parcel</div>
                        </div>
                        <div class="fs-3 fw-bold text-dark font-monospace">₹{{ number_format($order->total_amount, 2) }}</div>
                    </div>
                </div>
            @endif

            <!-- Customer Delivery OTP Verification Form -->
            <div class="card border-0 rounded-4 p-3 p-md-4 bg-white shadow-sm">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-shield-check text-success me-1"></i> Customer Confirmation OTP
                    </h5>
                    <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold" onclick="fillOtpGroup('delivery', '123456')">
                        <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Quick Fill 123456
                    </button>
                </div>
                <p class="text-secondary small mb-3">
                    Hand over the package to the customer and ask for their 6-digit confirmation OTP shown on their screen.
                </p>

                @if($errors->has('delivery_otp'))
                    <div class="alert alert-danger py-2 px-3 small rounded-3 mb-3 d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                        <div>{{ $errors->first('delivery_otp') }}</div>
                    </div>
                @endif

                <form action="{{ route('agent.deliveries.verify-delivery', $order->id) }}" method="POST" id="deliveryOtpForm">
                    @csrf
                    <!-- Hidden Master Input for Form Submission -->
                    <input type="hidden" name="delivery_otp" id="delivery_otp_hidden" value="" required>

                    <!-- Large Segmented 6-Digit OTP Inputs -->
                    <div class="d-flex justify-content-center gap-2 gap-sm-3 my-3" id="delivery-otp-container">
                        @for($i = 0; $i < 6; $i++)
                            <input type="text"
                                   inputmode="numeric"
                                   pattern="[0-9]*"
                                   maxlength="1"
                                   class="form-control text-center fw-bold otp-digit-box"
                                   data-group="delivery"
                                   data-index="{{ $i }}"
                                   autocomplete="off"
                                   {{ $i === 0 ? 'autofocus' : '' }}>
                        @endfor
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100 py-3 rounded-4 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2 mt-3">
                        <i class="bi bi-check-circle-fill fs-5"></i> Confirm & Complete Delivery
                    </button>
                </form>

                <form action="{{ route('agent.deliveries.resend-otp', $order->id) }}" method="POST" class="text-center mt-3 pt-2 border-top">
                    @csrf
                    <button type="submit" class="btn btn-link btn-sm text-decoration-none text-muted p-0">
                        <i class="bi bi-arrow-clockwise me-1"></i> Customer OTP expired? Resend New OTP to Customer
                    </button>
                </form>
            </div>
        </div>
    @endif

    <!-- Package Items to Deliver -->
    <h6 class="fw-bold mb-2">Package Items Checklist ({{ $order->items->count() }})</h6>
    <div class="list-group list-group-flush border rounded-3 mb-3">
        @foreach($order->items as $item)
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-bold text-dark">{{ $item->product_name }}</div>
                    @if($item->variant_name)
                        <div class="small text-muted">{{ $item->variant_name }}</div>
                    @endif
                </div>
                <span class="badge bg-light text-dark border fs-6">Qty: {{ $item->quantity }}</span>
            </div>
        @endforeach
    </div>

    @if($order->customer_notes)
        <div class="p-2 bg-light rounded-3 small text-muted">
            <i class="bi bi-info-circle me-1 text-primary"></i> <strong>Special Note:</strong> {{ $order->customer_notes }}
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.otp-digit-box {
    width: 44px;
    height: 60px;
    font-size: 1.75rem !important;
    font-family: 'SF Mono', Consolas, 'Liberation Mono', Menlo, monospace;
    font-weight: 800;
    text-align: center;
    border-radius: 12px;
    border: 2px solid #cbd5e1;
    background-color: #f8fafc;
    color: #0f172a;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    padding: 0;
}
@media (min-width: 576px) {
    .otp-digit-box {
        width: 56px;
        height: 70px;
        font-size: 2.2rem !important;
        border-radius: 14px;
    }
}
.otp-digit-box:focus {
    border-color: #059669 !important;
    background-color: #ffffff !important;
    box-shadow: 0 0 0 4px rgba(5, 150, 105, 0.25) !important;
    outline: none !important;
    transform: translateY(-2px);
}
.otp-digit-box.filled {
    border-color: #059669;
    background-color: #ecfdf5;
    color: #064e3b;
}
</style>
@endpush

@push('scripts')
<script>
function syncOtp(group) {
    const boxes = document.querySelectorAll(`.otp-digit-box[data-group="${group}"]`);
    const hiddenInput = document.getElementById(`${group}_otp_hidden`);
    let otpVal = '';
    boxes.forEach(box => {
        if (box.value) {
            otpVal += box.value;
            box.classList.add('filled');
        } else {
            box.classList.remove('filled');
        }
    });
    if (hiddenInput) {
        hiddenInput.value = otpVal;
    }
}

function fillOtpGroup(group, code) {
    const boxes = document.querySelectorAll(`.otp-digit-box[data-group="${group}"]`);
    const digits = code.toString().split('');
    boxes.forEach((box, i) => {
        box.value = digits[i] || '';
    });
    syncOtp(group);
    if (boxes[boxes.length - 1]) {
        boxes[boxes.length - 1].focus();
    }
}

document.addEventListener('DOMContentLoaded', function () {
    ['pickup', 'delivery'].forEach(group => {
        const boxes = document.querySelectorAll(`.otp-digit-box[data-group="${group}"]`);
        if (!boxes.length) return;

        boxes.forEach((box, idx) => {
            // Digit entry and auto-advance
            box.addEventListener('input', function (e) {
                const val = this.value.replace(/\D/g, '');
                this.value = val ? val[val.length - 1] : '';
                syncOtp(group);

                if (this.value && idx < boxes.length - 1) {
                    boxes[idx + 1].focus();
                    boxes[idx + 1].select();
                }
            });

            // Backspace and Arrow navigation
            box.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace') {
                    if (!this.value && idx > 0) {
                        boxes[idx - 1].focus();
                        boxes[idx - 1].value = '';
                        syncOtp(group);
                    }
                } else if (e.key === 'ArrowLeft' && idx > 0) {
                    boxes[idx - 1].focus();
                } else if (e.key === 'ArrowRight' && idx < boxes.length - 1) {
                    boxes[idx + 1].focus();
                }
            });

            // Paste full code (e.g. from SMS or clipboard)
            box.addEventListener('paste', function (e) {
                e.preventDefault();
                const pastedData = (e.clipboardData || window.clipboardData).getData('text');
                const cleanDigits = pastedData.replace(/\D/g, '').slice(0, 6);
                if (cleanDigits) {
                    fillOtpGroup(group, cleanDigits);
                }
            });

            box.addEventListener('focus', function () {
                this.select();
            });
        });

        // Form submit safety validator
        const form = document.getElementById(`${group}OtpForm`);
        if (form) {
            form.addEventListener('submit', function (e) {
                syncOtp(group);
                const hiddenInput = document.getElementById(`${group}_otp_hidden`);
                if (!hiddenInput || hiddenInput.value.length < 4) {
                    e.preventDefault();
                    alert('Please enter all 6 digits of the OTP.');
                    boxes[0].focus();
                }
            });
        }
    });
});
</script>
@endpush
