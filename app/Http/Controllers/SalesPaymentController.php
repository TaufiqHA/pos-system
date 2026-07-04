<?php

namespace App\Http\Controllers;

use App\Models\SalesPayment;
use Illuminate\Http\Request;

class SalesPaymentController extends Controller
{
    // Menyimpan data pembayaran baru
    public function create(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|string|unique:sales_payments,id',
            'sale_id' => 'required|string|exists:sales,id',
            'method' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|string',
            'reference' => 'nullable|string',
            'paid_at' => 'nullable|date',
        ]);

        $payment = SalesPayment::create($validated);

        return response()->json(['message' => 'Sales payment created', 'data' => $payment], 201);
    }

    // Menampilkan detail satu data pembayaran
    public function show(string $id)
    {
        $payment = SalesPayment::with('sale')->findOrFail($id);

        return response()->json(['data' => $payment]);
    }

    // Memperbarui data pembayaran
    public function update(Request $request, string $id)
    {
        $payment = SalesPayment::findOrFail($id);

        $validated = $request->validate([
            'sale_id' => 'sometimes|string|exists:sales,id',
            'method' => 'sometimes|string',
            'amount' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|string',
            'reference' => 'nullable|string',
            'paid_at' => 'nullable|date',
        ]);

        $payment->update($validated);

        return response()->json(['message' => 'Sales payment updated', 'data' => $payment]);
    }

    // Menghapus data pembayaran
    public function delete(string $id)
    {
        $payment = SalesPayment::findOrFail($id);
        $payment->delete();

        return response()->json(['message' => 'Sales payment deleted successfully']);
    }
}
