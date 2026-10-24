<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Gavora ERP - Sistem Inventaris & Penjualan')</title>
    
    <!-- Google Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --font-family-sans: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        body {
            background-color: #f8fafc;
            color: #1e293b;
            font-family: var(--font-family-sans);
            font-size: 0.925rem;
            letter-spacing: -0.01em;
        }
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: sticky;
            top: 0;
            overflow-y: auto;
            flex-shrink: 0;
            background-color: #0f172a;
            color: #94a3b8;
            transition: all 0.2s ease-in-out;
            border-right: 1px solid #1e293b;
            z-index: 1000;
            scrollbar-width: thin;
            scrollbar-color: #334155 transparent;
        }
        .sidebar::-webkit-scrollbar {
            width: 5px;
        }
        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }
        .sidebar .nav-link {
            color: #94a3b8;
            padding: 0.625rem 0.875rem;
            border-radius: 0.5rem;
            margin-bottom: 0.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.15s ease-in-out;
        }
        .sidebar .nav-link i {
            font-size: 1.1rem;
        }
        .sidebar .nav-link:hover {
            color: #f8fafc;
            background-color: #1e293b;
        }
        .sidebar .nav-link.active {
            color: #ffffff;
            background-color: #2563eb;
            font-weight: 600;
        }
        .sidebar-heading {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.075em;
            color: #64748b;
            margin-top: 1.25rem;
            margin-bottom: 0.5rem;
            padding-left: 0.875rem;
            font-weight: 700;
        }
        .content-wrapper {
            flex-grow: 1;
            min-width: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .top-navbar {
            position: sticky;
            top: 0;
            z-index: 1020;
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.875rem 1.75rem;
        }
        .card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.625rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.03), 0 1px 2px -1px rgba(0, 0, 0, 0.03);
        }
        .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid #f1f5f9;
        }
        .table thead th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            font-weight: 600;
            border-top: none;
            padding: 0.75rem 1rem;
        }
        .table tbody td {
            padding: 0.875rem 1rem;
        }
        .badge-role-admin {
            background-color: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        .badge-role-staff {
            background-color: #dbeafe;
            color: #2563eb;
            border: 1px solid #bfdbfe;
        }
        .sticky-summary {
            position: sticky;
            top: 5rem;
        }
        @media (max-width: 991.98px) {
            .app-layout {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                border-right: none;
                border-bottom: 1px solid #1e293b;
            }
            .top-navbar {
                position: sticky;
                top: 0;
            }
            .sticky-summary {
                position: static;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="d-flex app-layout">
    <!-- Sidebar Navigasi Perusahaan -->
    <nav class="sidebar d-flex flex-column p-3 flex-shrink-0">
        <a href="{{ route('dashboard') }}" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none px-2">
            <div class="bg-primary text-white rounded-3 p-2 me-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                <i class="bi bi-box-seam fs-5"></i>
            </div>
            <div>
                <span class="fs-5 fw-bold text-white tracking-tight">GAVORA</span>
                <span class="d-block text-secondary text-uppercase fw-semibold" style="font-size: 0.65rem; letter-spacing: 0.1em;">Enterprise ERP</span>
            </div>
        </a>
        <hr class="border-secondary border-opacity-25 my-3">

        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <div class="sidebar-heading">Katalog & Inventaris</div>
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
                        <span class="badge bg-danger ms-auto rounded-pill">{{ $lowCount }}</span>
                    @endif
                </a>
            </li>

            @if(auth()->user()->isAdmin())
            <li>
                <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories*') ? 'active' : '' }}">
                    <i class="bi bi-tags"></i>
                    <span>Kategori Produk</span>
                </a>
            </li>
            <li>
                <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers*') ? 'active' : '' }}">
                    <i class="bi bi-truck"></i>
                    <span>Pemasok (Supplier)</span>
                </a>
            </li>
            @endif

            <div class="sidebar-heading">Transaksi Operasional</div>
            <li>
                <a href="{{ route('purchases.index') }}" class="nav-link {{ request()->routeIs('purchases*') ? 'active' : '' }}">
                    <i class="bi bi-arrow-down-left-square"></i>
                    <span>Pembelian (Stok Masuk)</span>
                </a>
            </li>
            <li>
                <a href="{{ route('sales.index') }}" class="nav-link {{ request()->routeIs('sales*') ? 'active' : '' }}">
                    <i class="bi bi-arrow-up-right-square"></i>
                    <span>Penjualan (Kasir)</span>
                </a>
            </li>

            @if(auth()->user()->isAdmin())
            <div class="sidebar-heading">Administrasi</div>
            <li>
                <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>Manajemen Pengguna</span>
                </a>
            </li>
            @endif
        </ul>

        <hr class="border-secondary border-opacity-25 my-3">
        <div class="px-2 d-flex align-items-center justify-content-between">
            <span class="small text-secondary">Aplikasi Versi 1.0</span>
            <span class="badge bg-secondary-subtle text-light-emphasis small">ERP Online</span>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="content-wrapper">
        <!-- Top Navbar -->
        <header class="top-navbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <span class="text-secondary small fw-medium">
                    <i class="bi bi-calendar3 me-1 text-primary"></i> {{ now()->translatedFormat('l, d F Y') }}
                </span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2 shadow-sm" style="width: 36px; height: 36px; font-weight: 600;">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="text-start me-1">
                            <div class="fw-semibold small leading-tight text-dark">{{ auth()->user()->name }}</div>
                            <span class="badge {{ auth()->user()->isAdmin() ? 'badge-role-admin' : 'badge-role-staff' }}" style="font-size: 0.675rem;">
                                {{ ucfirst(auth()->user()->role) }}
                            </span>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="userDropdown">
                        <li>
                            <div class="px-3 py-2 border-bottom">
                                <div class="fw-semibold text-dark small">{{ auth()->user()->name }}</div>
                                <div class="text-muted small" style="font-size: 0.75rem;">{{ auth()->user()->email }}</div>
                            </div>
                        </li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" id="logout-form">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger d-flex align-items-center py-2">
                                    <i class="bi bi-box-arrow-right me-2"></i> Keluar (Logout)
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
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm border-0" role="alert">
                    <i class="bi bi-check-circle-fill fs-5 me-2 text-success"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm border-0" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-danger"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center shadow-sm border-0" role="alert">
                    <i class="bi bi-exclamation-circle-fill fs-5 me-2 text-warning-emphasis"></i>
                    <div>{{ session('warning') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                    <div class="d-flex align-items-center mb-1 fw-semibold text-danger">
                        <i class="bi bi-x-circle-fill fs-5 me-2"></i>
                        <span>Mohon periksa kembali formulir berikut:</span>
                    </div>
                    <ul class="mb-0 ps-4 small text-danger-emphasis">
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
                &copy; {{ date('Y') }} Gavora Enterprise Resource Planning (ERP). Hak Cipta Dilindungi.
            </div>
        </footer>
    </div>
</div>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
