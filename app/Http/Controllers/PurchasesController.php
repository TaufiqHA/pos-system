<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Purchases;
use Illuminate\Support\Str;

class PurchasesController extends Controller
{
    // Menampilkan semua data purchases
    public function index(Request $request)
    {
        $purchases = Purchases::with(['supplier', 'branch', 'user', 'items', 'purchasePayments'])->orderBy('created_at', 'desc')->get();

        if ($request->wantsJson()) {
            return response()->json($purchases);
        }

        $suppliers = \App\Models\Suppliers::all();
        $branches = \App\Models\Branch::all();
        $users = \App\Models\User::all();
        $products = \App\Models\Product::all();

        return view('admin.purchases', compact('purchases', 'suppliers', 'branches', 'users', 'products'));
    }

    // Mengalihkan ke index dengan parameter modal create
    public function create()
    {
        return redirect()->route('purchases.index', ['action' => 'create']);
    }

    // Menyimpan data purchase baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'branch_id' => 'required|exists:branches,id',
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'status' => 'required|string|max:50',
            'payment_method' => 'nullable|string|in:TUNAI,TRANSFER,KREDIT',
            
            // Validation for nested items
            'items' => 'nullable|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        // Generate ID string (UUID)
        $validated['id'] = (string) Str::uuid();

        // Generate unique invoice number
        $invoice = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        while (Purchases::where('invoice', $invoice)->exists()) {
            $invoice = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        }
        $validated['invoice'] = $invoice;

        // Set default values if not provided
        $validated['discount'] = $validated['discount'] ?? 0;
        $validated['tax'] = $validated['tax'] ?? 0;

        $purchase = \Illuminate\Support\Facades\DB::transaction(function() use ($validated, $request) {
            $purchase = Purchases::create($validated);

            if ($request->has('items') && is_array($request->items)) {
                foreach ($request->items as $item) {
                    $product = \App\Models\Product::find($item['product_id']);
                    if ($product) {
                        \App\Models\PurchaseItem::create([
                            'id' => (string) Str::uuid(),
                            'purchase_id' => $purchase->id,
                            'product_id' => $product->id,
                            'sku' => $product->sku,
                            'product_name' => $product->name,
                            'unit' => $product->unit ?? 'pcs',
                            'qty' => $item['qty'],
                            'price' => $item['price'],
                            'subtotal' => $item['qty'] * $item['price'],
                        ]);

                        // Jika status pembelian LUNAS, tambah stok untuk branch terkait
                        if ($purchase->status === 'LUNAS') {
                            $stockRecord = \App\Models\ProductStock::where('product_id', $product->id)
                                ->where('branch_id', $purchase->branch_id)
                                ->first();

                            if ($stockRecord) {
                                $stockRecord->increment('stock', $item['qty']);
                            } else {
                                \App\Models\ProductStock::create([
                                    'id' => (string) Str::uuid(),
                                    'product_id' => $product->id,
                                    'branch_id' => $purchase->branch_id,
                                    'stock' => $item['qty'],
                                    'minimum_stock' => 0,
                                    'average_cost' => $item['price'],
                                ]);
                            }
                        }
                    }
                }
            }

            // Create automatic purchase payment
            $paymentMethod = $request->input('payment_method', 'TUNAI');
            $paymentStatus = ($paymentMethod === 'TUNAI' || $paymentMethod === 'TRANSFER') ? 'LUNAS' : 'BELUM BAYAR';
            $paidAt = ($paymentStatus === 'LUNAS') ? now() : null;

            \App\Models\PurchasePayment::create([
                'id' => (string) Str::uuid(),
                'purchase_id' => $purchase->id,
                'method' => $paymentMethod,
                'amount' => $purchase->grand_total,
                'status' => $paymentStatus,
                'paid_at' => $paidAt,
            ]);

            return $purchase;
        });

        $purchase->load(['items', 'purchasePayments']);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Purchase berhasil dibuat', 'data' => $purchase], 201);
        }

        return redirect()->route('purchases.index')->with('success', 'Purchase berhasil dibuat');
    }

    // Menampilkan detail spesifik satu purchase
    public function show($id)
    {
        $purchase = Purchases::with(['supplier', 'branch', 'user', 'items', 'purchasePayments'])->findOrFail($id);

        return response()->json($purchase);
    }

    // Mengalihkan ke index dengan parameter modal edit
    public function edit($id)
    {
        return redirect()->route('purchases.index', ['action' => 'edit', 'id' => $id]);
    }

    // Mengupdate data purchase
    public function update(Request $request, $id)
    {
        $purchase = Purchases::findOrFail($id);

        $validated = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'branch_id' => 'required|exists:branches,id',
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'status' => 'required|string|max:50',
            
            // Validation for nested items
            'items' => 'nullable|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        // Set default values if not provided
        $validated['discount'] = $validated['discount'] ?? 0;
        $validated['tax'] = $validated['tax'] ?? 0;

        \Illuminate\Support\Facades\DB::transaction(function() use ($purchase, $validated, $request) {
            $purchase->update($validated);

            if ($request->has('items')) {
                // Delete previous items
                $purchase->items()->delete();

                // Add new items
                if (is_array($request->items)) {
                    foreach ($request->items as $item) {
                        $product = \App\Models\Product::find($item['product_id']);
                        if ($product) {
                            \App\Models\PurchaseItem::create([
                                'id' => (string) Str::uuid(),
                                'purchase_id' => $purchase->id,
                                'product_id' => $product->id,
                                'sku' => $product->sku,
                                'product_name' => $product->name,
                                'unit' => $product->unit ?? 'pcs',
                                'qty' => $item['qty'],
                                'price' => $item['price'],
                                'subtotal' => $item['qty'] * $item['price'],
                            ]);
                        }
                    }
                }
            }
        });

        $purchase->load('items');

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Purchase berhasil diupdate', 'data' => $purchase]);
        }

        return redirect()->route('purchases.index')->with('success', 'Purchase berhasil diupdate');
    }

    // Menghapus data purchase
    public function destroy(Request $request, $id)
    {
        $purchase = Purchases::findOrFail($id);
        $purchase->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Purchase berhasil dihapus']);
        }

        return redirect()->route('purchases.index')->with('success', 'Purchase berhasil dihapus');
    }
}
