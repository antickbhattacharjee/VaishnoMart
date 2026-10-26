@extends('layouts.app')

@section('title', 'Track Order #' . $order->order_number . ' - VaishnoMart')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customer.orders') }}" class="text-decoration-none">My Orders</a></li>
            <li class="breadcrumb-item active" aria-current="page">#{{ $order->order_number }}</li>
        </ol>
    </nav>

    <!-- Header & Status Badge -->
    <div class="card border-0 rounded-4 shadow-sm bg-white p-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <span class="badge bg-{{ $order->status_badge_class }} px-3 py-2 rounded-pill fw-bold text-uppercase fs-6 mb-2">
                    {{ $order->status_label }}
                </span>
                <h3 class="fw-bold mb-0">Order #{{ $order->order_number }}</h3>
                <div class="small text-muted">Placed on {{ $order->created_at->format('M d, Y • h:i A') }}</div>
            </div>
            <div class="d-flex gap-2">
                @if(in_array($order->status, [App\Models\Order::STATUS_PENDING_VENDOR, App\Models\Order::STATUS_ACCEPTED]))
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#cancelOrderModal">
                        <i class="bi bi-x-circle me-1"></i> Cancel Order
                    </button>
                @endif

                @if($order->status === App\Models\Order::STATUS_DELIVERED && !$order->review)
                    <button type="button" class="btn btn-warning text-dark btn-sm rounded-pill fw-bold px-3" data-bs-toggle="modal" data-bs-target="#reviewModal">
                        <i class="bi bi-star-fill me-1"></i> Rate & Review
                    </button>
                @endif
            </div>
        </div>

        <!-- Live Order Status Stepper -->
        @php
            $currentStatus = $order->status;
            $statuses = [
                'pending_vendor' => ['label' => 'Order Placed', 'icon' => 'bi-receipt'],
                'accepted' => ['label' => 'Store Confirmed', 'icon' => 'bi-check2-circle'],
                'preparing' => ['label' => 'Preparing', 'icon' => 'bi-fire'],
                'ready_for_pickup' => ['label' => 'Packed', 'icon' => 'bi-box-seam'],
                'delivery_assigned' => ['label' => 'Agent Assigned', 'icon' => 'bi-bicycle'],
                'out_for_delivery' => ['label' => 'Out for Delivery', 'icon' => 'bi-truck'],
                'delivered' => ['label' => 'Delivered', 'icon' => 'bi-house-check-fill'],
            ];

            $orderStatusWeights = [
                'pending_payment' => 0,
                'pending_vendor' => 1,
                'accepted' => 2,
                'preparing' => 3,
                'ready_for_pickup' => 4,
                'delivery_requested' => 4,
                'delivery_assigned' => 5,
                'picked_up' => 6,
                'out_for_delivery' => 6,
                'delivered' => 7,
                'cancelled' => -1,
                'rejected' => -1,
            ];

            $currentWeight = $orderStatusWeights[$currentStatus] ?? 0;
        @endphp

        @if($currentWeight >= 0)
            <div class="mt-4 pt-3 border-top">
                <div class="d-none d-md-flex vm-stepper">
                    @foreach($statuses as $key => $meta)
                        @php
                            $stepWeight = $orderStatusWeights[$key];
                            $isCompleted = $currentWeight > $stepWeight;
                            $isActive = ($currentWeight === $stepWeight);
                        @endphp
                        <div class="vm-step-node {{ $isCompleted ? 'completed' : '' }} {{ $isActive ? 'active' : '' }}">
                            <div class="vm-step-circle">
                                @if($isCompleted)
                                    <i class="bi bi-check-lg"></i>
                                @else
                                    <i class="bi {{ $meta['icon'] }}"></i>
                                @endif
                            </div>
                            <div class="vm-step-label">{{ $meta['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="alert alert-danger border-0 rounded-3 mt-3 mb-0">
                <i class="bi bi-x-octagon-fill me-2"></i>
                This order was cancelled: <strong>{{ $order->cancellation_reason ?? 'Cancelled by store or user.' }}</strong>
            </div>
        @endif
    </div>

    <!-- CRITICAL: Delivery Confirmation OTP Banner (when out for delivery) -->
    @if($order->status === App\Models\Order::STATUS_OUT_FOR_DELIVERY)
        @php
            $deliveryOtpRecord = \App\Models\OtpVerification::where('order_id', $order->id)
                ->where('stage', \App\Models\OtpVerification::STAGE_DELIVERY)
                ->where('is_verified', false)
                ->latest('id')
                ->first();
            $notification = \App\Models\SystemNotification::where('user_id', auth()->id())
                ->where('title', 'like', '%Out for Delivery%')
                ->latest('id')
                ->first();
        @endphp
        <div class="alert alert-warning border-0 rounded-4 shadow-sm p-4 mb-4 text-dark" style="background: linear-gradient(135deg, #fef3c7, #fde68a);">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning text-dark p-3 fs-2 d-flex align-items-center justify-content-center shadow-sm">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Your Order is Out for Delivery! 🚀</h5>
                        <p class="mb-1">
                            {{ $notification->message ?? 'Your order is on the way! Please verify the parcel before sharing your confirmation OTP.' }}
                        </p>
                        <div class="small fw-semibold text-danger">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            Share this OTP with delivery partner <strong>ONLY</strong> when you physically receive and verify your products.
                        </div>
                    </div>
                </div>
                <div class="p-3 bg-white rounded-3 shadow-sm text-center border" style="min-width: 170px;">
                    <div class="small text-muted fw-bold text-uppercase">Delivery OTP</div>
                    <div class="fs-1 fw-bold text-dark font-monospace my-1">
                        <span class="bg-warning-subtle px-3 py-1 rounded-3 border border-warning">{{ $deliveryOtpRecord->plain_otp ?? '123456' }}</span>
                    </div>
                    <div class="text-muted fw-semibold" style="font-size: 0.75rem;">Share with Rider upon Arrival</div>
                    <div class="mt-2 pt-1 border-top">
                        <form action="{{ route('customer.orders.resend-otp', $order->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1" style="font-size: 0.75rem;">
                                <i class="bi bi-arrow-clockwise me-1"></i> Resend OTP
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- Order Items & Delivery Address -->
        <div class="col-lg-8">
            <!-- Items Table Card -->
            <div class="card border-0 rounded-4 shadow-sm bg-white p-4 mb-4">
                <h5 class="fw-bold mb-3">Order Items ({{ $order->items->count() }})</h5>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>Item</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="py-3">
                                        <div class="fw-bold text-dark">{{ $item->product_name }}</div>
                                        @if($item->variant_name)
                                            <div class="small text-muted">{{ $item->variant_name }}</div>
                                        @endif
                                        @if($item->deposit_amount > 0)
                                            <span class="badge bg-info text-dark small">+ ₹{{ number_format($item->deposit_amount, 0) }} Can Deposit</span>
                                        @endif
                                    </td>
                                    <td>₹{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="fw-bold">{{ $item->quantity }}</td>
                                    <td class="text-end fw-bold text-success">₹{{ number_format($item->total_amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Store & Delivery Addresses -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card border-0 rounded-4 shadow-sm bg-white p-4 h-100">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-shop text-success fs-5"></i>
                            <h6 class="fw-bold mb-0">Store Details</h6>
                        </div>
                        <div class="fw-bold text-dark">{{ $order->vendor->business_name }}</div>
                        <div class="small text-muted mb-2">{{ $order->vendor->address }}</div>
                        <div class="small text-muted"><i class="bi bi-telephone me-1"></i>{{ $order->vendor->phone }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 rounded-4 shadow-sm bg-white p-4 h-100">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-geo-alt-fill text-danger fs-5"></i>
                            <h6 class="fw-bold mb-0">Delivery Address</h6>
                        </div>
                        <div class="fw-bold text-dark">{{ $order->deliveryAddress->recipient_name ?? auth()->user()->name }}</div>
                        <div class="small text-muted mb-2">{{ $order->deliveryAddress->formatted_address ?? 'Customer Address' }}</div>
                        <div class="small text-muted"><i class="bi bi-telephone me-1"></i>{{ $order->deliveryAddress->phone ?? auth()->user()->phone }}</div>
                    </div>
                </div>
            </div>

            <!-- Delivery Partner Information (if assigned) -->
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
                                    {{ $order->deliveryAgent->deliveryAgentProfile->vehicle_type ?? 'Bike' }} ({{ $order->deliveryAgent->deliveryAgentProfile->vehicle_number ?? 'DL-Verified' }})
                                </div>
                            </div>
                        </div>
                        <div>
                            <a href="tel:{{ $order->deliveryAgent->phone }}" class="btn btn-outline-success btn-sm rounded-pill px-3">
                                <i class="bi bi-telephone-fill me-1"></i> Call Partner
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Order Event Timeline -->
            <div class="card border-0 rounded-4 shadow-sm bg-white p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-1 text-primary"></i> Order Event History</h6>
                <div class="list-group list-group-flush small">
                    @foreach($order->statusHistory as $history)
                        <div class="list-group-item px-0 py-2 border-bottom-0 d-flex justify-content-between">
                            <div>
                                <i class="bi bi-dot text-success fs-5 align-middle"></i>
                                <span class="fw-semibold text-capitalize">{{ str_replace('_', ' ', $history->new_status) }}</span>:
                                <span class="text-muted">{{ $history->comment }}</span>
                            </div>
                            <span class="text-muted">{{ $history->created_at->format('h:i A') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Bill Summary & Payment Info -->
        <div class="col-lg-4">
            <div class="card border-0 rounded-4 shadow-sm bg-white p-4 sticky-top" style="top: 90px;">
                <h5 class="fw-bold mb-3">Bill Details</h5>

                <div class="d-flex justify-content-between small mb-2">
                    <span class="text-muted">Items Subtotal:</span>
                    <span class="fw-semibold">₹{{ number_format($order->subtotal, 2) }}</span>
                </div>

                @if($order->deposit_total > 0)
                    <div class="d-flex justify-content-between small mb-2 text-primary">
                        <span><i class="bi bi-arrow-repeat me-1"></i>Can Deposit:</span>
                        <span class="fw-semibold">₹{{ number_format($order->deposit_total, 2) }}</span>
                    </div>
                @endif

                <div class="d-flex justify-content-between small mb-2">
                    <span class="text-muted">Delivery Fee:</span>
                    <span class="fw-semibold">{{ $order->delivery_fee == 0 ? 'FREE' : '₹' . number_format($order->delivery_fee, 2) }}</span>
                </div>

                <div class="d-flex justify-content-between small mb-2">
                    <span class="text-muted">Taxes (5% GST):</span>
                    <span class="fw-semibold">₹{{ number_format($order->tax_amount, 2) }}</span>
                </div>

                @if($order->discount_amount > 0)
                    <div class="d-flex justify-content-between small mb-2 text-success">
                        <span>Coupon Discount:</span>
                        <span class="fw-bold">-₹{{ number_format($order->discount_amount, 2) }}</span>
                    </div>
                @endif

                <hr class="my-3">

                <div class="d-flex justify-content-between align-items-baseline mb-4">
                    <span class="fw-bold fs-5">Total Paid:</span>
                    <span class="display-6 fw-bold text-success">₹{{ number_format($order->total_amount, 2) }}</span>
                </div>

                <div class="p-3 bg-light rounded-3 border small">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Payment Method:</span>
                        <span class="fw-bold text-uppercase">{{ $order->payment_method }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Payment Status:</span>
                        <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }} text-capitalize">
                            {{ $order->payment_status }}
                        </span>
                    </div>
                </div>

                @if($order->customer_notes)
                    <div class="mt-3 p-2 bg-light rounded-2 small text-muted">
                        <strong>Delivery Note:</strong> {{ $order->customer_notes }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Cancel Order Modal -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Cancel Order #{{ $order->order_number }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('customer.orders.cancel', $order->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="small text-muted">Are you sure you want to cancel this order? Stock will be restored to the store.</p>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Reason for Cancellation</label>
                        <select name="reason" class="form-select" required>
                            <option value="Changed my mind">Changed my mind</option>
                            <option value="Ordered by mistake">Ordered by mistake</option>
                            <option value="Delivery time too long">Delivery time too long</option>
                            <option value="Found alternative local store">Found alternative local store</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill btn-sm" data-bs-dismiss="modal">Keep Order</button>
                    <button type="submit" class="btn btn-danger rounded-pill btn-sm">Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Rate & Review Modal -->
@if($order->status === App\Models\Order::STATUS_DELIVERED && !$order->review)
<div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Rate Store & Delivery</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('customer.orders.review', $order->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Store Rating ({{ $order->vendor->business_name }})</label>
                        <select name="vendor_rating" class="form-select">
                            <option value="5">⭐⭐⭐⭐⭐ Excellent (5 Stars)</option>
                            <option value="4">⭐⭐⭐⭐ Good (4 Stars)</option>
                            <option value="3">⭐⭐⭐ Average (3 Stars)</option>
                            <option value="2">⭐⭐ Poor (2 Stars)</option>
                            <option value="1">⭐ Terrible (1 Star)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Store Review Feedback</label>
                        <textarea name="vendor_review" rows="2" class="form-control" placeholder="How was the product freshness and packaging?"></textarea>
                    </div>

                    @if($order->deliveryAgent)
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Delivery Partner Rating ({{ $order->deliveryAgent->name }})</label>
                            <select name="delivery_rating" class="form-select">
                                <option value="5">⭐⭐⭐⭐⭐ Quick & Courteous (5 Stars)</option>
                                <option value="4">⭐⭐⭐⭐ Good (4 Stars)</option>
                                <option value="3">⭐⭐⭐ Average (3 Stars)</option>
                                <option value="2">⭐⭐ Delayed (2 Stars)</option>
                                <option value="1">⭐ Poor Delivery (1 Star)</option>
                            </select>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill btn-sm" data-bs-dismiss="modal">Later</button>
                    <button type="submit" class="btn btn-success rounded-pill btn-sm">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection
