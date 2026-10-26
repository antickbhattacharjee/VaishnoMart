@extends('layouts.admin')

@section('title', 'Platform Settings & Rules')

@section('content')
<div class="mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Platform Settings & Operational Rules</h3>
        <p class="text-muted text-sm mb-0">Configure hyperlocal distance fee rules, vendor commissions, and order routing thresholds.</p>
    </div>
</div>

<form action="{{ route('admin.settings.update') }}" method="POST">
    @csrf

    <div class="row g-4">
        <!-- Delivery Fee Rules -->
        <div class="col-lg-6">
            <div class="admin-card p-4 h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-bicycle me-2 text-primary"></i>Delivery Fee & Distance Economics</h5>
                <p class="text-xs text-muted">Configurable formula applied during checkout based on Haversine distance from the assigned vendor.</p>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-xs">Base Delivery Fee (₹)</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="0.5" name="delivery_fee[base_fee]" class="form-control" value="{{ $deliveryRule->base_fee ?? 25.00 }}" required>
                    </div>
                    <small class="text-muted text-xs">Flat base fee applied to deliveries up to base distance.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-xs">Per-Kilometer Distance Rate (₹/km)</label>
                    <div class="input-group">
                        <span class="input-group-text">₹/km</span>
                        <input type="number" step="0.5" name="delivery_fee[per_km_fee]" class="form-control" value="{{ $deliveryRule->per_km_fee ?? 8.00 }}" required>
                    </div>
                    <small class="text-muted text-xs">Rate added per kilometer beyond the base distance.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-xs">Free Delivery Cart Threshold (₹)</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="10" name="delivery_fee[free_delivery_threshold]" class="form-control" value="{{ $deliveryRule->free_delivery_threshold ?? 500.00 }}" required>
                    </div>
                    <small class="text-muted text-xs">Orders with subtotal exceeding this threshold get free delivery.</small>
                </div>
            </div>
        </div>

        <!-- Hyperlocal Operational & SLA Configuration -->
        <div class="col-lg-6">
            <div class="admin-card p-4 h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-gear-wide-connected me-2 text-primary"></i>SLA & Platform Commission</h5>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-xs">Default Platform Commission (%)</label>
                    <div class="input-group">
                        <input type="number" step="0.1" name="settings[default_commission_percentage]" class="form-control" value="{{ \App\Models\Setting::get('default_commission_percentage', '5.00') }}" required>
                        <span class="input-group-text">%</span>
                    </div>
                    <small class="text-muted text-xs">Commission automatically deducted from vendor payout on successful delivery.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-xs">Vendor Order Acceptance SLA (Minutes)</label>
                    <div class="input-group">
                        <input type="number" name="settings[vendor_acceptance_timeout_minutes]" class="form-control" value="{{ \App\Models\Setting::get('vendor_acceptance_timeout_minutes', '5') }}" required>
                        <span class="input-group-text">Minutes</span>
                    </div>
                    <small class="text-muted text-xs">Time given to vendor before auto-reassignment to next nearest store.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-xs">Pickup / Delivery OTP Lifetime (Minutes)</label>
                    <div class="input-group">
                        <input type="number" name="settings[otp_expiry_minutes]" class="form-control" value="{{ \App\Models\Setting::get('otp_expiry_minutes', '10') }}" required>
                        <span class="input-group-text">Minutes</span>
                    </div>
                    <small class="text-muted text-xs">Cryptographic OTP expiry period after generation.</small>
                </div>
            </div>
        </div>

        <!-- Platform Identity & Contact -->
        <div class="col-12">
            <div class="admin-card p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>Platform Identity & Support</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-xs">Platform Name</label>
                        <input type="text" name="settings[app_name]" class="form-control" value="{{ \App\Models\Setting::get('app_name', 'VaishnoMart') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-xs">Customer Support Phone</label>
                        <input type="text" name="settings[support_phone]" class="form-control" value="{{ \App\Models\Setting::get('support_phone', '+91 98765 43210') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-xs">Support Email Address</label>
                        <input type="email" name="settings[support_email]" class="form-control" value="{{ \App\Models\Setting::get('support_email', 'support@vaishnomart.com') }}">
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Save Platform Settings
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
