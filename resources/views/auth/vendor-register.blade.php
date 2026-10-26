@extends('layouts.app')

@section('title', 'Partner Store Registration - VaishnoMart')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card border-0 rounded-4 shadow-lg p-4 p-md-5 bg-white">
                <div class="text-center mb-4">
                    <div class="vm-brand-icon mx-auto mb-2" style="width: 52px; height: 52px; font-size: 1.6rem; background: linear-gradient(135deg, #059669, #047857);">
                        <i class="bi bi-shop-window"></i>
                    </div>
                    <h3 class="fw-bold mb-1">Register Your Store</h3>
                    <p class="text-muted small">Sell water cans, cloud kitchen meals, dry fruits, and groceries directly to customers</p>
                </div>

                <form action="{{ route('vendor.register') }}" method="POST">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Store / Business Name</label>
                            <input type="text" name="business_name" class="form-control" value="{{ old('business_name') }}" placeholder="e.g. Vaishno Fresh Food Hub" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Owner / Manager Name</label>
                            <input type="text" name="owner_name" class="form-control" value="{{ old('owner_name') }}" placeholder="e.g. Suman Gupta" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Business Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="store@example.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Store Contact Mobile</label>
                            <input type="tel" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="9876543210" pattern="[0-9]{10}" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Primary Business Category</label>
                            <select name="primary_category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('primary_category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Serviceable Delivery Radius (km)</label>
                            <input type="number" name="service_radius_km" class="form-control" value="{{ old('service_radius_km', 10) }}" min="1" max="50" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Complete Store Physical Address</label>
                        <textarea name="address" rows="2" class="form-control" placeholder="Shop/Building number, Market, Area, Landmark" required>{{ old('address') }}</textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Store Latitude (Optional)</label>
                            <input type="number" step="any" name="latitude" class="form-control" value="{{ old('latitude', 28.6310) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Store Longitude (Optional)</label>
                            <input type="number" step="any" name="longitude" class="form-control" value="{{ old('longitude', 77.2180) }}">
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Account Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Min. 8 characters" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Re-type password" required>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mb-4">
                        <button type="submit" class="btn btn-vm-primary btn-lg rounded-pill fw-bold">
                            Complete Vendor Registration
                        </button>
                    </div>

                    <div class="text-center small text-muted">
                        Already have a vendor account? <a href="{{ route('login') }}" class="text-success fw-bold text-decoration-none">Sign In Here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
