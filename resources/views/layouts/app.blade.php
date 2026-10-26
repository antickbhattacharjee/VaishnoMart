<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'VaishnoMart') - Your Needs. Your Neighbourhood. Delivered.</title>
    <meta name="description" content="@yield('meta_description', 'VaishnoMart connects you directly to neighbourhood stores for drinking water, cloud kitchens, dry fruits, grocery, and daily essentials.')">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --vm-primary: #059669;
            --vm-primary-dark: #064e3b;
            --vm-primary-light: #ecfdf5;
            --vm-accent: #f59e0b;
            --vm-accent-dark: #d97706;
            --vm-dark: #0f172a;
            --vm-gray-100: #f8fafc;
            --vm-gray-200: #e2e8f0;
            --vm-gray-600: #475569;
            --vm-border: #e2e8f0;
            --vm-radius: 12px;
            --vm-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.08);
            --vm-shadow-lg: 0 10px 25px -3px rgba(15, 23, 42, 0.12);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--vm-gray-100);
            color: var(--vm-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Flawless Pagination Styling */
        nav[role="navigation"] svg {
            width: 1rem !important;
            height: 1rem !important;
            max-width: 1rem !important;
            max-height: 1rem !important;
        }
        .pagination {
            display: inline-flex !important;
            align-items: center !important;
            gap: 4px !important;
        }
        .pagination .page-item .page-link {
            min-width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            border-radius: 8px !important;
            color: #334155;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            transition: all 0.15s ease-in-out;
        }
        .pagination .page-item .page-link:hover:not(.disabled) {
            background-color: #f1f5f9;
            color: #059669;
            border-color: #cbd5e1;
        }
        .pagination .page-item.active .page-link {
            background-color: #059669 !important;
            border-color: #059669 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 10px rgba(5, 150, 105, 0.25) !important;
        }
        .pagination .page-item.disabled .page-link {
            background-color: #f8fafc;
            color: #94a3b8;
            border-color: #f1f5f9;
        }

        /* Navbar & Header */
        .vm-navbar {
            background: #ffffff;
            border-bottom: 1px solid var(--vm-border);
            padding: 0.75rem 0;
            position: sticky;
            top: 0;
            z-index: 1030;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        }

        .vm-brand-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .vm-brand-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--vm-primary), var(--vm-primary-dark));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.4rem;
            box-shadow: 0 4px 10px rgba(5, 150, 105, 0.3);
        }

        .vm-brand-text {
            line-height: 1.1;
        }

        .vm-brand-name {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--vm-primary-dark);
            letter-spacing: -0.5px;
        }

        .vm-brand-tagline {
            font-size: 0.72rem;
            color: var(--vm-gray-600);
            font-weight: 500;
        }

        .vm-location-btn {
            background-color: var(--vm-primary-light);
            border: 1px solid rgba(5, 150, 105, 0.2);
            color: var(--vm-primary-dark);
            border-radius: 50px;
            padding: 6px 16px;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .vm-location-btn:hover {
            background-color: #d1fae5;
            border-color: var(--vm-primary);
        }

        .vm-search-bar {
            position: relative;
            width: 100%;
        }

        .vm-search-input {
            width: 100%;
            background: #f1f5f9;
            border: 1px solid transparent;
            border-radius: 50px;
            padding: 10px 20px 10px 45px;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .vm-search-input:focus {
            background: #ffffff;
            border-color: var(--vm-primary);
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15);
            outline: none;
        }

        .vm-search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.1rem;
        }

        /* Buttons & Badges */
        .btn-vm-primary {
            background: linear-gradient(135deg, var(--vm-primary), var(--vm-primary-dark));
            color: #ffffff;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            padding: 8px 18px;
            transition: all 0.2s;
        }

        .btn-vm-primary:hover {
            background: linear-gradient(135deg, #047857, #064e3b);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.35);
        }

        .btn-vm-outline {
            background: transparent;
            color: var(--vm-primary);
            border: 1.5px solid var(--vm-primary);
            font-weight: 600;
            border-radius: 8px;
            padding: 7px 16px;
            transition: all 0.2s;
        }

        .btn-vm-outline:hover {
            background: var(--vm-primary-light);
            color: var(--vm-primary-dark);
        }

        .vm-badge-distance {
            background: #f1f5f9;
            color: #334155;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* Product Cards */
        .vm-product-card {
            background: #ffffff;
            border: 1px solid var(--vm-border);
            border-radius: 14px;
            padding: 14px;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
        }

        .vm-product-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--vm-shadow);
            border-color: #cbd5e1;
        }

        .vm-product-img-box {
            height: 150px;
            background: #f8fafc;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-bottom: 12px;
            position: relative;
        }

        .vm-product-icon {
            font-size: 3.5rem;
            color: var(--vm-primary);
            opacity: 0.85;
        }

        .vm-product-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--vm-dark);
            margin-bottom: 4px;
            line-height: 1.3;
        }

        .vm-store-tag {
            font-size: 0.75rem;
            color: var(--vm-gray-600);
            display: flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 8px;
        }

        .vm-price-tag {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--vm-primary-dark);
        }

        .vm-price-old {
            font-size: 0.85rem;
            color: #94a3b8;
            text-decoration: line-through;
            margin-left: 6px;
            font-weight: 500;
        }

        /* Category Pill */
        .vm-cat-card {
            background: #ffffff;
            border: 1px solid var(--vm-border);
            border-radius: 14px;
            padding: 14px 10px;
            text-align: center;
            text-decoration: none;
            color: var(--vm-dark);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            height: 100%;
        }

        .vm-cat-card:hover {
            border-color: var(--vm-primary);
            background: var(--vm-primary-light);
            transform: translateY(-2px);
            color: var(--vm-primary-dark);
        }

        .vm-cat-icon-circle {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: var(--vm-primary);
            transition: all 0.2s;
        }

        .vm-cat-card:hover .vm-cat-icon-circle {
            background: #ffffff;
            transform: scale(1.1);
        }

        /* Order Stepper */
        .vm-stepper {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin: 20px 0;
        }

        .vm-stepper::before {
            content: '';
            position: absolute;
            top: 18px;
            left: 20px;
            right: 20px;
            height: 3px;
            background: #e2e8f0;
            z-index: 1;
        }

        .vm-step-node {
            position: relative;
            z-index: 2;
            background: #ffffff;
            text-align: center;
            width: 80px;
        }

        .vm-step-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 6px;
            font-size: 0.95rem;
            font-weight: 700;
            border: 2px solid #ffffff;
        }

        .vm-step-node.completed .vm-step-circle {
            background: var(--vm-primary);
            color: #ffffff;
        }

        .vm-step-node.active .vm-step-circle {
            background: var(--vm-accent);
            color: #ffffff;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.25);
        }

        .vm-step-label {
            font-size: 0.7rem;
            font-weight: 600;
            color: #64748b;
            line-height: 1.2;
        }

        .vm-step-node.active .vm-step-label,
        .vm-step-node.completed .vm-step-label {
            color: var(--vm-dark);
        }

        /* Footer */
        .vm-footer {
            background: #0f172a;
            color: #94a3b8;
            margin-top: auto;
            padding: 40px 0 20px;
            font-size: 0.9rem;
        }

        .vm-footer a {
            color: #cbd5e1;
            text-decoration: none;
            transition: color 0.2s;
        }

        .vm-footer a:hover {
            color: #ffffff;
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Top Navigation Bar -->
    <header class="vm-navbar">
        <div class="container">
            <div class="row align-items-center g-2">
                <!-- Brand -->
                <div class="col-auto">
                    <a href="{{ route('home') }}" class="vm-brand-logo">
                        <div class="vm-brand-icon">
                            <i class="bi bi-shop"></i>
                        </div>
                        <div class="vm-brand-text">
                            <div class="vm-brand-name">VaishnoMart</div>
                            <div class="vm-brand-tagline">Neighbourhood Hyperlocal</div>
                        </div>
                    </a>
                </div>

                <!-- Location Selector Button -->
                <div class="col-auto d-none d-md-block">
                    <button type="button" class="vm-location-btn" data-bs-toggle="modal" data-bs-target="#locationModal" id="btnLocationSelector">
                        <i class="bi bi-geo-alt-fill text-success"></i>
                        <span id="navLocationLabel">{{ session('customer_location_name', 'Connaught Place, New Delhi') }}</span>
                        <i class="bi bi-chevron-down small"></i>
                    </button>
                </div>

                <!-- Search Input Form -->
                <div class="col">
                    <form action="{{ route('search') }}" method="GET" class="vm-search-bar">
                        <i class="bi bi-search vm-search-icon"></i>
                        <input type="text" name="q" value="{{ request('q') }}" class="vm-search-input" placeholder="Search drinking water, thalis, dry fruits, grocery..." id="inputGlobalSearch">
                    </form>
                </div>

                <!-- User Controls & Cart -->
                <div class="col-auto d-flex align-items-center gap-2">
                    <!-- Cart Button -->
                    <a href="{{ route('cart.index') }}" class="btn btn-light position-relative rounded-pill px-3 py-2 fw-semibold border" id="btnNavbarCart">
                        <i class="bi bi-cart3 text-success fs-5 align-middle"></i>
                        <span class="d-none d-sm-inline ms-1 align-middle">Cart</span>
                        @php
                            $cartCount = 0;
                            if (auth()->check()) {
                                $c = \App\Models\Cart::where('user_id', auth()->id())->first();
                                $cartCount = $c ? $c->itemsCount : 0;
                            } else {
                                $c = \App\Models\Cart::where('session_id', session()->getId())->first();
                                $cartCount = $c ? $c->itemsCount : 0;
                            }
                        @endphp
                        @if($cartCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="navCartBadge">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </a>

                    <!-- Account Menu -->
                    @auth
                        <div class="dropdown">
                            <button class="btn btn-light rounded-pill px-3 py-2 fw-semibold border dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" id="dropdownUserMenu">
                                <i class="bi bi-person-circle fs-5 text-primary"></i>
                                <span class="d-none d-md-inline">{{ Str::limit(auth()->user()->name, 12) }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 rounded-3">
                                <li class="px-3 py-2 border-bottom">
                                    <div class="fw-bold text-dark">{{ auth()->user()->name }}</div>
                                    <div class="small text-muted text-capitalize">{{ str_replace('_', ' ', auth()->user()->role) }}</div>
                                </li>

                                @if(auth()->user()->isAdmin())
                                    <li><a class="dropdown-item py-2 fw-medium text-primary" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Admin Dashboard</a></li>
                                @endif

                                @if(auth()->user()->isVendor())
                                    <li><a class="dropdown-item py-2 fw-medium text-success" href="{{ route('vendor.dashboard') }}"><i class="bi bi-shop-window me-2"></i>Vendor Store Dashboard</a></li>
                                @endif

                                @if(auth()->user()->isDeliveryAgent())
                                    <li><a class="dropdown-item py-2 fw-medium text-warning" href="{{ route('agent.dashboard') }}"><i class="bi bi-bicycle me-2"></i>Delivery Agent App</a></li>
                                @endif

                                <li><a class="dropdown-item py-2" href="{{ route('customer.dashboard') }}"><i class="bi bi-grid me-2"></i>My Orders</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('customer.addresses') }}"><i class="bi bi-geo me-2"></i>Saved Addresses</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item py-2 text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-vm-outline px-3" id="btnNavLogin">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Sign In
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Global Alerts Container -->
    <div class="container mt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 d-flex align-items-center" role="alert">
                <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show rounded-3 shadow-sm border-0 d-flex align-items-center" role="alert">
                <i class="bi bi-info-circle-fill fs-5 me-2"></i>
                <div>{{ session('info') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show rounded-3 shadow-sm border-0 d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                <div>{{ session('warning') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0" role="alert">
                <div class="fw-bold mb-1"><i class="bi bi-x-circle-fill me-1"></i>Please correct the following:</div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <main class="flex-grow-1">
        @yield('content')
    </main>

    <!-- Location Selector Modal -->
    <div class="modal fade" id="locationModal" tabindex="-1" aria-labelledby="locationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="locationModalLabel">Select Your Delivery Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">VaishnoMart shows products and stores nearest to your selected address.</p>
                    <div class="d-grid gap-2 mb-3">
                        <button class="btn btn-outline-success rounded-3 text-start py-2 px-3 fw-semibold" id="btnDetectGeolocation">
                            <i class="bi bi-crosshair text-success me-2 fs-5 align-middle"></i> Use Current GPS Location
                        </button>
                    </div>

                    <div class="text-uppercase small fw-bold text-muted text-center mb-3">Or choose a neighbourhood</div>

                    <div class="list-group list-group-flush rounded-3 border">
                        <button type="button" class="list-group-item list-group-item-action py-3 preset-loc" data-lat="28.6315" data-lng="77.2200" data-name="Connaught Place, Central Delhi">
                            <div class="fw-bold"><i class="bi bi-geo-alt me-2 text-danger"></i>Connaught Place (Central Hub)</div>
                            <div class="small text-muted ps-4">Barakhamba Road, Janpath, CP Circle</div>
                        </button>
                        <button type="button" class="list-group-item list-group-item-action py-3 preset-loc" data-lat="28.5672" data-lng="77.2100" data-name="AIIMS & South Extension, Delhi">
                            <div class="fw-bold"><i class="bi bi-geo-alt me-2 text-danger"></i>South Extension & AIIMS</div>
                            <div class="small text-muted ps-4">Ring Road, South Delhi (Within 8km)</div>
                        </button>
                        <button type="button" class="list-group-item list-group-item-action py-3 preset-loc" data-lat="28.6506" data-lng="77.2303" data-name="Old Delhi & Chandni Chowk">
                            <div class="fw-bold"><i class="bi bi-geo-alt me-2 text-danger"></i>Old Delhi & Chandni Chowk</div>
                            <div class="small text-muted ps-4">Daryaganj, Chawri Bazar, Red Fort Area</div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="vm-footer">
        <div class="container">
            <div class="row g-4 mb-4">
                <div class="col-lg-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="vm-brand-icon" style="width: 32px; height: 32px; font-size: 1rem;">
                            <i class="bi bi-shop"></i>
                        </div>
                        <span class="fs-4 fw-bold text-white">VaishnoMart</span>
                    </div>
                    <p class="small text-secondary mb-3">
                        VaishnoMart is your hyperlocal multi-vendor marketplace connecting you directly with trusted neighbourhood vendors for drinking water, cloud kitchen food, dry fruits, grocery, and daily essentials.
                    </p>
                    <div class="d-flex gap-3 fs-5 text-secondary">
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-instagram"></i></a>
                        <a href="#"><i class="bi bi-whatsapp"></i></a>
                        <a href="#"><i class="bi bi-twitter-x"></i></a>
                    </div>
                </div>

                <div class="col-6 col-lg-2">
                    <h6 class="text-white fw-bold mb-3">Categories</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 small">
                        <li><a href="{{ route('category.show', 'drinking-water') }}">Drinking Water</a></li>
                        <li><a href="{{ route('category.show', 'cloud-kitchens') }}">Cloud Kitchens</a></li>
                        <li><a href="{{ route('category.show', 'dry-fruits') }}">Dry Fruits & Nuts</a></li>
                        <li><a href="{{ route('category.show', 'grocery') }}">Grocery & Staples</a></li>
                        <li><a href="{{ route('category.show', 'dairy') }}">Dairy Products</a></li>
                    </ul>
                </div>

                <div class="col-6 col-lg-2">
                    <h6 class="text-white fw-bold mb-3">Partner With Us</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 small">
                        <li><a href="{{ route('vendor.register') }}">Register as a Vendor</a></li>
                        <li><a href="{{ route('agent.register') }}">Join as Delivery Partner</a></li>
                        <li><a href="{{ route('login') }}">Vendor Portal Login</a></li>
                        <li><a href="{{ route('login') }}">Delivery App Login</a></li>
                        <li><a href="{{ route('login') }}">Admin Login</a></li>
                    </ul>
                </div>

                <div class="col-lg-4">
                    <h6 class="text-white fw-bold mb-3">Customer Support</h6>
                    <p class="small text-secondary mb-2">Need help with an order or have questions? Our support team is available 7 days a week.</p>
                    <div class="d-flex align-items-center gap-2 mb-2 text-white fw-semibold">
                        <i class="bi bi-telephone-fill text-success"></i> +91 98765 43210
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-3 text-white fw-semibold">
                        <i class="bi bi-envelope-fill text-success"></i> support@vaishnomart.com
                    </div>
                    <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 px-3 py-2">
                        <i class="bi bi-shield-check me-1"></i> 100% Verified Stores & Safe OTP Delivery
                    </span>
                </div>
            </div>

            <hr class="border-secondary my-3">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 small text-secondary">
                <div>&copy; {{ date('Y') }} VaishnoMart Hyperlocal Marketplace. All rights reserved.</div>
                <div class="d-flex gap-3">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Refund & Cancellation</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Preset Location selector handler
        document.querySelectorAll('.preset-loc').forEach(btn => {
            btn.addEventListener('click', function() {
                const lat = this.dataset.lat;
                const lng = this.dataset.lng;
                const name = this.dataset.name;

                fetch("{{ route('location.set') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ lat: lat, lng: lng, location_name: name })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    }
                });
            });
        });

        // Browser Geolocation
        const geoBtn = document.getElementById('btnDetectGeolocation');
        if (geoBtn) {
            geoBtn.addEventListener('click', function() {
                if (navigator.geolocation) {
                    geoBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Locating...';
                    navigator.geolocation.getCurrentPosition(pos => {
                        const lat = pos.coords.latitude;
                        const lng = pos.coords.longitude;
                        fetch("{{ route('location.set') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ lat: lat, lng: lng, location_name: 'Current GPS Location' })
                        })
                        .then(res => res.json())
                        .then(data => {
                            window.location.reload();
                        });
                    }, err => {
                        alert('Unable to retrieve your location. Please select one of the available neighbourhoods.');
                        geoBtn.innerHTML = '<i class="bi bi-crosshair text-success me-2 fs-5 align-middle"></i> Use Current GPS Location';
                    });
                } else {
                    alert('Geolocation is not supported by your browser.');
                }
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
