@extends('layouts.app')

@section('title', 'Join as Delivery Partner - VaishnoMart')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card border-0 rounded-4 shadow-lg p-4 p-md-5 bg-white">
                <div class="text-center mb-4">
                    <div class="vm-brand-icon mx-auto mb-2" style="width: 52px; height: 52px; font-size: 1.6rem; background: linear-gradient(135deg, #f59e0b, #d97706);">
                        <i class="bi bi-bicycle"></i>
                    </div>
                    <h3 class="fw-bold mb-1">Join Delivery Fleet</h3>
                    <p class="text-muted small">Deliver drinking water, food, and daily essentials with transparent per-order payouts</p>
                </div>

                <form action="{{ route('agent.register') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Full Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Vikas Sharma" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="vikas@example.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Mobile Phone (10-digits)</label>
                            <input type="tel" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="9876543210" pattern="[0-9]{10}" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Vehicle Type</label>
                            <select name="vehicle_type" class="form-select" required>
                                <option value="bike">Motorcycle / Bike</option>
                                <option value="scooter">Scooter / Scooty</option>
                                <option value="cycle">Bicycle</option>
                                <option value="van">Delivery Van</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Vehicle Number</label>
                            <input type="text" name="vehicle_number" class="form-control text-uppercase" placeholder="DL-01-AB-1234" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Driving License No.</label>
                            <input type="text" name="license_number" class="form-control text-uppercase" placeholder="DL-1234567890" required>
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
                        <button type="submit" class="btn btn-warning text-dark btn-lg rounded-pill fw-bold">
                            Join VaishnoMart Delivery Fleet
                        </button>
                    </div>

                    <div class="text-center small text-muted">
                        Already registered as delivery partner? <a href="{{ route('login') }}" class="text-success fw-bold text-decoration-none">Sign In</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
