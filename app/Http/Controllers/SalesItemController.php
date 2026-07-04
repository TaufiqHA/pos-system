<?php

namespace App\Http\Controllers;

use App\Models\SalesItem;
use Illuminate\Http\Request;

class SalesItemController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|string|unique:sale_items,id',
            'sale_id' => 'required|string|exists:sales,id',
            'product_id' => 'required|string|exists:products,id',
            'sku' => 'required|string',
            'product_name' => 'required|string',
            'unit' => 'required|string',
            'qty' => 'required|integer|min:1',
            'price' => 'required|numeric',
            'cost' => 'required|numeric',
            'subtotal' => 'required|numeric',
            'is_wholesale' => 'boolean',
        ]);

        $salesItem = SalesItem::create($validated);

        return response()->json($salesItem, 201);
    }

    public function show($id)
    {
        $salesItem = SalesItem::with(['sale', 'product'])->findOrFail($id);

        return response()->json($salesItem);
    }

    public function update(Request $request, $id)
    {
        $salesItem = SalesItem::findOrFail($id);

        $validated = $request->validate([
            'sale_id' => 'sometimes|required|string|exists:sales,id',
            'product_id' => 'sometimes|required|string|exists:products,id',
            'sku' => 'sometimes|required|string',
            'product_name' => 'sometimes|required|string',
            'unit' => 'sometimes|required|string',
            'qty' => 'sometimes|required|integer|min:1',
            'price' => 'sometimes|required|numeric',
            'cost' => 'sometimes|required|numeric',
            'subtotal' => 'sometimes|required|numeric',
            'is_wholesale' => 'boolean',
        ]);

        $salesItem->update($validated);

        return response()->json($salesItem);
    }

    public function destroy($id)
    {
        $salesItem = SalesItem::findOrFail($id);
        $salesItem->delete();

        return response()->json(['message' => 'Sales Item deleted successfully']);
    }
}
