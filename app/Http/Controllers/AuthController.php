<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return Auth::user()->isCustomer()
                ? redirect()->route('products.index')
                : redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Proses autentikasi user.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $redirectRoute = Auth::user()->isCustomer() ? route('products.index') : route('dashboard');

            return redirect()->intended($redirectRoute)
                ->with('success', 'Selamat datang, ' . Auth::user()->name . ' (' . ucfirst(Auth::user()->role) . ')!');
        }

        return back()
            ->withErrors(['email' => 'Kombinasi email dan password tidak sesuai.'])
            ->onlyInput('email');
    }

    /**
     * Login instan 1-klik sebagai Customer (tanpa input sandi).
     */
    public function loginAsCustomer(Request $request): RedirectResponse
    {
        $customer = \App\Models\User::firstOrCreate(
            ['email' => 'customer@gavora.test'],
            [
                'name' => 'Pelanggan / Customer',
                'password' => \Illuminate\Support\Facades\Hash::make('customer123'),
                'role' => 'customer',
            ]
        );

        Auth::login($customer);
        $request->session()->regenerate();

        return redirect()->route('products.index')
            ->with('success', 'Selamat datang di Katalog Produk Gavora! Anda masuk dengan akses Customer.');
    }

    /**
     * Logout user dari sesi aplikasi.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}
