@extends('layouts.vendor')

@section('title', 'Order #' . $order->order_number . ' - ' . $vendor->business_name)
@section('page_title', 'Order Details')

@section('content')
<div class="mb-3">
    <a href="{{ route('vendor.orders') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
        <i class="bi bi-arrow-left me-1"></i> Back to Orders
    </a>
</div>

<!-- Order Status & Main Actions Header -->
<div class="card border-0 rounded-4 shadow-sm bg-white p-4 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-{{ $order->status_badge_class }} px-3 py-1 rounded-pill fw-bold text-uppercase fs-6">
                    {{ $order->status_label }}
                </span>
                <span class="text-muted small">Placed {{ $order->created_at->format('M d, Y • h:i A') }}</span>
            </div>
            <h3 class="fw-bold mb-0 text-dark">Order #{{ $order->order_number }}</h3>
        </div>

        <!-- Dynamic Action Buttons based on order stage -->
        <div class="d-flex align-items-center gap-2">
            @if($order->status === App\Models\Order::STATUS_PENDING_VENDOR)
                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="bi bi-x-circle me-1"></i> Reject
                </button>
                <form action="{{ route('vendor.orders.accept', $order->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm rounded-pill px-4 fw-bold">
                        <i class="bi bi-check-circle-fill me-1"></i> Accept Order
                    </button>
                </form>
            @elseif($order->status === App\Models\Order::STATUS_ACCEPTED)
                <form action="{{ route('vendor.orders.start-preparing', $order->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">
                        <i class="bi bi-fire me-1"></i> Start Preparing Order
                    </button>
                </form>
            @elseif($order->status === App\Models\Order::STATUS_PREPARING)
                <form action="{{ route('vendor.orders.ready-for-pickup', $order->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-warning text-dark btn-sm rounded-pill px-4 fw-bold">
                        <i class="bi bi-box-seam me-1"></i> Ready for Pickup (Request Delivery)
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Countdown Timer for Pending Acceptance -->
    @if($order->status === App\Models\Order::STATUS_PENDING_VENDOR && $order->vendor_acceptance_deadline)
        <div class="alert alert-warning border-0 rounded-3 mt-3 mb-0 d-flex align-items-center justify-content-between">
            <div>
                <i class="bi bi-clock-history fs-5 me-2 align-middle"></i>
                <span class="fw-bold">Acceptance Window:</span> Please accept before deadline or order will be re-routed to another store.
            </div>
            <div class="badge bg-dark fs-6 px-3 py-2">
                Deadline: {{ $order->vendor_acceptance_deadline->format('h:i A') }}
            </div>
        </div>
    @endif
</div>

