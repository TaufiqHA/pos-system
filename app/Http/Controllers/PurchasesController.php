<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Debts;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PurchaseItem;
use App\Models\PurchasePayment;
use App\Models\Purchases;
use App\Models\Suppliers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $suppliers = Suppliers::all();
        $branches = Branch::all();
        $users = User::all();
        $products = Product::all();

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
        $invoice = 'INV-'.date('Ymd').'-'.strtoupper(Str::random(6));
        while (Purchases::where('invoice', $invoice)->exists()) {
            $invoice = 'INV-'.date('Ymd').'-'.strtoupper(Str::random(6));
        }
        $validated['invoice'] = $invoice;

        // Set default values if not provided
        $validated['discount'] = $validated['discount'] ?? 0;
        $validated['tax'] = $validated['tax'] ?? 0;

        $purchase = DB::transaction(function () use ($validated, $request) {
            $paymentMethod = $request->input('payment_method', 'TUNAI');
            if ($paymentMethod === 'KREDIT') {
                $validated['status'] = 'completed';
            }

            $purchase = Purchases::create($validated);

            if ($request->has('items') && is_array($request->items)) {
                foreach ($request->items as $item) {
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        PurchaseItem::create([
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

                        // Jika status pembelian LUNAS atau completed, tambah stok untuk branch terkait
                        if (in_array(strtoupper($purchase->status), ['LUNAS', 'COMPLETED'])) {
                            $stockRecord = ProductStock::where('product_id', $product->id)
                                ->where('branch_id', $purchase->branch_id)
                                ->first();

                            if ($stockRecord) {
                                $oldStock = max(0, $stockRecord->stock);
                                $oldCost = $stockRecord->average_cost;
                                $newQty = $item['qty'];
                                $newPrice = $item['price'];

                                $totalQty = $oldStock + $newQty;
                                $newAverageCost = $totalQty > 0
                                    ? (($oldStock * $oldCost) + ($newQty * $newPrice)) / $totalQty
                                    : $newPrice;

                                $stockRecord->update([
                                    'stock' => $stockRecord->stock + $newQty,
                                    'average_cost' => $newAverageCost,
                                ]);
                            } else {
                                ProductStock::create([
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
            $paymentStatus = ($paymentMethod === 'TUNAI' || $paymentMethod === 'TRANSFER') ? 'LUNAS' : 'BELUM BAYAR';
            $paidAt = ($paymentStatus === 'LUNAS') ? now() : null;

            PurchasePayment::create([
                'id' => (string) Str::uuid(),
                'purchase_id' => $purchase->id,
                'method' => $paymentMethod,
                'amount' => $purchase->grand_total,
                'status' => $paymentStatus,
                'paid_at' => $paidAt,
            ]);

            if ($paymentMethod === 'KREDIT') {
                Debts::create([
                    'debtor_type' => 'branch',
                    'debtor_branch_id' => $purchase->branch_id,
                    'debtor_outlet_id' => null,
                    'creditor_type' => 'supplier',
                    'supplier_id' => $purchase->supplier_id,
                    'creditor_branch_id' => null,
                    'source_type' => 'purchase',
                    'purchase_id' => $purchase->id,
                    'invoice_number' => $purchase->invoice,
                    'total_amount' => $purchase->grand_total,
                    'paid_amount' => 0,
                    'remaining_amount' => $purchase->grand_total,
                    'status' => 'unpaid',
                    'due_date' => $request->input('due_date'),
                    'notes' => $request->input('notes'),
                ]);
            }

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

        DB::transaction(function () use ($purchase, $validated, $request) {
            $purchase->update($validated);

            // If the purchase has a related Debt, update its total_amount and recalculate
            $debt = Debts::where('purchase_id', $purchase->id)->first();
            if ($debt) {
                $debt->total_amount = $purchase->grand_total;
                $debt->remaining_amount = max(0, $purchase->grand_total - $debt->paid_amount);
                if ($debt->remaining_amount <= 0) {
                    $debt->status = 'paid';
                } elseif ($debt->paid_amount > 0) {
                    $debt->status = 'partial';
                } else {
                    $debt->status = 'unpaid';
                }
                $debt->save();
            }

            if ($request->has('items')) {
                // Delete previous items
                $purchase->items()->delete();

                // Add new items
                if (is_array($request->items)) {
                    foreach ($request->items as $item) {
                        $product = Product::find($item['product_id']);
                        if ($product) {
                            PurchaseItem::create([
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

        DB::transaction(function () use ($purchase) {
            // Delete related debt and its payments (if any)
            Debts::where('purchase_id', $purchase->id)->delete();
            $purchase->delete();
        });

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Purchase berhasil dihapus']);
        }

        return redirect()->route('purchases.index')->with('success', 'Purchase berhasil dihapus');
    }
}
