<?php

namespace App\Http\Controllers;

use App\Models\Category;
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

    /**
     * Cabang: Monitoring stok produk di cabang saat ini
     */
    public function monitoringStok(Request $request)
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $query = ProductStock::with(['product.category', 'branch'])
            ->where('branch_id', $branchId);

        // Search filter (name or SKU)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category_id')) {
            $categoryId = $request->input('category_id');
            $query->whereHas('product', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        // Status filter (menipis / aman)
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'menipis') {
                $query->whereColumn('stock', '<=', 'minimum_stock');
            } elseif ($status === 'aman') {
                $query->whereColumn('stock', '>', 'minimum_stock');
            }
        }

        $stocks = $query->get();
        $categories = Category::orderBy('name')->get();

        if ($request->wantsJson()) {
            return response()->json($stocks);
        }

        return view('cabang.monitoring-stok', compact('stocks', 'categories'));
    }

    /**
     * Cabang: Update stok produk cabang
     */
    public function updateCabangStock(Request $request, string $id)
    {
        $user = auth()->user();
        $stock = ProductStock::where('id', $id)
            ->where('branch_id', $user->branch_id)
            ->firstOrFail();

        $validated = $request->validate([
            'stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'average_cost' => 'required|numeric|min:0',
        ]);

        $stock->update($validated);

        if ($request->has('_token')) {
            return redirect()->route('cabang.monitoring-stok')->with('success', 'Stok produk cabang berhasil diperbarui.');
        }

        return response()->json([
            'message' => 'Stock updated successfully',
            'data' => $stock,
        ]);
    }
}
