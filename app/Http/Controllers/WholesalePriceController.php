<?php

namespace App\Http\Controllers;

use App\Models\WholesalePrice;
use Illuminate\Http\Request;

class WholesalePriceController extends Controller
{
    // Aksi untuk menambah data
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|string|exists:products,id',
            'branch_id' => 'required|string|exists:branches,id',
            'min_qty' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        $product = \App\Models\Product::findOrFail($request->product_id);
        if ($request->price < $product->buy_price) {
            return response()->json([
                'message' => 'Harga grosir tidak boleh di bawah harga beli produk (Rp ' . number_format($product->buy_price, 0, ',', '.') . ')'
            ], 422);
        }

        $wholesalePrice = WholesalePrice::create($validated);

        return response()->json([
            'message' => 'Wholesale price berhasil ditambahkan',
            'data' => $wholesalePrice
        ], 201); // Sesuaikan dengan response format aplikasi, bisa juga menggunakan redirect()
    }

    // Aksi untuk mengubah data
    public function update(Request $request, $id)
    {
        $wholesalePrice = WholesalePrice::findOrFail($id);

        $validated = $request->validate([
            'product_id' => 'sometimes|required|string|exists:products,id',
            'branch_id' => 'sometimes|required|string|exists:branches,id',
            'min_qty' => 'sometimes|required|integer|min:1',
            'price' => 'sometimes|required|numeric|min:0',
        ]);

        $productId = $request->product_id ?? $wholesalePrice->product_id;
        $price = $request->price ?? $wholesalePrice->price;

        $product = \App\Models\Product::findOrFail($productId);
        if ($price < $product->buy_price) {
            return response()->json([
                'message' => 'Harga grosir tidak boleh di bawah harga beli produk (Rp ' . number_format($product->buy_price, 0, ',', '.') . ')'
            ], 422);
        }

        $wholesalePrice->update($validated);

        return response()->json([
            'message' => 'Wholesale price berhasil diperbarui',
            'data' => $wholesalePrice
        ]);
    }

    // Aksi untuk menghapus data
    public function destroy($id)
    {
        $wholesalePrice = WholesalePrice::findOrFail($id);
        $wholesalePrice->delete();

        return response()->json([
            'message' => 'Wholesale price berhasil dihapus'
        ]);
    }
}
