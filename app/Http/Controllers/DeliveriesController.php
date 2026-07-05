<?php

namespace App\Http\Controllers;

use App\Models\Deliveries;
use App\Models\ProductStock;
use App\Models\PurchaseOrders;
use App\Models\Sales;
use App\Models\SalesItem;
use App\Models\StockHistories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeliveriesController extends Controller
{
    public function index(Request $request)
    {
        $deliveries = Deliveries::with('sale')->orderBy('created_at', 'desc')->get();

        if ($request->wantsJson()) {
            return response()->json($deliveries);
        }

        $sales = Sales::whereDoesntHave('delivery')->get();

        return view('admin.deliver', compact('deliveries', 'sales'));
    }

    public function create()
    {
        return redirect()->route('deliveries.index', ['action' => 'create']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_id' => 'nullable|exists:sales,id',
            'status' => 'required|string|max:50',
            'sent_at' => 'nullable|date',
            'received_at' => 'nullable|date',
        ]);

        $validated['id'] = (string) Str::uuid();
        $validated['driver_name'] = 'Belum Ditentukan';

        if ($validated['status'] === 'DIKIRIM' && empty($validated['sent_at'])) {
            $validated['sent_at'] = now();
        }

        $delivery = Deliveries::create($validated);

        if ($request->wantsJson()) {
            return response()->json($delivery, 201);
        }

        return redirect()->route('deliveries.index')->with('success', 'Pengiriman berhasil dibuat');
    }

    public function show(string $id)
    {
        $delivery = Deliveries::with('sale')->findOrFail($id);

        return response()->json($delivery);
    }

    public function update(Request $request, string $id)
    {
        $delivery = Deliveries::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|string|max:50',
            'sent_at' => 'nullable|date',
            'received_at' => 'nullable|date',
        ]);

        $validated['driver_name'] = 'Belum Ditentukan';

        if ($validated['status'] === 'DIKIRIM' && empty($validated['sent_at'])) {
            $validated['sent_at'] = now();
        }

        if ($validated['status'] === 'DITERIMA' && empty($validated['received_at'])) {
            $validated['received_at'] = now();
        }

        $isReceivedNow = $validated['status'] === 'DITERIMA' && $delivery->status !== 'DITERIMA';

        DB::transaction(function () use ($delivery, $validated, $isReceivedNow) {
            $delivery->update($validated);

            if ($isReceivedNow) {
                $branchId = auth()->user()->branch_id ?? $delivery->sale->branch_id ?? null;

                if ($branchId && $delivery->sale_id) {
                    $items = [];
                    $purchaseOrder = PurchaseOrders::where('sale_id', $delivery->sale_id)->first();
                    if ($purchaseOrder && $purchaseOrder->notes) {
                        $notesData = json_decode($purchaseOrder->notes, true);
                        if (isset($notesData['items']) && is_array($notesData['items'])) {
                            $items = $notesData['items'];
                        }
                    }

                    if (empty($items)) {
                        $salesItems = SalesItem::where('sale_id', $delivery->sale_id)->get();
                        foreach ($salesItems as $salesItem) {
                            $items[] = [
                                'product_id' => $salesItem->product_id,
                                'qty' => $salesItem->qty,
                                'price' => $salesItem->price,
                            ];
                        }
                    }

                    foreach ($items as $item) {
                        $productId = $item['product_id'] ?? null;
                        $qty = $item['qty'] ?? 0;
                        $price = $item['price'] ?? 0;

                        if ($productId && $qty > 0) {
                            $stockRecord = ProductStock::where('product_id', $productId)
                                ->where('branch_id', $branchId)
                                ->first();

                            $previousStock = 0;
                            if ($stockRecord) {
                                $previousStock = $stockRecord->stock;
                                $stockRecord->increment('stock', $qty);
                            } else {
                                $stockRecord = ProductStock::create([
                                    'id' => (string) Str::uuid(),
                                    'product_id' => $productId,
                                    'branch_id' => $branchId,
                                    'stock' => $qty,
                                    'minimum_stock' => 0,
                                    'average_cost' => $price,
                                ]);
                            }
                            $newStock = $previousStock + $qty;

                            // Create stock history record
                            StockHistories::create([
                                'id' => (string) Str::uuid(),
                                'product_id' => $productId,
                                'branch_id' => $branchId,
                                'type' => 'IN',
                                'qty' => $qty,
                                'previous_stock' => $previousStock,
                                'new_stock' => $newStock,
                                'reference_type' => $purchaseOrder ? PurchaseOrders::class : Sales::class,
                                'reference_id' => $purchaseOrder ? $purchaseOrder->id : $delivery->sale_id,
                                'user_id' => auth()->id() ?? $delivery->sale->user_id ?? $purchaseOrder->user_id ?? null,
                            ]);
                        }
                    }
                }
            }
        });

        if ($request->wantsJson()) {
            return response()->json($delivery->fresh());
        }

        return redirect()->route('deliveries.index')->with('success', 'Pengiriman berhasil diupdate');
    }

    public function destroy(Request $request, string $id)
    {
        $delivery = Deliveries::findOrFail($id);
        $delivery->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Deleted']);
        }

        return redirect()->route('deliveries.index')->with('success', 'Pengiriman berhasil dihapus');
    }
}
