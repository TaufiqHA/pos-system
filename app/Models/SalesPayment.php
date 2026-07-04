<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesPayment extends Model
{
    use HasFactory;

    protected $table = 'sales_payments';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'sale_id',
        'method',
        'amount',
        'status',
        'reference',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sales::class, 'sale_id', 'id');
    }
}
