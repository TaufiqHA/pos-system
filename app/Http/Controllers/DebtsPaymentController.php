<?php

namespace App\Http\Controllers;

use App\Models\Debts;
use App\Models\DebtsPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DebtsPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $payments = DebtsPayment::with(['debt', 'creator'])->get();

        return response()->json($payments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'debt_id' => 'required|string|exists:debts,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'method' => 'required|string',
            'reference' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $debtModel = Debts::findOrFail($validated['debt_id']);
        $isCabang = auth()->user()->role && auth()->user()->role->name === 'cabang';
        $status = ($isCabang && $debtModel->debtor_type === 'branch') ? 'PENDING' : 'CONFIRMED';

        $payment = DB::transaction(function () use ($validated, $status) {
            $validated['created_by'] = auth()->id();
            $validated['status'] = $status;

            $payment = DebtsPayment::create($validated);

            if ($status === 'CONFIRMED') {
                $debt = Debts::findOrFail($payment->debt_id);
                $debt->paid_amount += $payment->amount;
                $debt->remaining_amount = max(0, $debt->total_amount - $debt->paid_amount);

                if ($debt->remaining_amount <= 0) {
                    $debt->status = 'paid';
                } elseif ($debt->paid_amount > 0) {
                    $debt->status = 'partial';
                } else {
                    $debt->status = 'unpaid';
                }
                $debt->save();
            }

            return $payment;
        });

        return response()->json([
            'message' => $status === 'PENDING'
                ? 'Pembayaran berhasil dikirim dan menunggu konfirmasi admin.'
                : 'Pembayaran hutang berhasil ditambahkan',
            'data' => $payment->load(['debt', 'creator']),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $payment = DebtsPayment::with(['debt', 'creator'])->findOrFail($id);

        return response()->json($payment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $payment = DebtsPayment::findOrFail($id);

        $validated = $request->validate([
            'payment_date' => 'sometimes|required|date',
            'amount' => 'sometimes|required|numeric|min:0',
            'method' => 'sometimes|required|string',
            'reference' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $payment = DB::transaction(function () use ($payment, $validated) {
            if (isset($validated['amount']) && $payment->status === 'CONFIRMED') {
                $diff = $validated['amount'] - $payment->amount;

                $debt = Debts::findOrFail($payment->debt_id);
                $debt->paid_amount += $diff;
                $debt->remaining_amount = max(0, $debt->total_amount - $debt->paid_amount);

                if ($debt->remaining_amount <= 0) {
                    $debt->status = 'paid';
                } elseif ($debt->paid_amount > 0) {
                    $debt->status = 'partial';
                } else {
                    $debt->status = 'unpaid';
                }
                $debt->save();
            }

            $payment->update($validated);

            return $payment;
        });

        return response()->json([
            'message' => 'Pembayaran hutang berhasil diupdate',
            'data' => $payment->load(['debt', 'creator']),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $payment = DebtsPayment::findOrFail($id);

        DB::transaction(function () use ($payment) {
            if ($payment->status === 'CONFIRMED') {
                $debt = Debts::findOrFail($payment->debt_id);
                $debt->paid_amount = max(0, $debt->paid_amount - $payment->amount);
                $debt->remaining_amount = max(0, $debt->total_amount - $debt->paid_amount);

                if ($debt->remaining_amount <= 0) {
                    $debt->status = 'paid';
                } elseif ($debt->paid_amount > 0) {
                    $debt->status = 'partial';
                } else {
                    $debt->status = 'unpaid';
                }
                $debt->save();
            }

            $payment->delete();
        });

        return response()->json(['message' => 'Pembayaran hutang berhasil dihapus']);
    }

    /**
     * Remove the specified resource from storage (alias for destroy).
     */
    public function delete(Request $request, $id)
    {
        return $this->destroy($request, $id);
    }

    /**
     * Confirm a pending payment.
     */
    public function confirm(Request $request, $id)
    {
        $payment = DebtsPayment::findOrFail($id);

        if ($payment->status !== 'PENDING') {
            return response()->json([
                'message' => 'Pembayaran ini sudah dikonfirmasi atau ditolak sebelumnya.',
            ], 422);
        }

        $payment = DB::transaction(function () use ($payment) {
            $payment->status = 'CONFIRMED';
            $payment->save();

            $debt = Debts::findOrFail($payment->debt_id);
            $debt->paid_amount += $payment->amount;
            $debt->remaining_amount = max(0, $debt->total_amount - $debt->paid_amount);

            if ($debt->remaining_amount <= 0) {
                $debt->status = 'paid';
            } elseif ($debt->paid_amount > 0) {
                $debt->status = 'partial';
            } else {
                $debt->status = 'unpaid';
            }
            $debt->save();

            return $payment;
        });

        return response()->json([
            'message' => 'Pembayaran berhasil dikonfirmasi.',
            'data' => $payment->load(['debt', 'creator']),
        ]);
    }

    /**
     * Reject a pending payment.
     */
    public function reject(Request $request, $id)
    {
        $payment = DebtsPayment::findOrFail($id);

        if ($payment->status !== 'PENDING') {
            return response()->json([
                'message' => 'Pembayaran ini sudah dikonfirmasi atau ditolak sebelumnya.',
            ], 422);
        }

        $payment->status = 'REJECTED';
        $payment->save();

        return response()->json([
            'message' => 'Pembayaran berhasil ditolak.',
            'data' => $payment->load(['debt', 'creator']),
        ]);
    }
}
