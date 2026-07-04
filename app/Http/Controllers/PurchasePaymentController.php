<?php

namespace App\Http\Controllers;

use App\Models\PurchasePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PurchasePaymentController extends Controller
{
    // 1. Create (Store) - Menyimpan data pembayaran
    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'method' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'status' => 'required|string|max:255',
            'reference' => 'nullable|string|max:255',
            'paid_at' => 'nullable|date',
        ]);

        $validated['id'] = (string) Str::uuid();

        $payment = PurchasePayment::create($validated);

        return response()->json(['message' => 'Pembayaran berhasil ditambahkan', 'data' => $payment], 201);
    }

    // 2. Show - Menampilkan detail satu pembayaran spesifik
    public function show($id)
    {
        $payment = PurchasePayment::findOrFail($id);

        return response()->json($payment);
    }

    // 3. Update - Memperbarui data pembayaran
    public function update(Request $request, $id)
    {
        $payment = PurchasePayment::findOrFail($id);

        $validated = $request->validate([
            'method' => 'sometimes|required|string|max:255',
            'amount' => 'sometimes|required|numeric',
            'status' => 'sometimes|required|string|max:255',
            'reference' => 'nullable|string|max:255',
            'paid_at' => 'nullable|date',
        ]);

        $payment->update($validated);

        return response()->json(['message' => 'Pembayaran berhasil diupdate', 'data' => $payment]);
    }

    // 4. Delete (Destroy) - Menghapus data pembayaran
    public function destroy($id)
    {
        $payment = PurchasePayment::findOrFail($id);
        $payment->delete();

        return response()->json(['message' => 'Pembayaran berhasil dihapus']);
    }
}
