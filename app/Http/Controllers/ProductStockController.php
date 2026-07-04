<?php

namespace App\Http\Controllers;

use App\Models\ProductStock;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductStockController extends Controller
{
    /**
     * Read: Menampilkan daftar semua product stocks
     */
    public function index(Request $request)
    {
        $query = ProductStock::with(['product', 'branch']);

        if ($request->routeIs('admin.monitoring-stock')) {
            $userBranchId = auth()->user()->branch_id;
            if ($userBranchId) {
                $query->where('branch_id', $userBranchId);
            }
            $stocks = $query->get();

            return view('admin.monitoringstock', compact('stocks'));
        }

        $stocks = $query->get();

        return response()->json($stocks);
    }

    /**
     * Create: Menyimpan data product stock baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|string|unique:product_stocks,id',
            'product_id' => 'required|string|exists:products,id',
            'branch_id' => 'required|string|exists:branches,id',
            'stock' => 'required|integer',
            'minimum_stock' => 'required|integer',
            'average_cost' => 'required|numeric',
        ]);

        // Generate ID jika tidak diberikan
        if (empty($validated['id'])) {
            $validated['id'] = Str::uuid()->toString();
        }

        $stock = ProductStock::create($validated);

        return response()->json([
            'message' => 'Stock created successfully',
            'data' => $stock,
        ], 201);
    }

    /**
     * Read: Menampilkan detail satu product stock
     */
    public function show(string $id)
    {
        $stock = ProductStock::with(['product', 'branch'])->findOrFail($id);

        return response()->json($stock);
    }

    /**
     * Update: Memperbarui data product stock
     */
    public function update(Request $request, string $id)
    {
        $stock = ProductStock::findOrFail($id);

        $validated = $request->validate([
            'product_id' => 'sometimes|string|exists:products,id',
            'branch_id' => 'sometimes|string|exists:branches,id',
            'stock' => 'sometimes|integer',
            'minimum_stock' => 'sometimes|integer',
            'average_cost' => 'sometimes|numeric',
        ]);

        $stock->update($validated);

        if ($request->has('_token')) {
            return redirect()->route('admin.monitoring-stock')->with('success', 'Stok produk berhasil diperbarui.');
        }

        return response()->json([
            'message' => 'Stock updated successfully',
            'data' => $stock,
        ]);
    }

    /**
     * Delete: Menghapus data product stock
     */
    public function destroy(string $id)
    {
        $stock = ProductStock::findOrFail($id);
        $stock->delete();

        return response()->json([
            'message' => 'Stock deleted successfully',
        ]);
    }
}
