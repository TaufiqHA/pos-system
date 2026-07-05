<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockHistories extends Model
{
    use HasFactory;

    protected $table = 'stock_histories';

    // Karena primary key menggunakan varchar (string)
    protected $keyType = 'string';

    public $incrementing = false;

    // Kolom-kolom yang diizinkan untuk diisi secara massal
    protected $fillable = [
        'id',
        'product_id',
        'branch_id',
        'type',
        'qty',
        'previous_stock',
        'new_stock',
        'reference_type',
        'reference_id',
        'user_id',
    ];

    /**
     * Relasi ke model Product (Many to One)
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Relasi ke model Branch (Many to One)
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Relasi ke model User (Many to One)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi polymorphic ke model referensi (misal Sales, Purchases, dll.)
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
