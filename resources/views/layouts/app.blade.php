<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Gavora Inventory & Sales')</title>
    <!-- Bootstrap 5.3 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 250px;
        }
        body {
            background-color: #f8f9fa;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background-color: #1e293b;
            color: #f1f5f9;
            transition: all 0.3s;
        }
        .sidebar .nav-link {
            color: #94a3b8;
            padding: 0.65rem 1rem;
            border-radius: 0.375rem;
            margin-bottom: 0.25rem;
            font-size: 0.925rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .sidebar .nav-link:hover {
            color: #ffffff;
            background-color: #334155;
        }
        .sidebar .nav-link.active {
            color: #ffffff;
            background-color: #0d6efd;
            font-weight: 500;
        }
        .sidebar-heading {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-top: 1rem;
            margin-bottom: 0.5rem;
            padding-left: 1rem;
            font-weight: 700;
        }
        .content-wrapper {
            flex-grow: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .top-navbar {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1.5rem;
        }
        .badge-role-admin {
            background-color: #dc3545;
            color: #ffffff;
        }
        .badge-role-staff {
            background-color: #0d6efd;
            color: #ffffff;
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <nav class="sidebar d-flex flex-column p-3 flex-shrink-0">
        <a href="{{ route('dashboard') }}" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none px-2">
            <i class="bi bi-box-seam-fill fs-4 me-2 text-primary"></i>
            <span class="fs-5 fw-bold">GAVORA ERP</span>
        </a>
        <hr class="border-secondary my-3">

        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <div class="sidebar-heading">Master Data</div>
            <li>
                <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.index') || request()->routeIs('products.show') ? 'active' : '' }}">
                    <i class="bi bi-boxes"></i>
                    <span>Katalog Produk</span>
                </a>
            </li>
            <li>
                <a href="{{ route('products.low-stock') }}" class="nav-link {{ request()->routeIs('products.low-stock') ? 'active' : '' }}">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span>Stok Menipis</span>
                    @php $lowCount = \App\Models\Product::lowStock()->count(); @endphp
                    @if($lowCount > 0)
                        <span class="badge bg-danger ms-auto">{{ $lowCount }}</span>
                    @endif
                </a>
            </li>

            @if(auth()->user()->isAdmin())
            <li>
                <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories*') ? 'active' : '' }}">
                    <i class="bi bi-tags"></i>
                    <span>Kategori</span>
                </a>
            </li>
            <li>
                <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers*') ? 'active' : '' }}">
                    <i class="bi bi-truck"></i>
                    <span>Supplier</span>
                </a>
            </li>
            @endif

            <div class="sidebar-heading">Transaksi</div>
            <li>
                <a href="{{ url('/purchases') }}" class="nav-link {{ request()->is('purchases*') ? 'active' : '' }}">
                    <i class="bi bi-arrow-down-left-square"></i>
                    <span>Pembelian (Stock-In)</span>
                </a>
            </li>
            <li>
                <a href="{{ url('/sales') }}" class="nav-link {{ request()->is('sales*') ? 'active' : '' }}">
                    <i class="bi bi-arrow-up-right-square"></i>
                    <span>Penjualan (Stock-Out)</span>
                </a>
            </li>

            @if(auth()->user()->isAdmin())
            <div class="sidebar-heading">Laporan & Pengaturan</div>
            <li>
                <a href="{{ url('/reports') }}" class="nav-link {{ request()->is('reports*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <span>Laporan Transaksi</span>
                </a>
            </li>
            <li>
                <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i>
                    <span>Manajemen User</span>
                </a>
            </li>
            @endif
        </ul>

        <hr class="border-secondary my-3">
        <div class="px-2 small text-muted">
            <i class="bi bi-shield-lock me-1"></i> Mode:
            <span class="badge {{ auth()->user()->isAdmin() ? 'badge-role-admin' : 'badge-role-staff' }}">
                {{ strtoupper(auth()->user()->role) }}
            </span>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="content-wrapper">
        <!-- Top Navbar -->
        <header class="top-navbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <span class="text-secondary small fw-medium">
                    <i class="bi bi-calendar3 me-1"></i> {{ now()->translatedFormat('l, d F Y') }}
                </span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 34px; height: 34px; font-weight: 600;">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="text-start me-1">
                            <div class="fw-semibold small leading-tight">{{ auth()->user()->name }}</div>
                            <span class="badge {{ auth()->user()->isAdmin() ? 'badge-role-admin' : 'badge-role-staff' }}" style="font-size: 0.65rem;">
                                {{ ucfirst(auth()->user()->role) }}
                            </span>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userDropdown">
                        <li><span class="dropdown-item-text text-muted small">{{ auth()->user()->email }}</span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" id="logout-form">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger d-flex align-items-center">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Main Body -->
        <main class="p-4">
            <!-- Flash Message Alerts -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                    <i class="bi bi-exclamation-circle-fill fs-5 me-2"></i>
                    <div>{{ session('warning') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <div class="d-flex align-items-center mb-1 fw-semibold">
                        <i class="bi bi-x-circle-fill fs-5 me-2"></i>
                        <span>Terdapat kesalahan pada input form:</span>
                    </div>
                    <ul class="mb-0 ps-4 small">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Page Content -->
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="mt-auto py-3 bg-white text-center text-muted small border-top">
            <div class="container-fluid">
                &copy; {{ date('Y') }} Gavora Inventory & Sales Management System — Ujian Tengah Semester (UTS)
            </div>
        </footer>
    </div>
</div>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
