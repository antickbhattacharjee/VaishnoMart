@extends('layouts.vendor')

@section('title', 'Earnings & Settlements - ' . $vendor->business_name)
@section('page_title', 'Earnings & Ledger')

@section('content')
<div class="row g-4 mb-4">
    <!-- Wallet Balance Card -->
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 rounded-4 shadow-sm text-white p-4" style="background: linear-gradient(135deg, #064e3b, #059669);">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-uppercase small fw-bold text-light opacity-75">Available Wallet Balance</span>
                <i class="bi bi-wallet-fill fs-3 text-warning"></i>
            </div>
            <div class="display-5 fw-bold mb-3">₹{{ number_format($walletBalance, 2) }}</div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-warning text-dark fw-bold rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#requestPayoutModal">
                    <i class="bi bi-arrow-down-circle me-1"></i> Request Payout
                </button>
            </div>
            <div class="small text-light opacity-75 mt-3">
                Minimum payout request is ₹500.00. Processed directly to registered store bank account.
            </div>
        </div>
    </div>

    <!-- Settlement History Card -->
    <div class="col-md-6 col-lg-7">
        <div class="card border-0 rounded-4 shadow-sm bg-white p-4 h-100">
            <h5 class="fw-bold mb-3">Recent Payout Settlements</h5>
            @if($settlements->isEmpty())
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-bank fs-2 mb-2"></i>
                    <p class="small mb-0">No settlement requests created yet.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Reference</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($settlements as $s)
                                <tr>
                                    <td class="fw-bold font-monospace small">{{ $s->settlement_reference }}</td>
                                    <td class="fw-bold text-success">₹{{ number_format($s->amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $s->status === 'paid' ? 'success' : 'warning' }} text-capitalize">
                                            {{ $s->status }}
                                        </span>
                                    </td>
                                    <td class="small text-muted">{{ $s->created_at->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Financial Ledger Transactions -->
<div class="card border-0 rounded-4 shadow-sm bg-white p-4">
    <h5 class="fw-bold mb-3"><i class="bi bi-journal-text me-1 text-primary"></i> Complete Financial Audit Ledger</h5>
    @if($transactions->isEmpty())
        <div class="p-4 text-center text-muted">
            <i class="bi bi-receipt-cutoff fs-2 mb-2"></i>
            <p class="small mb-0">No ledger transactions recorded yet. Completed orders will appear here automatically.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>Date & Reference</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th class="text-end">Balance After</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $t)
                        <tr>
                            <td class="py-3">
                                <div class="fw-semibold text-dark">{{ $t->created_at->format('M d, Y • h:i A') }}</div>
                                <div class="small font-monospace text-muted">{{ $t->reference_number }}</div>
                            </td>
                            <td>
                                <span class="badge bg-{{ str_contains($t->type, 'credit') ? 'success' : 'secondary' }} text-uppercase" style="font-size: 0.7rem;">
                                    {{ str_replace('_', ' ', $t->type) }}
                                </span>
                            </td>
                            <td>
                                <div class="small text-dark">{{ $t->description }}</div>
                                @if($t->order)
                                    <a href="{{ route('vendor.orders.show', $t->order_id) }}" class="small text-decoration-none">
                                        View Order #{{ $t->order->order_number }}
                                    </a>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold {{ str_contains($t->type, 'credit') ? 'text-success' : 'text-danger' }}">
                                    {{ str_contains($t->type, 'credit') ? '+' : '-' }}₹{{ number_format($t->amount, 2) }}
                                </span>
                            </td>
                            <td class="text-end fw-bold text-dark">
                                ₹{{ number_format($t->balance_after, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $transactions->links() }}
        </div>
    @endif
</div>

<!-- Request Payout Modal -->
<div class="modal fade" id="requestPayoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Request Store Settlement Payout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('vendor.settlements.request') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="small text-muted mb-3">Request transfer of your full wallet balance to your bank account.</p>
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Current Wallet Balance:</span>
                            <span class="fw-bold fs-5 text-success">₹{{ number_format($walletBalance, 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill btn-sm px-3">Submit Payout Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
