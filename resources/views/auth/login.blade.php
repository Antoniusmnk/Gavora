<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gavora Inventory System</title>
    <!-- Bootstrap 5.3 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            padding: 1.5rem;
        }
        .card-login {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 460px;
            width: 100%;
        }
        .demo-box {
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 0.5rem;
            padding: 0.85rem;
        }
    </style>
</head>
<body>

<div class="card card-login bg-white">
    <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle mb-3" style="width: 56px; height: 56px;">
                <i class="bi bi-box-seam-fill fs-2"></i>
            </div>
            <h4 class="fw-bold text-dark mb-1">GAVORA ERP</h4>
            <p class="text-muted small">Inventory & Sales Management System (UTS)</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show small" role="alert">
                <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" novalidate>
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label small fw-semibold">Alamat Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" id="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           value="{{ old('email', 'admin@inventory.test') }}" 
                           placeholder="nama@domain.com" required autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label small fw-semibold">Kata Sandi (Password)</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="password" 
                           class="form-control @error('password') is-invalid @enderror" 
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

        <!-- Kotak Informasi Akun Demo untuk Pengujian Dosen -->
        <div class="demo-box mt-3">
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-info-circle-fill text-primary me-2"></i>
                <span class="fw-semibold small text-dark">Akun Uji Coba Penilaian UTS:</span>
            </div>
            <div class="small text-secondary mb-1">
                <span class="badge bg-danger me-1">ADMIN</span> 
                <code>admin@inventory.test</code> | Pass: <code>password123</code>
            </div>
            <div class="small text-secondary">
                <span class="badge bg-primary me-1">STAFF</span> 
                <code>staff1@inventory.test</code> | Pass: <code>password123</code>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
