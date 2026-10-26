<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Tampilkan daftar user dengan pencarian, filter, dan pagination.
     */
    public function index(Request $request): View
    {
        $query = User::withCount(['purchases', 'sales'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->paginate(10)->withQueryString();

        return view('users.index', compact('users'));
    }

    /**
     * Tampilkan form penambahan user baru.
     */
    public function create(): View
    {
        return view('users.create');
    }

    /**
     * Simpan user baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:admin,staff,customer'],
        ], [
            'name.required' => 'Nama user wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email tersebut sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal terdiri dari 6 karakter.',
            'role.required' => 'Role pengguna wajib dipilih.',
            'role.in' => 'Role harus admin atau staff.',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('users.index')->with('success', 'User ' . $validated['name'] . ' berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail user (dialihkan ke halaman edit user).
     */
    public function show(User $user): RedirectResponse
    {
        return redirect()->route('users.edit', $user);
    }

    /**
     * Tampilkan form edit user.
     */
    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Perbarui data user di database.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'in:admin,staff,customer'],
        ], [
            'name.required' => 'Nama user wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan oleh user lain.',
            'password.min' => 'Password baru minimal terdiri dari 6 karakter.',
            'role.required' => 'Role pengguna wajib dipilih.',
            'role.in' => 'Role harus admin, staff, atau customer.',
        ]);

        // Cegah Admin login mengubah rolenya sendiri menjadi staff jika tidak ada admin lain
        if ($user->id === auth()->id() && $validated['role'] !== 'admin') {
            return back()->with('error', 'Anda tidak dapat mengubah role Anda sendiri menjadi staff.');
        }

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);

        return redirect()->route('users.index')->with('success', 'Data user ' . $user->name . ' berhasil diperbarui.');
    }

    /**
     * Hapus user dengan validasi proteksi integritas data.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Proteksi 1: Admin tidak boleh menghapus akunnya sendiri yang sedang aktif
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Gagal: Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan.');
        }

        // Proteksi 2: Tidak boleh menghapus user yang sudah memiliki riwayat transaksi
        if ($user->purchases()->exists() || $user->sales()->exists()) {
            return back()->with('error', 'Gagal: User ' . $user->name . ' tidak dapat dihapus karena memiliki riwayat transaksi pembelian atau penjualan.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User ' . $name . ' berhasil dihapus dari sistem.');
    }
}
