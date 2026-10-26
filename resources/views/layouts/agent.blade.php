<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Delivery Agent App') - VaishnoMart</title>
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
            --vm-accent: #f59e0b;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
            padding-bottom: 70px;
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

        .agent-header {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 0;
            position: sticky;
            top: 0;
            z-index: 1020;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .agent-bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-around;
            padding: 8px 0;
            z-index: 1030;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
        }

        .agent-nav-item {
            color: #64748b;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .agent-nav-item.active, .agent-nav-item:hover {
            color: var(--vm-primary);
        }

        .agent-nav-item i {
            font-size: 1.25rem;
            margin-bottom: 2px;
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Agent Top Bar -->
    <header class="agent-header">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-warning text-dark p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-bicycle fs-5"></i>
                </div>
                <div>
                    <div class="fw-bold fs-6 lh-1">VaishnoMart Fleet</div>
                    <div class="small text-muted" style="font-size: 0.7rem;">{{ auth()->user()->name }}</div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                @php $agentProfile = auth()->user()->deliveryAgentProfile; @endphp
                <form action="{{ route('agent.online.toggle') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm {{ $agentProfile && $agentProfile->is_online ? 'btn-success' : 'btn-secondary' }} rounded-pill px-3 py-1 fw-bold">
                        <i class="bi bi-circle-fill me-1" style="font-size: 0.6rem;"></i>
                        {{ $agentProfile && $agentProfile->is_online ? 'ONLINE' : 'OFFLINE' }}
                    </button>
                </form>

                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-light btn-sm rounded-circle border" title="Logout">
                        <i class="bi bi-box-arrow-right text-danger"></i>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Global Alerts -->
    <div class="container mt-2">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 small" role="alert">
                <i class="bi bi-check-circle-fill me-1"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show rounded-3 shadow-sm border-0 small" role="alert">
                <i class="bi bi-info-circle-fill me-1"></i>{{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0 small" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="container py-2">
        @yield('content')
    </main>

    <!-- Bottom Mobile Navigation Bar -->
    <nav class="agent-bottom-nav">
        <a href="{{ route('agent.dashboard') }}" class="agent-nav-item {{ request()->routeIs('agent.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            <span>Requests</span>
        </a>
        <a href="{{ route('agent.current-order') }}" class="agent-nav-item {{ request()->routeIs('agent.current-order') ? 'active' : '' }}">
            <i class="bi bi-geo-alt-fill text-warning"></i>
            <span>Active Trip</span>
        </a>
        <a href="{{ route('agent.history') }}" class="agent-nav-item {{ request()->routeIs('agent.history') ? 'active' : '' }}">
            <i class="bi bi-clock-history"></i>
            <span>History</span>
        </a>
        <a href="{{ route('agent.earnings') }}" class="agent-nav-item {{ request()->routeIs('agent.earnings') ? 'active' : '' }}">
            <i class="bi bi-wallet2 text-success"></i>
            <span>Earnings</span>
        </a>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