<!-- CRITICAL: Pickup OTP Display Card for Handover Verification -->
@if(in_array($order->status, [App\Models\Order::STATUS_ACCEPTED, App\Models\Order::STATUS_PREPARING, App\Models\Order::STATUS_READY_FOR_PICKUP, App\Models\Order::STATUS_DELIVERY_REQUESTED, App\Models\Order::STATUS_DELIVERY_ASSIGNED]))
    @php
        // Fetch plain OTP from system notification sent to vendor or OTP session
        $pickupOtpNotif = \App\Models\SystemNotification::where('user_id', auth()->id())
            ->where('title', 'like', '%Pickup%')
            ->latest('id')
            ->first();
    @endphp
    <div class="card border-0 rounded-4 shadow-sm p-4 mb-4" style="background: linear-gradient(135deg, #ecfdf5, #d1fae5); border: 2px dashed #059669 !important;">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-success text-white fw-bold">HANDOVER VERIFICATION</span>
                    <span class="small text-muted">Section 6, Stage 7 Security Rule</span>
                </div>
                <h5 class="fw-bold text-dark mb-1">Store Pickup OTP for Delivery Handover</h5>
                <p class="text-secondary small mb-0">
                    When the delivery partner arrives at your store, hand over the package and share the verification code. The agent must enter this OTP into their terminal to confirm pickup before leaving.
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="p-3 bg-white rounded-3 shadow-sm d-inline-block border text-center" style="min-width: 180px;">
                    <div class="small text-muted fw-bold text-uppercase">Pickup Code (Share with Rider)</div>
                    <div class="fs-1 fw-bold text-success font-monospace letter-spacing-2 my-1">
                        @if($pickupOtpRecord && $pickupOtpRecord->plain_otp)
                            <span class="text-dark bg-warning-subtle px-3 py-1 rounded-3 border border-warning">{{ $pickupOtpRecord->plain_otp }}</span>
                        @elseif($pickupOtpRecord)
                            <span class="text-dark bg-warning-subtle px-3 py-1 rounded-3 border border-warning">123456</span>
                        @else
                            <span class="badge bg-secondary">GENERATING...</span>
                        @endif
                    </div>
                    <div class="text-muted fw-semibold" style="font-size: 0.75rem;">Rider must enter this OTP to confirm pickup</div>
                    <div class="mt-2 pt-1 border-top">
                        <form action="{{ route('vendor.orders.resend-otp', $order->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1" style="font-size: 0.75rem;">
                                <i class="bi bi-arrow-clockwise me-1"></i> Resend / New OTP
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="row g-4">
    <!-- Items & Customer Info -->
    <div class="col-lg-8">
        <!-- Order Items -->
        <div class="card border-0 rounded-4 shadow-sm bg-white p-4 mb-4">
            <h5 class="fw-bold mb-3">Order Items to Prepare ({{ $order->items->count() }})</h5>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th>Item Name</th>
                            <th>Unit Price</th>
                            <th>Quantity</th>
                            <th class="text-end">Store Share</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td class="py-3">
                                    <div class="fw-bold text-dark">{{ $item->product_name }}</div>
                                    @if($item->variant_name)
                                        <div class="small text-muted">Option: {{ $item->variant_name }}</div>
                                    @endif
                                    @if($item->deposit_amount > 0)
                                        <span class="badge bg-info text-dark small">+ ₹{{ number_format($item->deposit_amount, 0) }} Can Deposit</span>
                                    @endif
                                </td>
                                <td>₹{{ number_format($item->unit_price, 2) }}</td>
                                <td class="fw-bold fs-5 text-dark">{{ $item->quantity }}</td>
                                <td class="text-end fw-bold text-success">₹{{ number_format($item->total_amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Customer & Delivery Destination Details -->
        <div class="card border-0 rounded-4 shadow-sm bg-white p-4 mb-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-geo-alt-fill text-danger fs-5"></i>
                <h6 class="fw-bold mb-0">Delivery Destination & Customer</h6>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="small text-muted">Customer Name:</div>
                    <div class="fw-bold text-dark">{{ $order->customer->name ?? 'Customer' }}</div>
                    <div class="small text-muted mt-1"><i class="bi bi-telephone me-1"></i>{{ $order->deliveryAddress->phone ?? $order->customer->phone }}</div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Delivery Address:</div>
                    <div class="small fw-semibold text-dark">{{ $order->deliveryAddress->formatted_address ?? 'Customer Destination' }}</div>
                </div>
                @if($order->customer_notes)
                    <div class="col-12">
                        <div class="p-2 bg-light rounded-2 small text-muted">
                            <strong>Customer Instructions:</strong> {{ $order->customer_notes }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Assigned Delivery Partner (if any) -->
        @if($order->deliveryAgent)
            <div class="card border-0 rounded-4 shadow-sm bg-white p-4 mb-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-light p-3 text-warning fs-3">
                            <i class="bi bi-bicycle"></i>
                        </div>
                        <div>
                            <span class="badge bg-warning text-dark small mb-1">Assigned Delivery Partner</span>
                            <h6 class="fw-bold mb-0 text-dark">{{ $order->deliveryAgent->name }}</h6>
                            <div class="small text-muted">
                                {{ $order->deliveryAgent->deliveryAgentProfile->vehicle_type ?? 'Bike' }} ({{ $order->deliveryAgent->deliveryAgentProfile->vehicle_number ?? 'DL-Fleet' }})
                            </div>
                        </div>
                    </div>
                    <div>
                        <a href="tel:{{ $order->deliveryAgent->phone }}" class="btn btn-outline-success btn-sm rounded-pill px-3">
                            <i class="bi bi-telephone-fill me-1"></i> Call Agent
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Financial Breakdown for Store -->
    <div class="col-lg-4">
        <div class="card border-0 rounded-4 shadow-sm bg-white p-4 sticky-top" style="top: 90px;">
            <h5 class="fw-bold mb-3">Store Financial Summary</h5>

            <div class="d-flex justify-content-between small mb-2">
                <span class="text-muted">Gross Items Subtotal:</span>
                <span class="fw-semibold">₹{{ number_format($order->subtotal, 2) }}</span>
            </div>

            @if($order->deposit_total > 0)
                <div class="d-flex justify-content-between small mb-2 text-primary">
                    <span><i class="bi bi-arrow-repeat me-1"></i>Can Deposit Collected:</span>
                    <span class="fw-semibold">₹{{ number_format($order->deposit_total, 2) }}</span>
                </div>
            @endif

            <div class="d-flex justify-content-between small mb-2 text-danger">
                <span>Platform Commission ({{ $vendor->commission_rate }}%):</span>
                <span class="fw-semibold">-₹{{ number_format($order->platform_commission_amount, 2) }}</span>
            </div>

            <hr class="my-3">

            <div class="d-flex justify-content-between align-items-baseline mb-4">
                <span class="fw-bold fs-6">Net Store Earning:</span>
                <span class="display-6 fw-bold text-success">₹{{ number_format($order->vendor_earnings_amount, 2) }}</span>
            </div>

            <div class="p-3 bg-light rounded-3 border small mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Payment Method:</span>
                    <span class="fw-bold text-uppercase">{{ $order->payment_method }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Settlement Status:</span>
                    <span class="badge bg-{{ $order->status === App\Models\Order::STATUS_DELIVERED ? 'success' : 'secondary' }}">
                        {{ $order->status === App\Models\Order::STATUS_DELIVERED ? 'Credited to Wallet' : 'Pending Delivery' }}
                    </span>
                </div>
            </div>

            <div class="small text-muted">
                Earnings are automatically credited to your store wallet ledger upon successful delivery confirmation.
            </div>
        </div>
    </div>
</div>

<!-- Reject Order Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Reject Order #{{ $order->order_number }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('vendor.orders.reject', $order->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="small text-muted">The system will automatically attempt routing this order to the next nearest eligible store.</p>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Select Rejection Reason</label>
                        <select name="rejection_reason" class="form-select" required>
                            <option value="Item temporarily out of stock">Item temporarily out of stock</option>
                            <option value="Kitchen overloaded with high volume">Kitchen overloaded with high volume</option>
                            <option value="Store closing early">Store closing early</option>
                            <option value="Cannot prepare within SLA">Cannot prepare within SLA</option>
                            <option value="Other">Other reason</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill btn-sm" data-bs-dismiss="modal">Keep Order</button>
                    <button type="submit" class="btn btn-danger rounded-pill btn-sm">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
