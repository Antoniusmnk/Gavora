<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Cek autentikasi
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu untuk mengakses sistem.');
        }

        $user = auth()->user();

        // 2. Cek apakah role user saat ini tercakup dalam role yang diizinkan
        if (!in_array($user->role, $roles)) {
            abort(403, 'Akses ditolak: Anda tidak memiliki izin untuk mengakses halaman atau aksi ini.');
        }

        return $next($request);
    }
}
