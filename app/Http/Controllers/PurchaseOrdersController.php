<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrders;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PurchaseOrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $purchaseOrders = PurchaseOrders::with(['branch', 'user', 'sale'])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($request->wantsJson()) {
            return response()->json($purchaseOrders);
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

        $purchaseOrder->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Purchase Order berhasil diupdate',
                'data' => $purchaseOrder->load(['branch', 'user', 'sale']),
            ]);
        }

        return redirect()->route('purchase-orders.index')->with('success', 'Purchase Order berhasil diupdate');
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
