@extends('layouts.app')

@section('title', 'Customer Sign Up - VaishnoMart')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card border-0 rounded-4 shadow-lg p-4 p-md-5 bg-white">
                <div class="text-center mb-4">
                    <div class="vm-brand-icon mx-auto mb-2" style="width: 52px; height: 52px; font-size: 1.6rem;">
                        <i class="bi bi-person-plus-fill"></i>
                    </div>
                    <h3 class="fw-bold mb-1">Create Customer Account</h3>
                    <p class="text-muted small">Join VaishnoMart for instant neighbourhood deliveries</p>
                </div>

                <form action="{{ route('register') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Full Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Ramesh Kumar" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="name@example.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Mobile Phone (10-digits)</label>
                            <input type="tel" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="9876543210" pattern="[0-9]{10}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Default Delivery Address (Street / Flat / House)</label>
                        <input type="text" name="address_line1" class="form-control" value="{{ old('address_line1') }}" placeholder="e.g. Flat 204, Tower B, Connaught Residency">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">City</label>
                            <input type="text" name="city" class="form-control" value="New Delhi">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">PIN Code</label>
                            <input type="text" name="postal_code" class="form-control" value="110001">
                        </div>
                    </div>

                    <div class="row g-2 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Min. 8 characters" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Re-type password" required>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mb-4">
                        <button type="submit" class="btn btn-vm-primary btn-lg rounded-pill fw-bold">
                            Create My Account
                        </button>
                    </div>

                    <div class="text-center small text-muted">
                        Already have an account? <a href="{{ route('login') }}" class="text-success fw-bold text-decoration-none">Sign In</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
