<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStock extends Model
{
    use HasFactory;

    // Menandakan bahwa primary key bukan integer auto-increment
    public $incrementing = false;

    // Menetapkan tipe primary key sebagai string
    protected $keyType = 'string';

    // Kolom-kolom yang diizinkan untuk diisi secara massal
    protected $fillable = [
        'id',
        'product_id',
        'branch_id',
        'stock',
        'minimum_stock',
        'average_cost',
    ];

    /**
     * Relasi ke model Product (Many to One)
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relasi ke model Branch (Many to One)
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
