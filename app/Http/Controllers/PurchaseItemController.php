<?php

namespace App\Http\Controllers;

use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PurchaseItemController extends Controller
{
    // 1. Create (Menyimpan data purchase item baru)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'product_id' => 'required|exists:products,id',
            'sku' => 'required|string|max:255',
            'product_name' => 'required|string|max:255',
            'unit' => 'required|string|max:255',
            'qty' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'subtotal' => 'nullable|numeric|min:0',
        ]);

        // Generate ID string (UUID) secara manual
        $validated['id'] = (string) Str::uuid();

        // Hitung subtotal jika tidak diberikan secara eksplisit
        $validated['subtotal'] = $validated['subtotal'] ?? ($validated['qty'] * $validated['price']);

        $purchaseItem = PurchaseItem::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Purchase item berhasil dibuat',
                'data' => $purchaseItem,
            ], 201);
        }

        return redirect()->back()->with('success', 'Purchase item berhasil dibuat');
    }

    // 2. Show (Menampilkan detail spesifik dari satu purchase item)
    public function show($id)
    {
        $purchaseItem = PurchaseItem::with(['purchase', 'product'])->findOrFail($id);

        return response()->json($purchaseItem);
    }

    // 3. Update (Memperbarui data purchase item yang ada)
    public function update(Request $request, $id)
    {
        $purchaseItem = PurchaseItem::findOrFail($id);

        $validated = $request->validate([
            'purchase_id' => 'sometimes|required|exists:purchases,id',
            'product_id' => 'sometimes|required|exists:products,id',
            'sku' => 'sometimes|required|string|max:255',
            'product_name' => 'sometimes|required|string|max:255',
            'unit' => 'sometimes|required|string|max:255',
            'qty' => 'sometimes|required|integer|min:1',
            'price' => 'sometimes|required|numeric|min:0',
            'subtotal' => 'nullable|numeric|min:0',
        ]);

        // Hitung ulang subtotal jika qty atau price berubah dan subtotal tidak dikirim
        if (isset($validated['qty']) || isset($validated['price'])) {
            $qty = $validated['qty'] ?? $purchaseItem->qty;
            $price = $validated['price'] ?? $purchaseItem->price;
            $validated['subtotal'] = $validated['subtotal'] ?? ($qty * $price);
        }

        $purchaseItem->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Purchase item berhasil diupdate',
                'data' => $purchaseItem,
            ]);
        }

        return redirect()->back()->with('success', 'Purchase item berhasil diupdate');
    }

    // 4. Delete (Menghapus data purchase item)
    public function destroy(Request $request, $id)
    {
        $purchaseItem = PurchaseItem::findOrFail($id);
        $purchaseItem->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Purchase item berhasil dihapus',
            ]);
        }

        return redirect()->back()->with('success', 'Purchase item berhasil dihapus');
    }
}
