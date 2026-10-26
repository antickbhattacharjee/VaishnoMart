@extends('layouts.app')

@section('title', 'Sign In - VaishnoMart')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 rounded-4 shadow-lg p-4 p-md-5 bg-white">
                <div class="text-center mb-4">
                    <div class="vm-brand-icon mx-auto mb-2" style="width: 52px; height: 52px; font-size: 1.6rem;">
                        <i class="bi bi-shop"></i>
                    </div>
                    <h3 class="fw-bold mb-1">Welcome Back</h3>
                    <p class="text-muted small">Sign in to your VaishnoMart account</p>
                </div>

                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Email Address or Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                            <input type="text" name="login" id="loginField" class="form-control border-start-0" value="{{ old('login') }}" placeholder="name@example.com or 10-digit phone" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label small fw-bold mb-0">Password</label>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                            <input type="password" name="password" id="passwordField" class="form-control border-start-0" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" name="remember" class="form-check-input" id="rememberMe">
                        <label class="form-check-label small text-muted" for="rememberMe">Remember me on this browser</label>
                    </div>

                    <div class="d-grid gap-2 mb-4">
                        <button type="submit" class="btn btn-vm-primary btn-lg rounded-pill fw-bold" id="btnSubmitLogin">
                            Sign In to Account
                        </button>
                    </div>

                    <div class="text-center small text-muted mb-4">
                        Don't have an account? <a href="{{ route('register') }}" class="text-success fw-bold text-decoration-none">Sign Up</a>
                    </div>
                </form>

                <!-- Quick-Fill Demo Accounts Section for Evaluation -->
                <div class="border-top pt-3">
                    <div class="text-uppercase small fw-bold text-muted text-center mb-2">⚡ Quick-Fill Demo Accounts</div>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-sm btn-outline-danger text-start px-3 py-2 demo-fill-btn" data-login="admin@vaishnomart.com" data-pass="password123">
                            <i class="bi bi-shield-check me-2"></i> <strong>Super Admin:</strong> admin@vaishnomart.com
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success text-start px-3 py-2 demo-fill-btn" data-login="water@vaishnomart.com" data-pass="password123">
                            <i class="bi bi-shop me-2"></i> <strong>Vendor (Aqua Pure):</strong> water@vaishnomart.com
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning text-dark text-start px-3 py-2 demo-fill-btn" data-login="kitchen@vaishnomart.com" data-pass="password123">
                            <i class="bi bi-fire me-2"></i> <strong>Vendor (Cloud Kitchen):</strong> kitchen@vaishnomart.com
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary text-start px-3 py-2 demo-fill-btn" data-login="rohan@vaishnomart.com" data-pass="password123">
                            <i class="bi bi-bicycle me-2"></i> <strong>Delivery Agent:</strong> rohan@vaishnomart.com
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary text-start px-3 py-2 demo-fill-btn" data-login="customer@vaishnomart.com" data-pass="password123">
                            <i class="bi bi-person me-2"></i> <strong>Customer:</strong> customer@vaishnomart.com
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.demo-fill-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('loginField').value = this.dataset.login;
            document.getElementById('passwordField').value = this.dataset.pass;
        });
    });
</script>
@endpush
