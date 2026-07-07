<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Debts extends Model
{
    use HasFactory;

    protected $table = 'debts';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'debtor_type',
        'debtor_branch_id',
        'debtor_outlet_id',
        'creditor_type',
        'supplier_id',
        'creditor_branch_id',
        'source_type',
        'purchase_id',
        'sale_id',
        'invoice_number',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'due_date',
        'status',
        'notes',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });

        static::saved(function ($debt) {
            if ($debt->sale_id) {
                $sale = $debt->sale;
                if ($sale) {
                    $newStatus = $debt->status === 'paid' ? 'LUNAS' : 'BELUM BAYAR';
                    if ($sale->status !== $newStatus) {
                        $sale->update(['status' => $newStatus]);
                    }

                    // Update related sales payment status
                    $payment = $sale->salesPayments()->first();
                    if ($payment && $payment->status !== $newStatus) {
                        $payment->update([
                            'status' => $newStatus,
                            'paid_at' => $debt->status === 'paid' ? now() : null,
                        ]);
                    }
                }
            }
        });
    }

    // Relations

    public function debtorBranch()
    {
        return $this->belongsTo(Branch::class, 'debtor_branch_id');
    }

    public function debtorOutlet()
    {
        return $this->belongsTo(Outlets::class, 'debtor_outlet_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Suppliers::class, 'supplier_id');
    }

    public function creditorBranch()
    {
        return $this->belongsTo(Branch::class, 'creditor_branch_id');
    }

    public function purchase()
    {
        return $this->belongsTo(Purchases::class, 'purchase_id');
    }

    public function sale()
    {
        return $this->belongsTo(Sales::class, 'sale_id');
    }

    public function payments()
    {
        return $this->hasMany(DebtsPayment::class, 'debt_id');
    }
}
