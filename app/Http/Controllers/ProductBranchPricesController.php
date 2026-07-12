<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBranchPrices;
use Illuminate\Http\Request;

class ProductBranchPricesController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $query = Product::whereHas('productStocks', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })
        ->select('products.*')
        ->selectSub(function ($q) use ($branchId) {
            $q->select('sale_items.price')
                ->from('sale_items')
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->whereColumn('sale_items.product_id', 'products.id')
                ->where('sales.branch_id', $branchId)
                ->orderByDesc('sales.date')
                ->orderByDesc('sales.created_at')
                ->limit(1);
        }, 'latest_purchase_price')
        ->with([
            'category',
            'branchPrices' => function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            },
            'wholesalePrices' => function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            },
        ]);

        // Search filter (name or SKU)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category_id')) {
            $categoryId = $request->input('category_id');
            $query->where('category_id', $categoryId);
        }

        $products = $query->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $branch = $user->branch;

        if ($request->wantsJson()) {
            return response()->json([
                'products' => $products,
                'branch' => $branch,
            ]);
        }

        return view('cabang.atur-harga-cabang', compact('products', 'categories', 'branch'));
    }

    /**
     * Create (Menyimpan data harga cabang produk baru)
     */
    public function create(Request $request)
    {
        if ($request->has('sell_price')) {
            $request->merge([
                'sell_price' => str_replace('.', '', $request->input('sell_price')),
            ]);
        }

        $validated = $request->validate([
            'product_id' => 'required|string|exists:products,id',
            'branch_id' => 'required|string|exists:branches,id',
            'sell_price' => 'required|numeric|min:0',
            'is_wholesale' => 'sometimes|boolean',
        ]);

        // Validasi agar harga jual tidak di bawah harga beli
        $product = Product::findOrFail($request->product_id);
        if ($request->sell_price < $product->buy_price) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Harga jual cabang tidak boleh di bawah harga beli produk (Rp '.number_format($product->buy_price, 0, ',', '.').')',
                ], 422);
            }

            return redirect()->back()->withErrors(['sell_price' => 'Harga jual cabang tidak boleh di bawah harga beli produk (Rp '.number_format($product->buy_price, 0, ',', '.').')']);
        }

        // Cek kombinasi unique (product_id, branch_id)
        $exists = ProductBranchPrices::where('product_id', $request->product_id)
            ->where('branch_id', $request->branch_id)
            ->exists();

        if ($exists) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Harga cabang untuk produk dan cabang ini sudah ada.',
                ], 422);
            }

            return redirect()->back()->withErrors(['product_id' => 'Harga cabang untuk produk dan cabang ini sudah ada.']);
        }

        if ($request->has('is_wholesale')) {
            $product->update([
                'is_wholesale' => (bool) $request->input('is_wholesale'),
            ]);
        }

        $price = ProductBranchPrices::create([
            'product_id' => $validated['product_id'],
            'branch_id' => $validated['branch_id'],
            'sell_price' => $validated['sell_price'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Harga cabang produk berhasil ditambahkan',
                'data' => $price,
            ], 201);
        }

        return redirect()->back()->with('success', 'Harga cabang produk berhasil ditambahkan.');
    }

    /**
     * Show (Menampilkan data spesifik)
     */
    public function show(Request $request, $id)
    {
        $price = ProductBranchPrices::with(['product', 'branch'])->findOrFail($id);

        if ($request->wantsJson()) {
            return response()->json($price);
        }

        return view('admin.product-branch-prices-show', compact('price'));
    }

    /**
     * Update (Memperbarui data harga cabang produk)
     */
    public function update(Request $request, $id)
    {
        if ($request->has('sell_price')) {
            $request->merge([
                'sell_price' => str_replace('.', '', $request->input('sell_price')),
            ]);
        }

        $priceRecord = ProductBranchPrices::findOrFail($id);

        $validated = $request->validate([
            'product_id' => 'sometimes|required|string|exists:products,id',
            'branch_id' => 'sometimes|required|string|exists:branches,id',
            'sell_price' => 'sometimes|required|numeric|min:0',
            'is_wholesale' => 'sometimes|boolean',
        ]);

        $productId = $request->product_id ?? $priceRecord->product_id;
        $sellPrice = $request->sell_price ?? $priceRecord->sell_price;
        $branchId = $request->branch_id ?? $priceRecord->branch_id;

        // Validasi agar harga jual tidak di bawah harga beli
        $product = Product::findOrFail($productId);
        if ($sellPrice < $product->buy_price) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Harga jual cabang tidak boleh di bawah harga beli produk (Rp '.number_format($product->buy_price, 0, ',', '.').')',
                ], 422);
            }

            return redirect()->back()->withErrors(['sell_price' => 'Harga jual cabang tidak boleh di bawah harga beli produk (Rp '.number_format($product->buy_price, 0, ',', '.').')']);
        }

        // Cek kombinasi unique (product_id, branch_id) jika ada perubahan salah satu
        if ($productId !== $priceRecord->product_id || $branchId !== $priceRecord->branch_id) {
            $exists = ProductBranchPrices::where('product_id', $productId)
                ->where('branch_id', $branchId)
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'message' => 'Harga cabang untuk produk dan cabang ini sudah ada.',
                    ], 422);
                }

                return redirect()->back()->withErrors(['product_id' => 'Harga cabang untuk produk dan cabang ini sudah ada.']);
            }
        }

        if ($request->has('is_wholesale')) {
            $product->update([
                'is_wholesale' => (bool) $request->input('is_wholesale'),
            ]);
        }

        $priceRecord->update([
            'product_id' => $productId,
            'branch_id' => $branchId,
            'sell_price' => $sellPrice,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Harga cabang produk berhasil diupdate',
                'data' => $priceRecord,
            ]);
        }

        return redirect()->back()->with('success', 'Harga cabang produk berhasil diupdate.');
    }

    /**
     * Delete (Menghapus data harga cabang produk)
     */
    public function delete(Request $request, $id)
    {
        $priceRecord = ProductBranchPrices::findOrFail($id);
        $priceRecord->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Harga cabang produk berhasil dihapus',
            ]);
        }

        return redirect()->back()->with('success', 'Harga cabang produk berhasil dihapus.');
    }
}
