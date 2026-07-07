<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DebtsPayment extends Model
{
    use HasFactory;

    protected $table = 'debts_payments';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'debt_id',
        'payment_date',
        'amount',
        'method',
        'reference',
        'notes',
        'created_by',
        'status',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function debt()
    {
        return $this->belongsTo(Debts::class, 'debt_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
