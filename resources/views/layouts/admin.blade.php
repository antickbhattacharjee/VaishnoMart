<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Platform') - VaishnoMart Admin</title>
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
            --vm-admin-primary: #3b82f6;
            --vm-admin-dark: #1e3a8a;
            --vm-sidebar-bg: #0b1329;
            --vm-sidebar-hover: #1e293b;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
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
            color: #3b82f6;
            border-color: #cbd5e1;
        }
        .pagination .page-item.active .page-link {
            background-color: #3b82f6 !important;
            border-color: #3b82f6 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.25) !important;
        }
        .pagination .page-item.disabled .page-link {
            background-color: #f8fafc;
            color: #94a3b8;
            border-color: #f1f5f9;
        }

        .admin-sidebar {
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

        .admin-main {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        @media (max-width: 991px) {
            .admin-sidebar {
                margin-left: -260px;
                transition: margin 0.3s ease;
            }
            .admin-sidebar.show {
                margin-left: 0;
            }
            .admin-main {
                margin-left: 0;
            }
        }

        .nav-link-admin {
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

        .nav-link-admin:hover, .nav-link-admin.active {
            color: #ffffff;
            background: var(--vm-sidebar-hover);
        }

        .nav-link-admin.active {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .admin-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            background: #ffffff;
        }

        .table th {
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            background-color: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Sidebar Navigation -->
    <aside class="admin-sidebar p-3" id="adminSidebar">
        <div class="d-flex align-items-center gap-2 mb-4 px-2 py-2">
            <div class="rounded-3 bg-primary p-2 text-white fs-4 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <i class="bi bi-shield-check"></i>
            </div>
            <div>
                <h6 class="mb-0 text-white fw-bold">VaishnoMart</h6>
                <small class="text-xs text-primary-emphasis fw-semibold">Super Admin Console</small>
            </div>
        </div>

        <nav class="flex-grow-1 overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}" class="nav-link-admin {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard Overview</span>
            </a>
            <a href="{{ route('admin.orders.index') }}" class="nav-link-admin {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                <i class="bi bi-bag-check"></i>
                <span>Live Order Monitor</span>
            </a>
            <a href="{{ route('admin.vendors.index') }}" class="nav-link-admin {{ request()->routeIs('admin.vendors.*') ? 'active' : '' }}">
                <i class="bi bi-shop"></i>
                <span>Vendors & Stores</span>
            </a>
            <a href="{{ route('admin.agents.index') }}" class="nav-link-admin {{ request()->routeIs('admin.agents.*') ? 'active' : '' }}">
                <i class="bi bi-bicycle"></i>
                <span>Delivery Fleet</span>
            </a>
            <a href="{{ route('admin.categories.index') }}" class="nav-link-admin {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <i class="bi bi-grid"></i>
                <span>Categories</span>
            </a>
            <a href="{{ route('admin.settlements.index') }}" class="nav-link-admin {{ request()->routeIs('admin.settlements.*') ? 'active' : '' }}">
                <i class="bi bi-cash-stack"></i>
                <span>Vendor Settlements</span>
            </a>
            <a href="{{ route('admin.settings.index') }}" class="nav-link-admin {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <i class="bi bi-sliders"></i>
                <span>Platform Settings</span>
            </a>
            <a href="{{ route('admin.audit-logs') }}" class="nav-link-admin {{ request()->routeIs('admin.audit-logs') ? 'active' : '' }}">
                <i class="bi bi-journal-text"></i>
                <span>Audit & Security Logs</span>
            </a>

            <div class="border-top border-secondary opacity-25 my-3"></div>

            <a href="{{ route('home') }}" target="_blank" class="nav-link-admin">
                <i class="bi bi-box-arrow-up-right"></i>
                <span>View Storefront</span>
            </a>
        </nav>

        <div class="pt-3 border-top border-secondary opacity-25 mt-auto"></div>
        <div class="px-2 py-2 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
                    {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="text-truncate" style="max-width: 130px;">
                    <div class="text-white text-sm fw-semibold text-truncate">{{ Auth::user()->name }}</div>
                    <div class="text-muted text-xs text-truncate">Administrator</div>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger border-0 p-1" title="Sign Out">
                    <i class="bi bi-box-arrow-right fs-5"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="admin-main">
        <!-- Top Navigation -->
        <header class="bg-white border-bottom py-2 px-4 sticky-top d-flex align-items-center justify-content-between shadow-xs">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light d-lg-none p-1" id="sidebarToggle">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <div class="d-none d-sm-block">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                        <i class="bi bi-shield-lock-fill me-1"></i> VaishnoMart Ecosystem Control
                    </span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <span class="text-xs text-muted d-none d-md-inline">
                    Server Time: {{ now()->timezone('Asia/Kolkata')->format('d M Y, h:i A') }} IST
                </span>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Live Orders
                </a>
            </div>
        </header>

        <!-- Main Body -->
        <main class="flex-grow-1 p-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <div class="fw-bold mb-1">Please fix the following issues:</div>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="bg-white border-top py-3 px-4 text-center text-muted text-xs">
            &copy; {{ date('Y') }} VaishnoMart Admin Platform. Hyperlocal Multi-Vendor Engine. Built with Laravel 11.
        </footer>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebarToggle = document.getElementById('sidebarToggle');
        const adminSidebar = document.getElementById('adminSidebar');
        if (sidebarToggle && adminSidebar) {
            sidebarToggle.addEventListener('click', () => {
                adminSidebar.classList.toggle('show');
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
