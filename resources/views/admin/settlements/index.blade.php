@extends('layouts.admin')

@section('title', 'Vendor Settlements & Payouts')

@section('content')
<div class="mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Vendor Settlements & Payouts</h3>
        <p class="text-muted text-sm mb-0">Review store withdrawal requests, verify order earnings, and record bank transfer references.</p>
    </div>
</div>

<div class="admin-card p-4">
    @if($settlements->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-cash-stack fs-1 d-block mb-2 text-secondary"></i>
            <h5>No settlement requests found</h5>
            <p class="text-xs mb-0">Vendors can request payouts from their dashboard once orders are delivered.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Settlement #</th>
                        <th>Store / Vendor</th>
                        <th>Amount Requested</th>
                        <th>Bank / UPI Snapshot</th>
                        <th>Status</th>
                        <th>Requested At</th>
                        <th>UTR / Reference</th>
                        <th class="text-end">Process Payout</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($settlements as $settlement)
                        <tr>
                            <td>
                                <span class="fw-bold">#{{ $settlement->settlement_number }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $settlement->vendor->business_name ?? 'Vendor' }}</div>
                                <small class="text-muted text-xs">ID #{{ $settlement->vendor_id }}</small>
                            </td>
                            <td class="fw-bold text-success fs-6">
                                ₹{{ number_format($settlement->amount, 2) }}
                            </td>
                            <td>
                                @if(is_array($settlement->bank_details_snapshot))
                                    <div class="text-xs text-muted">
                                        {{ $settlement->bank_details_snapshot['bank_name'] ?? 'UPI' }} - 
                                        A/C: {{ $settlement->bank_details_snapshot['account_number'] ?? ($settlement->bank_details_snapshot['upi_id'] ?? 'N/A') }}
                                    </div>
                                @else
                                    <small class="text-muted text-xs">Primary Vendor Account</small>
                                @endif
                            </td>
                            <td>
                                @php
                                    $sBadge = match($settlement->status) {
                                        'paid' => 'success',
                                        'processing' => 'info text-dark',
                                        'pending' => 'warning text-dark',
                                        'rejected' => 'danger',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $sBadge }}">{{ strtoupper($settlement->status) }}</span>
                            </td>
                            <td class="text-xs text-muted">
                                {{ $settlement->created_at->format('d M, h:i A') }}
                            </td>
                            <td>
                                <span class="text-xs font-monospace">{{ $settlement->payment_reference ?? 'Pending' }}</span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#settlementModal{{ $settlement->id }}">
                                    Update
                                </button>
                            </td>
                        </tr>

                        <!-- Modal for Settlement Payout -->
                        <div class="modal fade" id="settlementModal{{ $settlement->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.settlements.update', $settlement->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">Process Settlement #{{ $settlement->settlement_number }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-xs">Payout Status</label>
                                                <select name="status" class="form-select">
                                                    <option value="paid" {{ $settlement->status === 'paid' ? 'selected' : '' }}>Paid (Transfer Completed)</option>
                                                    <option value="processing" {{ $settlement->status === 'processing' ? 'selected' : '' }}>Processing (Under Verification)</option>
                                                    <option value="rejected" {{ $settlement->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-xs">Bank UTR / Transaction Reference</label>
                                                <input type="text" name="payment_reference" class="form-control" value="{{ $settlement->payment_reference }}" placeholder="e.g. UTR123498765">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-xs">Administrative Notes</label>
                                                <textarea name="notes" class="form-control" rows="2" placeholder="Transfer notes or rejection reason...">{{ $settlement->notes }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary btn-sm">Save Payout Status</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $settlements->links() }}
        </div>
    @endif
</div>
@endsection
