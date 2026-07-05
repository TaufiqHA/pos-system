<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Deliveries;
use App\Models\Product;
use App\Models\Sales;
use App\Models\SalesItem;
use App\Models\SalesPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $sales = Sales::with(['branch.users', 'user', 'salesItems', 'salesPayments'])
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
        return redirect()->route('sales.index', ['action' => 'create']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice' => 'sometimes|string|unique:sales',
            'branch_id' => 'required|exists:branches,id',
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
                'sent_at' => null,
                'received_at' => null,
            ]);

            return $sale;
        });

        if ($request->wantsJson()) {
            return response()->json($sale, 201);
        }

        return redirect()
            ->route('sales.index')
            ->with('success', 'Penjualan berhasil dibuat');
    }

    public function show(string $id)
    {
        $sale = Sales::with([
            'branch.users',
            'user',
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
        $sales = Sales::with(['branch.users', 'user', 'salesItems', 'salesPayments'])
            ->where('branch_id', $branchId)
            ->where('create_by', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        if ($request->wantsJson()) {
            return response()->json($sales);
        }

        $branches = Branch::where('id', $branchId)->get();
        $products = Product::all();
        return view('cabang.penjualan', compact('sales', 'branches', 'products'));
        
    }

    public function edit($id)
    {
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
                        }
                    }
                }
            }
        });

        if ($request->wantsJson()) {
            return response()->json($sale->fresh());
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

        return redirect()
            ->route('sales.index')
            ->with('success', 'Penjualan berhasil dihapus');
    }
}
