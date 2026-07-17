<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        abort_unless($request->user()->role === 'admin', 403);

        $request->validate([
            'nama' => 'required|string|max:255|unique:kategoris,nama',
        ]);

        Kategori::create([
            'nama' => $request->nama,
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Kategori berhasil ditambahkan!');
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, $id)
    {
        abort_unless($request->user()->role === 'admin', 403);

        $request->validate([
            'nama' => 'required|string|max:255|unique:kategoris,nama,' . $id,
        ]);

        $category = Kategori::findOrFail($id);
        $category->update([
            'nama' => $request->nama,
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Kategori berhasil diperbarui!');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Request $request, $id)
    {
        abort_unless($request->user()->role === 'admin', 403);

        $category = Kategori::findOrFail($id);
        $category->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Kategori berhasil dihapus!');
    }
}