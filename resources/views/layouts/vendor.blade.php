<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Vendor Portal') - VaishnoMart</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --vm-primary: #059669;
            --vm-primary-dark: #064e3b;
            --vm-sidebar-bg: #0f172a;
            --vm-sidebar-hover: #1e293b;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            min-height: 100vh;
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

        .vendor-sidebar {
            width: 260px;
            background: var(--vm-sidebar-bg);
            color: #94a3b8;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }

        .vendor-main {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        @media (max-width: 991px) {
            .vendor-sidebar {
                margin-left: -260px;
                transition: margin 0.3s ease;
            }
            .vendor-sidebar.show {
                margin-left: 0;
            }
            .vendor-main {
                margin-left: 0;
            }
        }

        .nav-link-vendor {
            color: #94a3b8;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            transition: all 0.2s;
            margin-bottom: 4px;
        }

        .nav-link-vendor:hover, .nav-link-vendor.active {
            color: #ffffff;
            background: var(--vm-sidebar-hover);
        }

        .nav-link-vendor.active {
            background: linear-gradient(135deg, var(--vm-primary), #047857);
            font-weight: 600;
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Sidebar Navigation -->
    <aside class="vendor-sidebar p-3" id="vendorSidebar">
        <div class="d-flex align-items-center gap-2 mb-4 px-2 py-2">
            <div class="rounded-3 bg-success p-2 text-white fs-4 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-shop"></i>
            </div>
            <div>
                <div class="fw-bold text-white fs-5 lh-1">VaishnoMart</div>
                <div class="small text-muted" style="font-size: 0.75rem;">Vendor Dashboard</div>
            </div>
        </div>

        <div class="p-2 mb-3 rounded-3 bg-dark border border-secondary border-opacity-25">
            <div class="small text-muted">Store:</div>
            <div class="fw-bold text-white text-truncate">{{ auth()->user()->vendorProfile->business_name ?? 'My Store' }}</div>
            <div class="d-flex align-items-center gap-1 mt-1">
                <span class="badge bg-success" style="font-size: 0.65rem;">Active</span>
                <span class="small text-muted" style="font-size: 0.7rem;">Wallet: ₹{{ number_format(auth()->user()->vendorProfile->wallet_balance ?? 0, 2) }}</span>
            </div>
        </div>

        <nav class="flex-grow-1">
            <a href="{{ route('vendor.dashboard') }}" class="nav-link-vendor {{ request()->routeIs('vendor.dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-fill"></i> Overview
            </a>
            <a href="{{ route('vendor.orders', ['status' => 'incoming']) }}" class="nav-link-vendor {{ request('status') === 'incoming' ? 'active' : '' }}">
                <i class="bi bi-bell-fill text-warning"></i> Incoming Orders
                @php
                    $incCount = \App\Models\Order::where('vendor_profile_id', auth()->user()->vendorProfile?->id)->where('status', 'pending_vendor')->count();
                @endphp
                @if($incCount > 0)
                    <span class="badge bg-danger ms-auto">{{ $incCount }}</span>
                @endif
            </a>
            <a href="{{ route('vendor.orders') }}" class="nav-link-vendor {{ request()->routeIs('vendor.orders') && !request('status') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> All Orders
            </a>
            <a href="{{ route('vendor.products.index') }}" class="nav-link-vendor {{ request()->routeIs('vendor.products.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam-fill"></i> Product Catalogue
            </a>
            <a href="{{ route('vendor.earnings') }}" class="nav-link-vendor {{ request()->routeIs('vendor.earnings') ? 'active' : '' }}">
                <i class="bi bi-wallet2"></i> Earnings & Payouts
            </a>
            <hr class="border-secondary my-3">
            <a href="{{ route('home') }}" class="nav-link-vendor" target="_blank">
                <i class="bi bi-globe"></i> Customer Storefront
            </a>
        </nav>

        <div class="border-top border-secondary pt-3 mt-auto">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="nav-link-vendor w-100 text-danger bg-transparent border-0 text-start">
                    <i class="bi bi-box-arrow-right"></i> Sign Out
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="vendor-main">
        <!-- Top Operational Control Bar -->
        <header class="bg-white border-bottom p-3 px-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light d-lg-none border" id="btnToggleSidebar">
                    <i class="bi bi-list"></i>
                </button>
                <h5 class="fw-bold mb-0">@yield('page_title', 'Dashboard')</h5>
            </div>

            <!-- Store Online / Accepting Orders Toggle -->
            <div class="d-flex align-items-center gap-3">
                <form action="{{ route('vendor.store-status.toggle') }}" method="POST" class="d-flex align-items-center gap-2">
                    @csrf
                    @php $v = auth()->user()->vendorProfile; @endphp
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="is_open" id="storeOpenToggle" onchange="this.form.submit()" {{ $v && $v->is_open ? 'checked' : '' }}>
                        <label class="form-check-label small fw-bold" for="storeOpenToggle">
                            Store {{ $v && $v->is_open ? 'Open' : 'Closed' }}
                        </label>
                    </div>
                </form>

                <div class="dropdown">
                    <button class="btn btn-light btn-sm rounded-pill border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i> {{ auth()->user()->name }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><a class="dropdown-item small" href="{{ route('vendor.dashboard') }}">Dashboard</a></li>
                        <li><a class="dropdown-item small" href="{{ route('vendor.earnings') }}">Settlements</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item small text-danger">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Flash Alerts -->
        <div class="container-fluid px-4 mt-3">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show rounded-3 shadow-sm border-0" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0" role="alert">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>

        <main class="container-fluid px-4 py-3 flex-grow-1">
            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const toggleBtn = document.getElementById('btnToggleSidebar');
        const sidebar = document.getElementById('vendorSidebar');
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('show');
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
