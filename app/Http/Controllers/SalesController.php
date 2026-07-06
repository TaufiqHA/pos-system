<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Deliveries;
use App\Models\Outlets;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Sales;
use App\Models\SalesItem;
use App\Models\SalesPayment;
use App\Models\StockHistories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role && auth()->user()->role->name === 'cabang') {
            if ($request->wantsJson()) {
                return $this->cabangIndex($request);
            }

            return redirect()->route('cabang.penjualan');
        }

        $sales = Sales::with(['branch.users', 'user', 'salesItems', 'salesPayments'])
            ->where('create_by', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        if ($request->wantsJson()) {
            return response()->json($sales);
        }

        $products = Product::all();
        $branches = Branch::whereDoesntHave('users', function ($query) {
            $query->whereHas('role', function ($q) {
                $q->where('name', 'admin');
            });
        })->get();

        return view('admin.sale', compact('sales', 'products', 'branches'));
    }

    public function create()
    {
        if (auth()->user()->role && auth()->user()->role->name === 'cabang') {
            return redirect()->route('cabang.penjualan');
        }

        return redirect()->route('sales.index', ['action' => 'create']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice' => 'sometimes|string|unique:sales',
            'branch_id' => 'required|exists:branches,id',
            'outlet_id' => 'nullable|exists:outlets,id',
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'status' => 'required|string|max:50',
            'payment_method' => 'nullable|string|in:TUNAI,TRANSFER,KREDIT',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $validated['id'] = (string) Str::uuid();

        if (! isset($validated['invoice'])) {
            $invoice = 'INV-'.date('Ymd').'-'.strtoupper(Str::random(6));
            while (Sales::where('invoice', $invoice)->exists()) {
                $invoice =
                    'SLS-'.date('Ymd').'-'.strtoupper(Str::random(6));
            }
            $validated['invoice'] = $invoice;
        }

        $validated['discount'] = $validated['discount'] ?? 0;
        $validated['tax'] = $validated['tax'] ?? 0;
        $validated['create_by'] = auth()->id();

        $sale = DB::transaction(function () use ($validated, $request) {
            $sale = Sales::create($validated);

            if ($request->has('items') && is_array($request->items)) {
                $isCabang = auth()->user()->role && auth()->user()->role->name === 'cabang';
                $branchId = auth()->user()->branch_id;

                foreach ($request->items as $item) {
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

                        if ($isCabang && $branchId) {
                            $qty = $item['qty'];
                            $branchStock = ProductStock::where('product_id', $product->id)
                                ->where('branch_id', $branchId)
                                ->first();

                            $previousStock = 0;
                            if ($branchStock) {
                                $previousStock = $branchStock->stock;
                                $branchStock->update(['stock' => $previousStock - $qty]);
                            } else {
                                $branchStock = ProductStock::create([
                                    'id' => (string) Str::uuid(),
                                    'product_id' => $product->id,
                                    'branch_id' => $branchId,
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
                                'branch_id' => $branchId,
                                'type' => 'OUT',
                                'qty' => $qty,
                                'previous_stock' => $previousStock,
                                'new_stock' => $newStock,
                                'reference_type' => Sales::class,
                                'reference_id' => $sale->id,
                                'user_id' => auth()->id(),
                            ]);
                        }
                    }
                }
            }

            $paymentMethod = $request->input('payment_method', 'TUNAI');
            $paymentStatus =
                $paymentMethod === 'TUNAI' || $paymentMethod === 'TRANSFER'
                    ? 'LUNAS'
                    : 'BELUM BAYAR';
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
                'created_by' => auth()->id() ?? $sale->user_id,
                'sent_at' => null,
                'received_at' => null,
            ]);

            return $sale;
        });

        if ($request->wantsJson()) {
            return response()->json($sale, 201);
        }

        if (auth()->user()->role && auth()->user()->role->name === 'cabang') {
            return redirect()
                ->route('cabang.penjualan')
                ->with('success', 'Penjualan berhasil dibuat');
        }

        return redirect()
            ->route('sales.index')
            ->with('success', 'Penjualan berhasil dibuat');
    }

    public function show(string $id)
    {
        if (auth()->user()->role && auth()->user()->role->name === 'cabang' && ! request()->wantsJson() && ! request()->ajax()) {
            return redirect()->route('cabang.penjualan');
        }

        $sale = Sales::with([
            'branch.users',
            'user',
            'outlet',
            'salesItems',
            'salesPayments',
        ])->findOrFail($id);

        return response()->json($sale);
    }

    /**
     * Display sales for the current branch (cabang).
     */
    public function cabangIndex(Request $request)
    {
        $branchId = auth()->user()->branch_id;
        $sales = Sales::with(['branch.users', 'user', 'outlet', 'salesItems', 'salesPayments'])
            ->where('branch_id', $branchId)
            ->where('create_by', auth()->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($request->wantsJson()) {
            return response()->json($sales);
        }

        $branches = Branch::where('id', $branchId)->get();
        $products = Product::whereHas('productStocks', function ($query) use ($branchId) {
            $query->where('branch_id', $branchId);
        })->with(['branchPrices' => function ($query) use ($branchId) {
            $query->where('branch_id', $branchId);
        }])->orderBy('name')->get();
        $outlets = Outlets::where('branch_id', $branchId)->orderBy('name')->get();

        return view('cabang.penjualan', compact('sales', 'branches', 'products', 'outlets'));
    }

    public function edit($id)
    {
        if (auth()->user()->role && auth()->user()->role->name === 'cabang') {
            return redirect()->route('cabang.penjualan');
        }

        return redirect()->route('sales.index', [
            'action' => 'edit',
            'id' => $id,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $sale = Sales::findOrFail($id);

        $validated = $request->validate([
            'invoice' => 'sometimes|string|unique:sales,invoice,'.$id,
            'branch_id' => 'required|exists:branches,id',
            'outlet_id' => 'nullable|exists:outlets,id',
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'status' => 'required|string|max:50',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $validated['discount'] = $validated['discount'] ?? 0;
        $validated['tax'] = $validated['tax'] ?? 0;

        DB::transaction(function () use ($sale, $validated, $request) {
            $isCabang = auth()->user()->role && auth()->user()->role->name === 'cabang';
            $branchId = auth()->user()->branch_id;

            if ($isCabang && $branchId && $request->has('items')) {
                // Restore old stock
                foreach ($sale->salesItems as $oldItem) {
                    $oldStock = ProductStock::where('product_id', $oldItem->product_id)
                        ->where('branch_id', $branchId)
                        ->first();
                    if ($oldStock) {
                        $oldStock->update(['stock' => $oldStock->stock + $oldItem->qty]);
                        // Record stock history for restore
                        StockHistories::create([
                            'id' => (string) Str::uuid(),
                            'product_id' => $oldItem->product_id,
                            'branch_id' => $branchId,
                            'type' => 'IN',
                            'qty' => $oldItem->qty,
                            'previous_stock' => $oldStock->stock - $oldItem->qty,
                            'new_stock' => $oldStock->stock,
                            'reference_type' => Sales::class,
                            'reference_id' => $sale->id,
                            'user_id' => auth()->id(),
                        ]);
                    }
                }
            }

            $sale->update($validated);

            if ($request->has('items')) {
                $sale->salesItems()->delete();

                if (is_array($request->items)) {
                    foreach ($request->items as $item) {
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

                            if ($isCabang && $branchId) {
                                $qty = $item['qty'];
                                $branchStock = ProductStock::where('product_id', $product->id)
                                    ->where('branch_id', $branchId)
                                    ->first();

                                $previousStock = 0;
                                if ($branchStock) {
                                    $previousStock = $branchStock->stock;
                                    $branchStock->update(['stock' => $previousStock - $qty]);
                                } else {
                                    $branchStock = ProductStock::create([
                                        'id' => (string) Str::uuid(),
                                        'product_id' => $product->id,
                                        'branch_id' => $branchId,
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
                                    'branch_id' => $branchId,
                                    'type' => 'OUT',
                                    'qty' => $qty,
                                    'previous_stock' => $previousStock,
                                    'new_stock' => $newStock,
                                    'reference_type' => Sales::class,
                                    'reference_id' => $sale->id,
                                    'user_id' => auth()->id(),
                                ]);
                            }
                        }
                    }
                }
            }
        });

        if ($request->wantsJson()) {
            return response()->json($sale->fresh());
        }

        if (auth()->user()->role && auth()->user()->role->name === 'cabang') {
            return redirect()
                ->route('cabang.penjualan')
                ->with('success', 'Penjualan berhasil diupdate');
        }

        return redirect()
            ->route('sales.index')
            ->with('success', 'Penjualan berhasil diupdate');
    }

    public function destroy(Request $request, string $id)
    {
        $sale = Sales::findOrFail($id);
        $sale->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Deleted']);
        }

        if (auth()->user()->role && auth()->user()->role->name === 'cabang') {
            return redirect()
                ->route('cabang.penjualan')
                ->with('success', 'Penjualan berhasil dihapus');
        }

        return redirect()
            ->route('sales.index')
            ->with('success', 'Penjualan berhasil dihapus');
    }
}
