<?php

namespace App\Http\Controllers;

use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WilayahController extends Controller
{
    // 0. Index (Menampilkan semua data)
    public function index(Request $request)
    {
        $wilayahs = Wilayah::all();

        if ($request->wantsJson()) {
            return response()->json($wilayahs);
        }

        return view('admin.wilayah', compact('wilayahs'));
    }

    // 1. Create (Menyimpan data baru)
    public function create(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|string|unique:wilayahs,id|max:255',
            'name' => 'required|string|max:255',
        ]);

        if (empty($validated['id'])) {
            $validated['id'] = (string) Str::uuid();
        }

        $wilayah = Wilayah::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Wilayah berhasil ditambahkan',
                'data' => $wilayah,
            ], 201);
        }

        return redirect()->back()->with('success', 'Wilayah berhasil ditambahkan.');
    }

    // 2. Show (Menampilkan data spesifik)
    public function show($id)
    {
        $wilayah = Wilayah::findOrFail($id);

        return response()->json($wilayah);
    }

    // 3. Update (Memperbarui data)
    public function update(Request $request, $id)
    {
        $wilayah = Wilayah::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $wilayah->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Wilayah berhasil diupdate',
                'data' => $wilayah,
            ]);
        }

        return redirect()->back()->with('success', 'Wilayah berhasil diupdate.');
    }

    // 4. Delete (Menghapus data)
    public function delete(Request $request, $id)
    {
        $wilayah = Wilayah::findOrFail($id);
        $wilayah->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Wilayah berhasil dihapus',
            ]);
        }

        return redirect()->back()->with('success', 'Wilayah berhasil dihapus.');
    }
}
