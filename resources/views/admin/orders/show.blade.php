@extends('layouts.admin')

@section('title', 'Order ' . $order->order_number . ' - Administrative Inspection')

@section('content')
<div class="mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Orders
            </a>
            <h4 class="fw-bold mb-0">Order #{{ $order->order_number }}</h4>
        </div>
        <p class="text-muted text-sm mb-0">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        @php
            $statusBadge = match($order->status) {
                'pending_vendor' => 'warning text-dark',
                'accepted' => 'info text-dark',
                'preparing' => 'primary',
                'ready_for_pickup' => 'secondary',
                'delivery_requested' => 'warning text-dark',
                'delivery_assigned' => 'info text-dark',
                'picked_up', 'out_for_delivery' => 'primary',
                'delivered' => 'success',
                'cancelled', 'rejected' => 'danger',
                default => 'light text-dark',
            };
        @endphp
        <span class="badge bg-{{ $statusBadge }} fs-6 px-3 py-2">
            Status: {{ strtoupper(str_replace('_', ' ', $order->status)) }}
        </span>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Order Items, Parties & Addresses -->
    <div class="col-lg-8">
        <!-- Order Items -->
        <div class="admin-card p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-basket me-2 text-primary"></i>Items Ordered</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Item Details</th>
                            <th class="text-center">Unit Price</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $item->product_name }}</div>
                                    @if($item->variant_name)
                                        <small class="text-muted">Option: {{ $item->variant_name }}</small>
                                    @endif
                                    @if($item->deposit_amount > 0)
                                        <div class="badge bg-info-subtle text-info text-xs">
                                            Bottle/Can Deposit: ₹{{ number_format($item->deposit_amount, 2) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">₹{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-center fw-bold">x{{ $item->quantity }}</td>
                                <td class="text-end fw-bold">₹{{ number_format($item->total_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-top">
                        <tr>
                            <td colspan="3" class="text-end text-muted">Subtotal:</td>
                            <td class="text-end">₹{{ number_format($order->subtotal, 2) }}</td>
                        </tr>
                        @if($order->tax_amount > 0)
                            <tr>
                                <td colspan="3" class="text-end text-muted">Taxes & GST:</td>
                                <td class="text-end">₹{{ number_format($order->tax_amount, 2) }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td colspan="3" class="text-end text-muted">Delivery Charges:</td>
                            <td class="text-end">₹{{ number_format($order->delivery_fee, 2) }}</td>
                        </tr>
                        @if($order->discount_amount > 0)
                            <tr>
                                <td colspan="3" class="text-end text-success">Discount:</td>
                                <td class="text-end text-success">-₹{{ number_format($order->discount_amount, 2) }}</td>
                            </tr>
                        @endif
                        <tr class="fs-5 fw-bold">
                            <td colspan="3" class="text-end">Total Amount:</td>
                            <td class="text-end text-primary">₹{{ number_format($order->total_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Order Parties -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="admin-card p-3 h-100">
                    <h6 class="fw-bold text-muted text-uppercase text-xs mb-2">Customer</h6>
                    <div class="fw-semibold">{{ $order->customer->name ?? 'Unknown' }}</div>
                    <div class="text-xs text-muted">{{ $order->customer->email ?? '' }}</div>
                    <div class="text-xs text-muted">{{ $order->customer->phone ?? '' }}</div>
                    <div class="mt-2 text-xs">
                        <span class="badge bg-secondary-subtle text-secondary">
                            Delivery Dest: {{ $order->deliveryAddress->city ?? 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="admin-card p-3 h-100">
                    <h6 class="fw-bold text-muted text-uppercase text-xs mb-2">Fulfilling Vendor</h6>
                    @if($order->vendor)
                        <div class="fw-semibold">{{ $order->vendor->business_name }}</div>
                        <div class="text-xs text-muted">{{ $order->vendor->business_address }}</div>
                        <div class="text-xs text-muted">Phone: {{ $order->vendor->user->phone ?? 'N/A' }}</div>
                    @else
                        <div class="text-warning text-xs">No vendor assigned currently</div>
                    @endif
                </div>
            </div>

            <div class="col-md-4">
                <div class="admin-card p-3 h-100">
                    <h6 class="fw-bold text-muted text-uppercase text-xs mb-2">Assigned Rider</h6>
                    @if($order->deliveryAgent)
                        <div class="fw-semibold">{{ $order->deliveryAgent->name }}</div>
                        <div class="text-xs text-muted">Phone: {{ $order->deliveryAgent->phone }}</div>
                        <div class="text-xs text-muted">Vehicle: {{ $order->deliveryAgent->deliveryAgentProfile->vehicle_type ?? 'Bike' }}</div>
                    @else
                        <div class="text-muted text-xs fst-italic">No delivery rider assigned yet</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Full Status History Event Log (Audit Trail) -->
        <div class="admin-card p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-clock-history me-2 text-primary"></i>Lifecycle Status History</h5>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>From Status</th>
                            <th>To Status</th>
                            <th>Triggered By</th>
                            <th>Timestamp</th>
                            <th>Notes / Metadata</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($order->statusHistory as $hist)
                            <tr>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $hist->previous_status ?? 'ORIGIN' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">{{ $hist->new_status }}</span>
                                </td>
                                <td>
                                    {{ $hist->actor->name ?? 'System' }} ({{ $hist->actor_role ?? 'system' }})
                                </td>
                                <td class="text-xs text-muted">
                                    {{ $hist->created_at->format('d M Y, h:i:s A') }}
                                </td>
                                <td class="text-xs text-muted">
                                    {{ $hist->reason ?? json_encode($hist->metadata ?? []) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-2">No history records logged.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Vendor Assignment Routing Log -->
        <div class="admin-card p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-signpost-split me-2 text-primary"></i>Vendor Routing Log</h5>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Store Offered</th>
                            <th>Distance</th>
                            <th>Offered At</th>
                            <th>Response Status</th>
                            <th>Rejection Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($order->vendorAssignments as $va)
                            <tr>
                                <td class="fw-semibold">{{ $va->vendor->business_name ?? 'Vendor #' . $va->vendor_id }}</td>
                                <td>{{ number_format($va->distance_km ?? 0, 2) }} km</td>
                                <td class="text-xs">{{ $va->created_at->format('h:i:s A') }}</td>
                                <td>
                                    @php
                                        $vaBadge = match($va->status) {
                                            'accepted' => 'success',
                                            'rejected' => 'danger',
                                            'expired' => 'warning text-dark',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $vaBadge }}">{{ strtoupper($va->status) }}</span>
                                </td>
                                <td class="text-xs text-muted">{{ $va->rejection_reason ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-2">No assignments recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Financial Breakdown & OTP Verification Records -->
    <div class="col-lg-4">
        <!-- Financial Ledger Summary -->
        <div class="admin-card p-4 mb-4">
            <h6 class="fw-bold text-muted text-uppercase text-xs mb-3">Order Economics & Ledger</h6>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted text-sm">Customer Paid (GMV):</span>
                <span class="fw-bold">₹{{ number_format($order->total_amount, 2) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted text-sm">Platform Commission ({{ $order->platform_commission_rate }}%):</span>
                <span class="fw-bold text-success">+₹{{ number_format($order->platform_commission_amount, 2) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted text-sm">Vendor Net Earning:</span>
                <span class="fw-bold text-primary">₹{{ number_format($order->vendor_payout_amount, 2) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted text-sm">Delivery Rider Fee:</span>
                <span class="fw-bold text-secondary">₹{{ number_format($order->delivery_fee, 2) }}</span>
            </div>
            <hr>
            <div class="d-flex justify-content-between">
                <span class="text-muted text-xs">Payment Method:</span>
                <span class="badge bg-light text-dark border">{{ strtoupper($order->payment_method) }}</span>
            </div>
            <div class="d-flex justify-content-between mt-1">
                <span class="text-muted text-xs">Payment Status:</span>
                <span class="badge bg-success-subtle text-success">{{ strtoupper($order->payment_status) }}</span>
            </div>
        </div>

        <!-- OTP Security Verification Audit Records -->
        <div class="admin-card p-4 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold text-muted text-uppercase text-xs mb-0">OTP Verification Records</h6>
                <span class="badge bg-dark-subtle text-dark">Cryptographic</span>
            </div>

            @forelse($order->otpVerifications as $otpRecord)
                <div class="border rounded-3 p-3 mb-2 bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-sm text-capitalize">
                            <i class="bi bi-shield-check text-primary me-1"></i> {{ str_replace('_', ' ', $otpRecord->stage) }}
                        </span>
                        @if($otpRecord->is_verified)
                            <span class="badge bg-success">VERIFIED</span>
                        @else
                            <span class="badge bg-warning text-dark">PENDING</span>
                        @endif
                    </div>
                    <div class="text-xs text-muted mb-1">
                        Generated For: Role <strong>{{ $otpRecord->role_authorized ?? 'Agent/Vendor' }}</strong>
                    </div>
                    <div class="text-xs text-muted">
                        Failed Attempts: {{ $otpRecord->attempts }}/3
                    </div>
                    <div class="text-xs text-muted">
                        Expires: {{ $otpRecord->expires_at->format('h:i:s A') }}
                    </div>
                    @if($otpRecord->verified_at)
                        <div class="text-xs text-success mt-1">
                            <i class="bi bi-check-all"></i> Verified at {{ $otpRecord->verified_at->format('d M, h:i:s A') }}
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-muted text-xs text-center py-3">
                    No OTP verification cycles generated yet.
                </div>
            @endforelse
        </div>

        <!-- Customer Rating / Review if present -->
        @if($order->review)
            <div class="admin-card p-4">
                <h6 class="fw-bold text-muted text-uppercase text-xs mb-3">Customer Review & Rating</h6>
                <div class="d-flex align-items-center gap-1 text-warning mb-2">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="bi bi-star{{ $i <= $order->review->vendor_rating ? '-fill' : '' }}"></i>
                    @endfor
                    <span class="text-dark fw-bold ms-2">{{ $order->review->vendor_rating }}/5</span>
                </div>
                @if($order->review->comment)
                    <p class="text-xs text-muted fst-italic mb-0">
                        "{{ $order->review->comment }}"
                    </p>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
