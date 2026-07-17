<?php

namespace App\Http\Controllers;

use App\Models\Lokasi;
use Illuminate\Http\Request;

class LokasiController extends Controller
{
    /**
     * Display a listing of the locations.
     */
    public function index(Request $request)
    {
        $query = Lokasi::where('aktif', 'Y');

        if ($request->filled('search')) {
            $query->where('nama_lokasi', 'like', '%' . $request->search . '%');
        }

        $lokasis = $query->paginate(10);

        return view('pages.admin.lokasi.index', compact('lokasis'));
    }

    /**
     * Store a newly created location in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_lokasi' => 'required|string|max:255',
        ]);

        Lokasi::create([
            'nama_lokasi' => $request->nama_lokasi,
            'aktif' => 'Y',
        ]);

        return redirect()->route('admin.lokasi.index')
            ->with('success', 'Lokasi berhasil ditambahkan!');
    }

    /**
     * Update the specified location in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_lokasi' => 'required|string|max:255',
        ]);

        $lokasi = Lokasi::findOrFail($id);
        $lokasi->update([
            'nama_lokasi' => $request->nama_lokasi,
        ]);

        return redirect()->route('admin.lokasi.index')
            ->with('success', 'Lokasi berhasil diperbarui!');
    }

    /**
     * Remove the specified location from storage (Soft Delete).
     */
    public function destroy($id)
    {
        $lokasi = Lokasi::findOrFail($id);
        
        // Soft delete: set aktif to 'N'
        $lokasi->update([
            'aktif' => 'N',
        ]);

        return redirect()->route('admin.lokasi.index')
            ->with('success', 'Lokasi berhasil dihapus (soft delete)!');
    }
}
