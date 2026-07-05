<?php

namespace App\Http\Controllers;

use App\Models\Deliveries;
use App\Models\Sales;
use Illuminate\Http\Request;
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

        $delivery->update($validated);

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
