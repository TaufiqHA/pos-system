<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Debts;
use App\Models\DebtsPayment;
use App\Models\Outlets;
use App\Models\Suppliers;
use Illuminate\Http\Request;

class DebtsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $debts = Debts::with([
            'debtorBranch',
            'debtorOutlet',
            'supplier',
            'creditorBranch',
            'purchase',
            'sale',
        ])->get();

        if ($request->wantsJson()) {
            return response()->json($debts);
        }

        return response()->json($debts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'debtor_type' => 'required|string',
            'debtor_branch_id' => 'nullable|string|exists:branches,id',
            'debtor_outlet_id' => 'nullable|string|exists:outlets,id',
            'creditor_type' => 'required|string',
            'supplier_id' => 'nullable|string|exists:suppliers,id',
            'creditor_branch_id' => 'nullable|string|exists:branches,id',
            'source_type' => 'nullable|string',
            'purchase_id' => 'nullable|string|exists:purchases,id',
            'sale_id' => 'nullable|string|exists:sales,id',
            'invoice_number' => 'nullable|string|max:255',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'remaining_amount' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
            'status' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if (! isset($validated['paid_amount'])) {
            $validated['paid_amount'] = 0;
        }

        if (! isset($validated['remaining_amount'])) {
            $validated['remaining_amount'] = $validated['total_amount'] - $validated['paid_amount'];
        }

        if (empty($validated['status'])) {
            if ($validated['paid_amount'] <= 0) {
                $validated['status'] = 'unpaid';
            } elseif ($validated['remaining_amount'] <= 0) {
                $validated['status'] = 'paid';
            } else {
                $validated['status'] = 'partial';
            }
        }

        $debt = Debts::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Debt berhasil dibuat',
                'data' => $debt->load(['debtorBranch', 'debtorOutlet', 'supplier', 'creditorBranch', 'purchase', 'sale']),
            ], 201);
        }

        return response()->json([
            'message' => 'Debt berhasil dibuat',
            'data' => $debt,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $debt = Debts::with([
            'debtorBranch',
            'debtorOutlet',
            'supplier',
            'creditorBranch',
            'purchase',
            'sale',
        ])->findOrFail($id);

        if ($request->wantsJson()) {
            return response()->json($debt);
        }

        return response()->json($debt);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $debt = Debts::findOrFail($id);

        $validated = $request->validate([
            'debtor_type' => 'sometimes|required|string',
            'debtor_branch_id' => 'nullable|string|exists:branches,id',
            'debtor_outlet_id' => 'nullable|string|exists:outlets,id',
            'creditor_type' => 'sometimes|required|string',
            'supplier_id' => 'nullable|string|exists:suppliers,id',
            'creditor_branch_id' => 'nullable|string|exists:branches,id',
            'source_type' => 'nullable|string',
            'purchase_id' => 'nullable|string|exists:purchases,id',
            'sale_id' => 'nullable|string|exists:sales,id',
            'invoice_number' => 'nullable|string|max:255',
            'total_amount' => 'sometimes|required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'remaining_amount' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
            'status' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $debt->update($validated);

        // recalculate remaining amount and status if total or paid amount was updated
        if (isset($validated['total_amount']) || isset($validated['paid_amount'])) {
            $total = $debt->total_amount;
            $paid = $debt->paid_amount;
            $debt->remaining_amount = $total - $paid;

            if ($debt->remaining_amount <= 0) {
                $debt->status = 'paid';
            } elseif ($paid > 0) {
                $debt->status = 'partial';
            } else {
                $debt->status = 'unpaid';
            }
            $debt->save();
        }

        // Sync with Sales model if sale_id is present
        if ($debt->sale_id) {
            $sale = $debt->sale;
            if ($sale) {
                $newStatus = $debt->status === 'paid' ? 'LUNAS' : 'BELUM BAYAR';
                if ($sale->status !== $newStatus) {
                    $sale->update(['status' => $newStatus]);
                }

                $payment = $sale->salesPayments()->first();
                if ($payment && $payment->status !== $newStatus) {
                    $payment->update([
                        'status' => $newStatus,
                        'paid_at' => $debt->status === 'paid' ? now() : null,
                    ]);
                }
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Debt berhasil diupdate',
                'data' => $debt->load(['debtorBranch', 'debtorOutlet', 'supplier', 'creditorBranch', 'purchase', 'sale']),
            ]);
        }

        return response()->json([
            'message' => 'Debt berhasil diupdate',
            'data' => $debt,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $debt = Debts::findOrFail($id);
        $debt->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Debt berhasil dihapus']);
        }

        return response()->json(['message' => 'Debt berhasil dihapus']);
    }

    public function delete(Request $request, $id)
    {
        return $this->destroy($request, $id);
    }

    /**
     * Display a listing of the resource for admin panel.
     */
    public function adminIndex(Request $request)
    {
        $debts = Debts::with([
            'debtorBranch',
            'debtorOutlet',
            'supplier',
            'creditorBranch',
            'purchase',
            'sale',
            'payments.creator',
        ])->orderBy('created_at', 'desc')->get();

        $pendingPayments = DebtsPayment::where('status', 'PENDING')->with([
            'debt.debtorBranch',
            'creator',
        ])->orderBy('created_at', 'desc')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'debts' => $debts,
                'pending_payments' => $pendingPayments,
            ]);
        }

        $suppliers = Suppliers::all();
        $branches = Branch::all();
        $outlets = Outlets::all();

        return view('admin.hutang', compact('debts', 'suppliers', 'branches', 'outlets', 'pendingPayments'));
    }

    /**
     * Display a listing of the resource for cabang panel.
     */
    public function cabangIndex(Request $request)
    {
        $branchId = auth()->user()->branch_id;

        $debts = Debts::where('debtor_type', 'branch')
            ->where('debtor_branch_id', $branchId)
            ->with([
                'debtorBranch',
                'debtorOutlet',
                'supplier',
                'creditorBranch',
                'purchase',
                'sale',
                'payments.creator',
            ])->orderBy('created_at', 'desc')->get();

        $outletDebts = Debts::where('debtor_type', 'outlet')
            ->where('creditor_type', 'branch')
            ->where('creditor_branch_id', $branchId)
            ->with([
                'debtorBranch',
                'debtorOutlet',
                'supplier',
                'creditorBranch',
                'purchase',
                'sale',
                'payments.creator',
            ])->orderBy('created_at', 'desc')->get();

        $pendingPayments = DebtsPayment::where('status', 'PENDING')
            ->whereHas('debt', function ($query) use ($branchId) {
                $query->where('debtor_type', 'outlet')
                    ->where('creditor_type', 'branch')
                    ->where('creditor_branch_id', $branchId);
            })
            ->with([
                'debt.debtorOutlet',
                'creator',
            ])->orderBy('created_at', 'desc')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'debts' => $debts,
                'outlet_debts' => $outletDebts,
                'pending_payments' => $pendingPayments,
            ]);
        }

        return view('cabang.hutang', compact('debts', 'outletDebts', 'pendingPayments'));
    }
}
