<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Tampilkan daftar kategori dengan pencarian dan pagination.
     */
    public function index(Request $request): View
    {
        $query = Category::withCount('products')->latest();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $categories = $query->paginate(10)->withQueryString();

        return view('categories.index', compact('categories'));
    }

    /**
     * Tampilkan form pembuatan kategori.
     */
    public function create(): View
    {
        return view('categories.create');
    }

    /**
     * Simpan kategori baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.max' => 'Nama kategori maksimal 100 karakter.',
            'name.unique' => 'Nama kategori ini sudah terdaftar sebelumnya.',
        ]);

        Category::create([
            'name' => $validated['name'],
        ]);

        return redirect()->route('categories.index')->with('success', 'Kategori "' . $validated['name'] . '" berhasil ditambahkan.');
    }

    /**
     * Tampilkan form edit kategori.
     */
    public function edit(Category $category): View
    {
        return view('categories.edit', compact('category'));
    }

    /**
     * Perbarui data kategori di database.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category->id)],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.max' => 'Nama kategori maksimal 100 karakter.',
            'name.unique' => 'Nama kategori ini sudah digunakan oleh kategori lain.',
        ]);

        $category->update([
            'name' => $validated['name'],
        ]);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui menjadi "' . $validated['name'] . '".');
    }

    /**
     * Hapus kategori dengan proteksi integritas relasi produk.
     */
    public function destroy(Category $category): RedirectResponse
    {
        // Proteksi: Tolak hapus jika kategori masih memiliki produk
        if ($category->products()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih memiliki produk terkait.');
        }

        $name = $category->name;
        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Kategori "' . $name . '" berhasil dihapus.');
    }
}
