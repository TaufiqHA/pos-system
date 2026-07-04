<?php

namespace App\Http\Controllers;

use App\Models\Suppliers;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SuppliersController extends Controller
{
    // Menampilkan semua data suppliers
    public function index(Request $request)
    {
        // Ambil semua data supplier dari database
        $suppliers = Suppliers::all();

        if ($request->wantsJson()) {
            return response()->json($suppliers);
        }

        // Return file view resources/views/admin/suppliers.blade.php dan berikan data $suppliers
        return view('admin.suppliers', compact('suppliers'));
    }

    // Mengalihkan ke index dengan parameter modal create
    public function create()
    {
        return redirect()->route('suppliers.index', ['action' => 'create']);
    }

    // Menyimpan data supplier baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Generate ID string (Contoh menggunakan UUID. Sesuaikan jika ada format khusus)
        $validated['id'] = (string) Str::uuid();

        $supplier = Suppliers::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Supplier berhasil dibuat', 'data' => $supplier], 201);
        }

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil dibuat');
    }

    // Menampilkan detail spesifik satu supplier
    public function show($id)
    {
        $supplier = Suppliers::findOrFail($id);

        return response()->json($supplier);
    }

    // Mengalihkan ke index dengan parameter modal edit
    public function edit($id)
    {
        return redirect()->route('suppliers.index', ['action' => 'edit', 'id' => $id]);
    }

    // Mengupdate data supplier
    public function update(Request $request, $id)
    {
        $supplier = Suppliers::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $supplier->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Supplier berhasil diupdate', 'data' => $supplier]);
        }

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil diupdate');
    }

    // Menghapus data supplier
    public function destroy(Request $request, $id)
    {
        $supplier = Suppliers::findOrFail($id);
        $supplier->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Supplier berhasil dihapus']);
        }

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil dihapus');
    }
}
