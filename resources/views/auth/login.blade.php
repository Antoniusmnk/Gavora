<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Gavora Enterprise ERP</title>

    <!-- Google Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            padding: 1.5rem;
        }
        .card-login {
            border: 1px solid #1e293b;
            border-radius: 0.75rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.4), 0 8px 10px -6px rgba(0, 0, 0, 0.4);
            max-width: 440px;
            width: 100%;
        }
        .demo-panel {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 1rem;
        }
    </style>
</head>
<body>

<div class="card card-login bg-white">
    <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-3 mb-3 shadow-sm" style="width: 52px; height: 52px;">
                <i class="bi bi-box-seam fs-3"></i>
            </div>
            <h4 class="fw-bold text-dark mb-1">GAVORA ERP</h4>
            <p class="text-muted small">Sistem Manajemen Inventaris & Penjualan</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show small border-0" role="alert">
                <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show small border-0" role="alert">
                <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" novalidate>
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label small fw-semibold text-secondary">Alamat Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-secondary border-end-0"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" id="email" 
                           class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror" 
                           value="{{ old('email', 'admin@inventory.test') }}" 
                           placeholder="nama@perusahaan.com" required autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label small fw-semibold text-secondary">Kata Sandi</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-secondary border-end-0"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="password" 
                           class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror" 
                           placeholder="••••••••" value="password123" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
                    <label class="form-check-label small text-muted" for="remember">
                        Ingat Saya
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm mb-3">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Sistem
            </button>
        </form>

        <!-- Panel Akun Pengujian 1-Click Fill -->
        <div class="demo-panel mt-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="fw-semibold small text-dark"><i class="bi bi-person-check me-1 text-primary"></i> Akun Pengujian:</span>
                <span class="text-muted" style="font-size: 0.7rem;">Klik untuk isi otomatis</span>
            </div>
            <div class="d-grid gap-2">
                <button type="button" class="btn btn-outline-danger btn-sm text-start d-flex justify-content-between align-items-center" onclick="fillLogin('admin@inventory.test', 'password123')">
                    <span><i class="bi bi-shield-lock me-1"></i> <strong>Admin</strong> (admin@inventory.test)</span>
                    <i class="bi bi-arrow-right-short fs-5"></i>
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm text-start d-flex justify-content-between align-items-center" onclick="fillLogin('staff1@inventory.test', 'password123')">
                    <span><i class="bi bi-person-badge me-1"></i> <strong>Staff</strong> (staff1@inventory.test)</span>
                    <i class="bi bi-arrow-right-short fs-5"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function fillLogin(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
