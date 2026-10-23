<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupplierController extends Controller
{
    /**
     * Tampilkan daftar supplier dengan pencarian dan pagination.
     */
    public function index(Request $request): View
    {
        $query = Supplier::withCount('purchases')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->paginate(10)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Tampilkan form pembuatan supplier baru.
     */
    public function create(): View
    {
        return view('suppliers.create');
    }

    /**
     * Simpan supplier baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100', 'unique:suppliers,email'],
        ], [
            'name.required' => 'Nama supplier wajib diisi.',
            'name.max' => 'Nama supplier maksimal 150 karakter.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.max' => 'Nomor telepon maksimal 30 karakter.',
            'email.email' => 'Format email supplier tidak valid.',
            'email.unique' => 'Email supplier tersebut sudah terdaftar.',
        ]);

        Supplier::create($validated);

        return redirect()->route('suppliers.index')->with('success', 'Supplier "' . $validated['name'] . '" berhasil ditambahkan.');
    }

    /**
     * Tampilkan form edit supplier.
     */
    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Perbarui data supplier di database.
     */
    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100', Rule::unique('suppliers', 'email')->ignore($supplier->id)],
        ], [
            'name.required' => 'Nama supplier wajib diisi.',
            'name.max' => 'Nama supplier maksimal 150 karakter.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.max' => 'Nomor telepon maksimal 30 karakter.',
            'email.email' => 'Format email supplier tidak valid.',
            'email.unique' => 'Email supplier sudah digunakan oleh supplier lain.',
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')->with('success', 'Data supplier "' . $supplier->name . '" berhasil diperbarui.');
    }

    /**
     * Hapus supplier dengan proteksi integritas transaksi pembelian.
     */
    public function destroy(Supplier $supplier): RedirectResponse
    {
        // Proteksi: Tolak hapus jika supplier sudah memiliki transaksi pembelian
        if ($supplier->purchases()->exists()) {
            return back()->with('error', 'Pemasok tidak dapat dihapus karena sudah memiliki riwayat transaksi pembelian.');
        }

        $name = $supplier->name;
        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Supplier "' . $name . '" berhasil dihapus.');
    }
}
