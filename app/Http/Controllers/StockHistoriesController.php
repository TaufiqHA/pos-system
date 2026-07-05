<?php

namespace App\Http\Controllers;

use App\Models\StockHistories;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StockHistoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = StockHistories::with(['product', 'branch', 'user', 'reference']);

        // Jika user memiliki branch_id (cabang), filter hanya untuk cabangnya sendiri
        if (auth()->user()->branch_id) {
            $query->where('branch_id', auth()->user()->branch_id);
        } elseif ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Optional filtering by request inputs
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $histories = $query->orderBy('created_at', 'desc')->get();

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json($histories);
        }

        // Mengarah ke halaman monitoring-stok cabang
        return view('cabang.riwayat-stok', compact('histories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|string|unique:stock_histories,id',
            'product_id' => 'required|string|exists:products,id',
            'branch_id' => 'required|string|exists:branches,id',
            'type' => 'required|string|max:255',
            'qty' => 'required|integer',
            'previous_stock' => 'required|integer',
            'new_stock' => 'required|integer',
            'reference_type' => 'nullable|string|max:255',
            'reference_id' => 'nullable|string|max:255',
            'user_id' => 'required|string|exists:users,id',
        ]);

        if (empty($validated['id'])) {
            $validated['id'] = (string) Str::uuid();
        }

        $history = StockHistories::create($validated);

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'message' => 'Stock history created successfully',
                'data' => $history,
            ], 201);
        }

        return redirect()->back()->with('success', 'Stock history created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $history = StockHistories::with(['product', 'branch', 'user', 'reference'])->findOrFail($id);

        return response()->json($history);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $history = StockHistories::findOrFail($id);

        $validated = $request->validate([
            'product_id' => 'sometimes|required|string|exists:products,id',
            'branch_id' => 'sometimes|required|string|exists:branches,id',
            'type' => 'sometimes|required|string|max:255',
            'qty' => 'sometimes|required|integer',
            'previous_stock' => 'sometimes|required|integer',
            'new_stock' => 'sometimes|required|integer',
            'reference_type' => 'nullable|string|max:255',
            'reference_id' => 'nullable|string|max:255',
            'user_id' => 'sometimes|required|string|exists:users,id',
        ]);

        $history->update($validated);

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'message' => 'Stock history updated successfully',
                'data' => $history,
            ]);
        }

        return redirect()->back()->with('success', 'Stock history updated successfully');
    }

    /**
     * Remove the specified resource from storage (using delete method).
     */
    public function delete(Request $request, $id)
    {
        $history = StockHistories::findOrFail($id);
        $history->delete();

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'message' => 'Stock history deleted successfully',
            ]);
        }

        return redirect()->back()->with('success', 'Stock history deleted successfully');
    }

    /**
     * Remove the specified resource from storage (using destroy method for resource compatibility).
     */
    public function destroy(Request $request, $id)
    {
        return $this->delete($request, $id);
    }
}
