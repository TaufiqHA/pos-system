<?php

namespace App\Http\Controllers;

use App\Models\Deliveries;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PurchaseOrders;
use App\Models\Sales;
use App\Models\SalesItem;
use App\Models\SalesPayment;
use App\Models\StockHistories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseOrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = PurchaseOrders::with(['branch', 'user', 'sale']);

        if ($user->role && $user->role->name === 'cabang') {
            $query->where('branch_id', $user->branch_id);
        }

        $purchaseOrders = $query->orderBy('created_at', 'desc')->get();

        if ($request->wantsJson()) {
            return response()->json($purchaseOrders);
        }

        if ($user->role && $user->role->name === 'cabang') {
            $products = Product::orderBy('name')->get();

            return view('cabang.daftar-po', compact('purchaseOrders', 'products'));
        }

        return view('admin.purchase_orders.index', compact('purchaseOrders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return redirect()->route('purchase-orders.index', ['action' => 'create']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'po_number' => 'nullable|string|max:255|unique:purchase_orders,po_number',
            'branch_id' => 'required|exists:branches,id',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|string|in:Draft,Pending,Approved,Rejected,Completed',
            'notes' => 'nullable|string',
            'sale_id' => 'nullable|exists:sales,id',
        ]);

        $validated['sale_id'] = null;

        $validated['id'] = (string) Str::uuid();

        if (empty($validated['po_number'])) {
            $poNumber = 'PO-'.date('Ymd').'-'.strtoupper(Str::random(6));
            while (PurchaseOrders::where('po_number', $poNumber)->exists()) {
                $poNumber = 'PO-'.date('Ymd').'-'.strtoupper(Str::random(6));
            }
            $validated['po_number'] = $poNumber;
        }

        // If it's a standard Form submit, serialize structured fields into notes
        if (! $request->wantsJson() && ! $request->has('notes')) {
            $notesData = [
                'user_notes' => $request->input('user_notes'),
                'subtotal' => $request->input('subtotal'),
                'discount' => $request->input('discount'),
                'tax' => $request->input('tax'),
                'grand_total' => $request->input('grand_total'),
                'payment_method' => $request->input('payment_method'),
                'items' => $request->input('items', []),
            ];
            $validated['notes'] = json_encode($notesData);
        }

        $purchaseOrder = PurchaseOrders::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Purchase Order berhasil dibuat',
                'data' => $purchaseOrder->load(['branch', 'user', 'sale']),
            ], 201);
        }

        return redirect()->back()->with('success', 'Purchase Order berhasil dibuat');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $purchaseOrder = PurchaseOrders::with(['branch', 'user', 'sale'])->findOrFail($id);

        return response()->json($purchaseOrder);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return redirect()->route('purchase-orders.index', ['action' => 'edit', 'id' => $id]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrders::findOrFail($id);

        $validated = $request->validate([
            'po_number' => 'nullable|string|max:255|unique:purchase_orders,po_number,'.$id,
            'branch_id' => 'required|exists:branches,id',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|string|in:Draft,Pending,Approved,Rejected,Completed',
            'notes' => 'nullable|string',
            'sale_id' => 'nullable|exists:sales,id',
        ]);

        if (empty($validated['po_number'])) {
            unset($validated['po_number']); // Don't overwrite with empty
        }

        // If it's a standard Form submit, serialize structured fields into notes
        if (! $request->wantsJson() && ! $request->has('notes')) {
            $notesData = [
                'user_notes' => $request->input('user_notes'),
                'subtotal' => $request->input('subtotal'),
                'discount' => $request->input('discount'),
                'tax' => $request->input('tax'),
                'grand_total' => $request->input('grand_total'),
                'payment_method' => $request->input('payment_method'),
                'items' => $request->input('items', []),
            ];
            $validated['notes'] = json_encode($notesData);
        }

        $purchaseOrder->update($validated);

        if ($purchaseOrder->status === 'Approved' && empty($purchaseOrder->sale_id)) {
            $notesData = json_decode($purchaseOrder->notes, true);
            if ($notesData) {
                $invoice = 'INV-'.date('Ymd').'-'.strtoupper(Str::random(6));
                while (Sales::where('invoice', $invoice)->exists()) {
                    $invoice = 'SLS-'.date('Ymd').'-'.strtoupper(Str::random(6));
                }

                DB::transaction(function () use ($purchaseOrder, $notesData, $invoice) {
                    $sale = Sales::create([
                        'id' => (string) Str::uuid(),
                        'invoice' => $invoice,
                        'branch_id' => $purchaseOrder->branch_id,
                        'user_id' => $purchaseOrder->user_id,
                        'create_by' => auth()->id(),
                        'date' => now()->format('Y-m-d H:i:s'),
                        'subtotal' => $notesData['subtotal'] ?? 0,
                        'discount' => $notesData['discount'] ?? 0,
                        'tax' => $notesData['tax'] ?? 0,
                        'grand_total' => $notesData['grand_total'] ?? 0,
                        'status' => 'LUNAS',
                    ]);

                    $items = $notesData['items'] ?? [];
                    foreach ($items as $item) {
                        $product = Product::find($item['product_id']);
                        if ($product) {
                            SalesItem::create([
                                'id' => (string) Str::uuid(),
                                'sale_id' => $sale->id,
                                'product_id' => $product->id,
                                'sku' => $product->sku,
                                'product_name' => $product->name,
                                'unit' => $product->unit ?? 'pcs',
                                'qty' => $item['qty'],
                                'price' => $item['price'],
                                'cost' => $product->buy_price,
                                'subtotal' => $item['qty'] * $item['price'],
                                'is_wholesale' => false,
                            ]);

                            // Kurangi stok Gudang Pusat
                            $pusatBranchId = auth()->user()->branch_id ?? 'BRC-001';
                            $qty = $item['qty'];

                            $pusatStock = ProductStock::where('product_id', $product->id)
                                ->where('branch_id', $pusatBranchId)
                                ->first();

                            $previousStock = 0;
                            if ($pusatStock) {
                                $previousStock = $pusatStock->stock;
                                $pusatStock->decrement('stock', $qty);
                            } else {
                                $pusatStock = ProductStock::create([
                                    'id' => (string) Str::uuid(),
                                    'product_id' => $product->id,
                                    'branch_id' => $pusatBranchId,
                                    'stock' => -$qty,
                                    'minimum_stock' => 0,
                                    'average_cost' => $product->buy_price,
                                ]);
                            }
                            $newStock = $previousStock - $qty;

                            // Catat ke StockHistories
                            StockHistories::create([
                                'id' => (string) Str::uuid(),
                                'product_id' => $product->id,
                                'branch_id' => $pusatBranchId,
                                'type' => 'OUT',
                                'qty' => $qty,
                                'previous_stock' => $previousStock,
                                'new_stock' => $newStock,
                                'reference_type' => PurchaseOrders::class,
                                'reference_id' => $purchaseOrder->id,
                                'user_id' => auth()->id() ?? $purchaseOrder->user_id,
                            ]);
                        }
                    }

                    $paymentMethod = $notesData['payment_method'] ?? 'TUNAI';
                    $paymentStatus = ($paymentMethod === 'TUNAI' || $paymentMethod === 'TRANSFER') ? 'LUNAS' : 'BELUM BAYAR';
                    $paidAt = $paymentStatus === 'LUNAS' ? now() : null;

                    SalesPayment::create([
                        'id' => (string) Str::uuid(),
                        'sale_id' => $sale->id,
                        'method' => $paymentMethod,
                        'amount' => $sale->grand_total,
                        'status' => $paymentStatus,
                        'paid_at' => $paidAt,
                    ]);

                    Deliveries::create([
                        'id' => (string) Str::uuid(),
                        'sale_id' => $sale->id,
                        'driver_name' => 'Belum Ditentukan',
                        'status' => 'PENDING',
                        'sent_at' => null,
                        'received_at' => null,
                    ]);

                    $purchaseOrder->update(['sale_id' => $sale->id]);
                });
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Purchase Order berhasil diupdate',
                'data' => $purchaseOrder->load(['branch', 'user', 'sale']),
            ]);
        }

        return redirect()->back()->with('success', 'Purchase Order berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrders::findOrFail($id);
        $purchaseOrder->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Purchase Order berhasil dihapus']);
        }

        return redirect()->route('purchase-orders.index')->with('success', 'Purchase Order berhasil dihapus');
    }

    /**
     * Remove the specified resource from storage (alias for destroy).
     */
    public function delete(Request $request, $id)
    {
        return $this->destroy($request, $id);
    }
}
